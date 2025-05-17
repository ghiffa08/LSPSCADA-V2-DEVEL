<?php

namespace App\Controllers;

use Config\Database;
use Config\Services;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Services\ExcelImportService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ElemenController extends DataTableController
{
    use ResponseTrait;

    private $db;
    private $importService;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect();

        $this->model = $this->elemenModel;

        $this->importService = new ExcelImportService();

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'skema.id_skema',
            2 => 'unit.id_unit',
            3 => 'elemen.kode_elemen',
            4 => 'elemen.nama_elemen',
            5 => null // No ordering for action column
        ];
    }

    /**
     * Display a listing of the elemen.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        $data = [
            'siteTitle'  => "Kelola Elemen",
            'listElemen' => $this->elemenModel->findAll(),
            'listSkema'  => $this->skemaModel->getActiveSchemes(),
            'listUnit'   => $this->unitModel->getActiveUnits(),
        ];

        return view('admin/elemen', $data);
    }

    /**
     * Store a newly created elemen in storage.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function store()
    {
        $rules = [
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'id_unit' => [
                'label' => 'ID Unit',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'kode' => [
                'label' => "Kode Elemen",
                'rules' => 'required|is_unique[elemen.kode_elemen]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
            'nama' => [
                'label' => "Nama Elemen",
                'rules' => 'required|is_unique[elemen.nama_elemen]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->handleValidationErrors('addElemenModal');
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            $data = [
                'id_skema'   => $this->request->getVar('id_skema'),
                'id_unit'    => $this->request->getVar('id_unit'),
                'kode_elemen' => $this->request->getVar('kode'),
                'nama_elemen' => $this->request->getVar('nama')
            ];

            $this->elemenModel->save($data);

            $db->transCommit();
            return $this->handleSuccess('Elemen berhasil ditambahkan!');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error adding elemen: ' . $e->getMessage());
            return $this->handleError('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Import Skema data from Excel
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'id_skema'     => $row[0] ?? null,
                'id_unit'      => $row[1] ?? null,
                'kode_elemen'  => $row[2] ?? null,
                'nama_elemen'  => $row[3] ?? null,
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->elemenModel,
            [],
            $transformCallback
        );

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } elseif ($result['status'] === 'partial') {
            return redirect()->back()->with('warning', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }


    /**
     * Generate Excel template for Skema import
     */
    public function downloadTemplate()
    {
        $headers = [
            'ID Skema',
            'ID Unit',
            'Kode Elemen',
            'Nama Elemen'
        ];

        $filename = 'template_elemen_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }


    /**
     * Update the specified elemen in storage.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    // public function update()
    // {
    //     $id = $this->request->getVar('edit_id');

    //     $rules = [
    //         'edit_id_skema' => [
    //             'label' => 'ID Skema',
    //             'rules' => 'required',
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus dipilih.',
    //             ],
    //         ],
    //         'edit_id_unit' => [
    //             'label' => 'ID Unit',
    //             'rules' => 'required',
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus dipilih.',
    //             ],
    //         ],
    //         'edit_kode' => [
    //             'label' => "Kode Elemen",
    //             'rules' => "required|is_unique[elemen.kode_elemen,id,$id]",
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus diisi.',
    //                 'is_unique' => '{field} sudah terdaftar.',
    //             ],
    //         ],
    //         'edit_nama' => [
    //             'label' => "Nama Elemen",
    //             'rules' => "required|is_unique[elemen.nama_elemen,id,$id]",
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus diisi.',
    //                 'is_unique' => '{field} sudah terdaftar.',
    //             ],
    //         ],
    //     ];

    //     if (!$this->validate($rules)) {
    //         return $this->handleValidationErrors("editElemenModal-$id");
    //     }

    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     try {
    //         $data = [
    //             'id_skema'   => $this->request->getVar('edit_id_skema'),
    //             'id_unit'    => $this->request->getVar('edit_id_unit'),
    //             'kode_elemen' => $this->request->getVar('edit_kode'),
    //             'nama_elemen' => $this->request->getVar('edit_nama')
    //         ];

    //         $this->elemenModel->update($id, $data);

    //         $db->transCommit();
    //         return $this->handleSuccess('Elemen berhasil diupdate!');
    //     } catch (\Exception $e) {
    //         $db->transRollback();
    //         log_message('error', 'Error updating elemen: ' . $e->getMessage());
    //         return $this->handleError('Terjadi kesalahan saat mengupdate data: ' . $e->getMessage());
    //     }
    // }

    /**
     * Remove the specified elemen from storage.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    // public function delete()
    // {
    //     $id = $this->request->getVar('id');
    //     if (empty($id)) {
    //         return $this->handleError('ID Elemen tidak valid');
    //     }

    //     $db = \Config\Database::connect();
    //     $db->transBegin();

    //     try {
    //         $this->elemenModel->deleteElemen($id);

    //         $db->transCommit();
    //         return $this->handleSuccess('Elemen berhasil dihapus!');
    //     } catch (\Exception $e) {
    //         $db->transRollback();
    //         log_message('error', 'Error deleting elemen: ' . $e->getMessage());
    //         return $this->handleError('Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
    //     }
    // }

    /**
     * Get elemen by unit id for dropdown selection.
     *
     * @return string HTML options for select dropdown
     */
    public function getElemen()
    {
        try {
            $id_unit = $this->request->getPost('id_unit');
            if (empty($id_unit)) {
                return '<option>-- Pilih Elemen --</option>';
            }

            $elemen = $this->elemenModel->getElementsByUnit($id_unit);

            $output = '<option>-- Pilih Elemen --</option>';
            foreach ($elemen as $value) {
                $output .= "<option value=\"{$value['id_elemen']}\">{$value['nama_elemen']}</option>";
            }

            return $output;
        } catch (\Exception $e) {
            log_message('error', 'Error getting elemen options: ' . $e->getMessage());
            return '<option>-- Error loading data --</option>';
        }
    }

    /**
     * Handle validation errors.
     *
     * @param string $modalId
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function handleValidationErrors(string $modalId)
    {
        session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
        session()->setFlashdata('modal_id', $modalId);
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    /**
     * Handle success response.
     *
     * @param string $message
     * @param string $type
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function handleSuccess(string $message, string $type = 'pesan')
    {
        session()->setFlashdata($type, $message);
        return redirect()->to('/elemen');
    }

    /**
     * Handle error response.
     *
     * @param string $message
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function handleError(string $message)
    {
        session()->setFlashdata('error', $message);
        return redirect()->back()->withInput();
    }
}
