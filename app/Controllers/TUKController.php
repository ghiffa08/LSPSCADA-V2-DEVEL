<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TUKController extends BaseController
{
    public function index()
    {
        $data = [
            'listTUK' => $this->tuk->findAll(),
            'siteTitle' => 'Kelola TUK'
        ];

        return view('dashboard/kelola_tuk', $data);
    }

    public function store()
    {
        $rules = [
            'nama' => [
                'label' => "Nama Tempat Uji Kompetensi",
                'rules' => 'required|is_unique[tuk.nama_tuk]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'is_unique' => '{field} sudah terdaftar.'
                ],
            ],
            'jenis_tuk' => [
                'label' => "jenis Tempat Uji Kompetensi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',

                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addTUKModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_tuk' => $this->request->getVar('nama'),
            'jenis_tuk' => $this->request->getVar('jenis'),
        ];

        $this->tuk->save($data);
        session()->setFlashdata('pesan', 'Tempat TUK berhasil ditambahkan!');
        return redirect()->to('/tempat-tuk');
    }

    public function update()
    {
        $rules = [
            'edit_nama' => [
                'label' => "Nama Tempat Uji Kompetensi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_jenis_tuk' => [
                'label' => "Jenis Tempat Uji Kompetensi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editTUKModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_tuk' => $this->request->getVar('edit_nama'),
            'jenis_tuk' => $this->request->getVar('edit_jenis_tuk'),
        ];


        $this->tuk->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Tempat TUK berhasil diupdate!');
        return redirect()->to('/tempat-tuk');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->tuk->deleteTUK($id);
        session()->setFlashdata('pesan', 'Tempat TUK berhasil dihapus!');
        return redirect()->to('/tempat-tuk');
    }
}
