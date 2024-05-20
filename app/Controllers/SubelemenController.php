<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SubelemenController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => "Kelola Subelemen",
            'listSubelemen' => $this->subelemen->findAll(),
            'listSkema' => $this->skema->AllSkema(),
            'listUnit' => $this->unit->AllUnit(),
            'listElemen' => $this->elemen->AllElemen(),
        ];

        return view('dashboard/subelemen', $data);
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
            'id_unit' => [
                'label' => 'ID Unit',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'id_elemen' => [
                'label' => 'ID Elemen',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'kode' => [
                'label' => "Kode Subelemen",
                'rules' => 'required|is_unique[subelemen.kode_subelemen]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
            'pertanyaan' => [
                'label' => "Pertanyaan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',

                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addSubelemenModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('id_skema'),
            'id_unit' => $this->request->getVar('id_unit'),
            'id_elemen' => $this->request->getVar('id_elemen'),
            'kode_subelemen' => $this->request->getVar('kode'),
            'pertanyaan' => $this->request->getVar('pertanyaan')
        ];

        $this->subelemen->save($data);
        session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
        return redirect()->to('/subelemen');
    }

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
                'id_unit' => $value[2],
                'id_elemen' => $value[3],
                'kode_subelemen' => $value[4],
                'pertanyaan' => $value[5],
            ];

            $this->subelemen->save($data);
        }

        session()->setFlashdata('pesan', 'Subelemen berhasil ditambahkan!');
        return redirect()->to('/subelemen');
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
            'edit_id_unit' => [
                'label' => 'ID Unit',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'edit_id_elemen' => [
                'label' => 'ID Elemen',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                ],
            ],
            'edit_kode' => [
                'label' => "Kode Subelemen",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_pertanyaan' => [
                'label' => "Pertanyaan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',

                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editSubelemenModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('edit_id_skema'),
            'id_unit' => $this->request->getVar('edit_id_unit'),
            'id_elemen' => $this->request->getVar('edit_id_elemen'),
            'kode_subelemen' => $this->request->getVar('edit_kode'),
            'pertanyaan' => $this->request->getVar('edit_pertanyaan')
        ];

        $this->subelemen->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Subelemen berhasil diupdate!');
        return redirect()->to('/subelemen');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->subelemen->deleteSubelemen($id);
        session()->setFlashdata('pesan', 'Subelemen berhasil dihapus!');
        return redirect()->to('/subelemen');
    }
}
