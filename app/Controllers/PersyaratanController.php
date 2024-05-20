<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PersyaratanController extends BaseController
{
    public function index()
    {
        $data = [
            'listPersyaratan' => $this->persyaratan->getAll(),
            'listSkema' => $this->skema->AllSkema(),
            'siteTitle' => 'Pengelolaan Persyaratan'
        ];

        return view('dashboard/persyaratan', $data);
    }

    public function store()
    {
        $rules = [
            'id_skema' => [
                'label' => "ID Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'keterangan_bukti' => [
                'label' => "Keterangan Bukti",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'persyaratan' => [
                'label' => "Persyaratan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addSyaratModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('id_skema'),
            'keterangan_bukti' => $this->request->getVar('keterangan_bukti'),
            'syarat' => $this->request->getVar('persyaratan'),
        ];


        $this->persyaratan->save($data);
        session()->setFlashdata('pesan', 'Persayaran Berhasil ditambahkan!');
        return redirect()->to('/persyaratan');
    }

    public function update()
    {
        $rules = [
            'edit_id_skema' => [
                'label' => "ID Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_keterangan_bukti' => [
                'label' => "Keterangan Bukti",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_persyaratan' => [
                'label' => "Persyaratan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editSyaratModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getVar('edit_id_skema'),
            'keterangan_bukti' => $this->request->getVar('edit_keterangan_bukti'),
            'syarat' => $this->request->getVar('edit_persyaratan'),
        ];


        $this->persyaratan->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Persayaran Berhasil diupdate!');
        return redirect()->to('/persyaratan');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->persyaratan->deleteSyarat($id);
        session()->setFlashdata('pesan', 'Peryaratan berhasil dihapus!');
        return redirect()->to('/persyaratan');
    }
}
