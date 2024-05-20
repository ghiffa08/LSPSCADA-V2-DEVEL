<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class UnitController extends BaseController
{
    public function index()
    {

        $data = [
            'siteTitle' => "Kelola Unit",
            'listUnit' => $this->unit->findAll(),
            'listSkema' => $this->skema->AllSkema()
        ];

        return view('dashboard/unit', $data);
    }

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
            'kode' => [
                'label' => "Kode Unit",
                'rules' => 'required|is_unique[unit.kode_unit]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
            'nama' => [
                'label' => "Nama Unit",
                'rules' => 'required|is_unique[unit.nama_unit]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
            'keterangan' => [
                'label' => "Keterangan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addUnitModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('id_skema'),
            'kode_unit' => $this->request->getVar('kode'),
            'nama_unit' => $this->request->getVar('nama'),
            'keterangan' => $this->request->getVar('keterangan'),
            'status' => $this->request->getVar('status')
        ];

        $this->unit->save($data);
        session()->setFlashdata('pesan', 'Unit berhasil ditambahkan!');
        return redirect()->to('/unit');
    }

    // public function import()
    // {
    //     try {
    //         $file = $this->request->getFile('file_exel');
    //         $extension = $file->getClientExtension();

    //         if ($extension != 'xlsx' && $extension != 'xls') {
    //             throw new Exception('Format File Tidak Sesuai!');
    //         }

    //         if ($extension == 'xls') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //         } else {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //         }

    //         $spreadsheet = $reader->load($file);
    //         $unit = $spreadsheet->getActiveSheet()->toArray();

    //         foreach ($unit as $key => $value) {
    //             if ($key == 0) {
    //                 continue;
    //             }

    //             $data = [
    //                 'id_skema' => $value[1],
    //                 'kode_unit' => $value[2],
    //                 'nama_unit' => $value[3],
    //                 'keterangan' => $value[4],
    //                 'status' => $value[5],
    //             ];

    //             $this->unit->insert($data); // Ubah sesuai dengan metode insert Anda
    //         }

    //         session()->setFlashdata('pesan', 'Unit berhasil ditambahkan!');
    //         return redirect()->to('/unit');
    //     } catch (Exception $e) {
    //         session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
    //         session()->setFlashdata('modal_id', 'importExelModal');
    //         session()->setFlashdata('errors', $e->getMessage());

    //         return redirect()->back()->withInput();
    //     }
    // }

    public function import()
    {

        $rules = [
            'file_exel' => [
                'label' => 'File Exel',
                'rules' => 'uploaded[file_exel]|max_size[file_exel,10048]|mime_in[file_exel,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File maksimal 10MB *',
                    'mime_in' => 'Harus berupa file Excel (xls atau xlsx) *',
                ],
            ],
        ];


        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'importExelModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file_exel');
        $extension = $file->getClientExtension();

        if ($extension == 'xls') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $spreadsheet = $reader->load($file);
        $unit = $spreadsheet->getActiveSheet()->toArray();

        print_r($unit);

        foreach ($unit as $key => $value) {
            if ($key == 0) {
                continue;
            }

            $data = [
                'id_skema' => $value[1],
                'kode_unit' => $value[2],
                'nama_unit' => $value[3],
                'keterangan' => $value[4],
                'status' => $value[5],
            ];

            $this->unit->save($data);
        }

        session()->setFlashdata('pesan', 'Unit berhasil ditambahkan!');
        return redirect()->to('/unit');
    }

    public function update()
    {

        $rules = [
            'edit_id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'edit_kode' => [
                'label' => "Kode Unit",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_nama' => [
                'label' => "Nama Unit",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_keterangan' => [
                'label' => "Keterangan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editUnitModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('edit_id_skema'),
            'kode_unit' => $this->request->getVar('edit_kode'),
            'nama_unit' => $this->request->getVar('edit_nama'),
            'keterangan' => $this->request->getVar('edit_keterangan'),
            'status' => $this->request->getVar('edit_status')
        ];

        $this->unit->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Skema berhasil diupdate!');
        return redirect()->to('/unit');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->unit->deleteUnit($id);
        session()->setFlashdata('pesan', 'Unit berhasil dihapus!');
        return redirect()->to('/unit');
    }

    public function getUnit()
    {
        $id_skema = $this->request->getPost('id_skema');
        $Unit = $this->unit->getUnit($id_skema);
        echo '<option>-- Pilih Unit --</option>';
        foreach ($Unit as $key => $value) {
            echo "<option value=" . $value['id_unit'] . ">" . $value['nama_unit'] . "</option>";
        }
    }
}
