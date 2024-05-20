<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ElemenController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => "Kelola Elemen",
            'listElemen' => $this->elemen->findAll(),
            'listSkema' => $this->skema->AllSkema(),
            'listUnit' => $this->unit->AllUnit(),

        ];

        return view('dashboard/elemen', $data);
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
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addElemenModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('id_skema'),
            'id_unit' => $this->request->getVar('id_unit'),
            'kode_elemen' => $this->request->getVar('kode'),
            'nama_elemen' => $this->request->getVar('nama')
        ];

        $this->elemen->save($data);
        session()->setFlashdata('pesan', 'Elemen berhasil ditambahkan!');
        return redirect()->to('/elemen');
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

        foreach ($unit as $key => $value) {
            if ($key == 0) {
                continue;
            }

            $data = [
                'id_skema' => $value[1],
                'id_unit' => $value[2],
                'kode_elemen' => $value[3],
                'nama_elemen' => $value[4],
            ];

            $this->elemen->save($data);
        }

        session()->setFlashdata('pesan', 'Elemen berhasil ditambahkan!');
        return redirect()->to('/elemen');
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
            'edit_kode' => [
                'label' => "Kode Elemen",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_nama' => [
                'label' => "Nama Elemen",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editElemenModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('edit_id_skema'),
            'id_unit' => $this->request->getVar('edit_id_unit'),
            'kode_elemen' => $this->request->getVar('edit_kode'),
            'nama_elemen' => $this->request->getVar('edit_nama')
        ];

        $this->elemen->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Elemen berhasil diupdate!');
        return redirect()->to('/elemen');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->elemen->deleteElemen($id);
        session()->setFlashdata('pesan', 'Elemen berhasil dihapus!');
        return redirect()->to('/elemen');
    }

    public function getElemen()
    {
        $id_unit = $this->request->getPost('id_unit');
        $Elemen = $this->elemen->getElemen($id_unit);
        echo '<option>-- Pilih Elemen --</option>';
        foreach ($Elemen as $key => $value) {
            echo "<option value=" . $value['id_elemen'] . ">" . $value['nama_elemen'] . "</option>";
        }
    }
}
