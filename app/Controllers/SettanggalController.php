<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SettanggalController extends BaseController
{
    public function index()
    {
        $data = [
            'listSettanggal' => $this->settanggalModel->findAll(),
            'siteTitle' => 'Set Tanggal',
        ];

        return view('admin/settanggal', $data);
    }

    public function store()
    {
        $rules = [
            'tanggal' => [
                'label' => "Tanggal Asesi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'keterangan' => [
                'label' => "Keterangan",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'status' => [
                'label' => "Status",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addSettanggalModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'tanggal' => $this->request->getVar('tanggal'),
            'keterangan' => $this->request->getVar('keterangan'),
            'status' => $this->request->getVar('status')
        ];


        $this->settanggal->save($data);
        session()->setFlashdata('pesan', 'Tanggal Asesi berhasil di set!');
        return redirect()->to('/master/tanggal');
    }

    public function update()
    {
        $rules = [
            'edit_tanggal' => [
                'label' => "Tanggal Asesi",
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
            'edit_status' => [
                'label' => "Status",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editSettanggalModal-' . $this->request->getVar('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'tanggal' => $this->request->getVar('edit_tanggal'),
            'keterangan' => $this->request->getVar('edit_keterangan'),
            'status' => $this->request->getVar('edit_status')
        ];


        $this->settanggal->update($this->request->getVar('edit_id'), $data);
        session()->setFlashdata('pesan', 'Tanggal Berhasil diupdate!');
        return redirect()->to('/master/tanggal');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->settanggal->deleteSettanggal($id);
        session()->setFlashdata('pesan', 'Tanggal Asesi berhasil dihapus!');
        return redirect()->to('/master/tanggal');;
    }
}
