<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SkemaController extends BaseController
{
    public function index()
    {

        $data = [
            'siteTitle' => 'Kelola Skema',
            'listSkema' => $this->skema->findAll()
        ];

        return view('dashboard/skema', $data);
    }

    public function store()
    {
        $rules = [
            'kode_skema' => [
                'label' => "Kode Skema",
                'rules' => 'required|is_unique[skema.kode_skema]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => 'kode_skema sudah terdaftar.'
                ],
            ],
            'nama_skema' => [
                'label' => "nama Skema",
                'rules' => 'required|is_unique[skema.nama_skema]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => 'nama_skema sudah terdaftar.'
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addSkemaModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_skema' => $this->request->getPost('kode_skema'),
            'nama_skema' => $this->request->getVar('nama_skema'),
            'status' => $this->request->getVar('status')
        ];

        $this->skema->save($data);
        session()->setFlashdata('pesan', 'Skema berhasil ditambahkan!');
        return redirect()->to('/skema');
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
            session()->setFlashdata('modal_id', 'importExelModal');

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
        $skema = $spreadsheet->getActiveSheet()->toArray();

        print_r($skema);

        foreach ($skema as $key => $value) {
            if ($key == 0) {
                continue;
            }

            $data = [
                'kode_skema' => $value[1],
                'nama_skema' => $value[2],
                'status' => $value[3],
            ];

            $this->skema->save($data);
        }

        session()->setFlashdata('pesan', 'Skema berhasil ditambahkan!');
        return redirect()->to('/skema');
    }

    public function update()
    {
        $rules = [
            'edit_kode' => [
                'label' => "Kode Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_nama' => [
                'label' => "nama Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editSkemaModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_skema' => $this->request->getVar('edit_kode'),
            'nama_skema' => $this->request->getVar('edit_nama'),
            'status' => $this->request->getVar('edit_status')
        ];

        $this->skema->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Skema berhasil diupdate!');
        return redirect()->to('/skema');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->skema->deleteSkema($id);
        session()->setFlashdata('pesan', 'Skema berhasil dihapus!');
        return redirect()->to('/skema');;
    }
}
