<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AsesmenController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => 'Kelola Asesmen',
            'listSkema' => $this->skema->AllSkema(),
            'listAsesmen' => $this->asesmen->getAllAsesmen(),
            'listSettanggal' => $this->settanggal->findAll(),
            'listTUK' => $this->tuk->findAll(),
        ];

        return view('dashboard/kelola_asesmen', $data);
    }

    public function store()
    {
        $rules = [
            'skema_sertifikasi' => [
                'label' => "Skema Sertifikasi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'jadwal_sertifikasi' => [
                'label' => "Tanggal Sertifikasi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'tuk' => [
                'label' => "Jenis Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addAsesmenModal'); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getPost('skema_sertifikasi'),
            'id_tanggal' => $this->request->getVar('jadwal_sertifikasi'),
            'id_tuk' => $this->request->getVar('tuk'),
        ];

        $this->asesmen->save($data);
        session()->setFlashdata('pesan', 'Asesmen berhasil ditambahkan!');
        return redirect()->to('/asesmen');
    }

    public function update()
    {
        $rules = [
            'edit_skema_sertifikasi' => [
                'label' => "Skema Sertifikasi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_jadwal_sertifikasi' => [
                'label' => "Tanggal Sertifikasi",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_tuk' => [
                'label' => "Jenis Skema",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editAsesmenModal-' . $this->request->getPost('edit_id')); // Tetapkan id modal dalam flash dat

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_skema' => $this->request->getPost('edit_skema_sertifikasi'),
            'id_tanggal' => $this->request->getVar('edit_jadwal_sertifikasi'),
            'id_tuk' => $this->request->getVar('edit_tuk'),
        ];

        $this->asesmen->update($this->request->getPost('edit_id'), $data);
        session()->setFlashdata('pesan', 'Asesmen berhasil diupdate!');
        return redirect()->to('/asesmen');
    }

    public function delete()
    {
        $id = $this->request->getVar($this->request->getPost('edit_id'));
        $this->asesmen->delete($id);
        session()->setFlashdata('pesan', 'Asesmen berhasil dihapus!');
        return redirect()->to('/asesmen');;
    }

    public function getJadwal()
    {
        $id_skema = $this->request->getPost('id_skema');

        $jadwal = $this->asesmen->getJadwal($id_skema);

        $options_tanggal = '';
        $options_tuk = '';
        $id_asesmen = '';

        foreach ($jadwal as $key => $row) {
            $options_tanggal .= '<option value="' . $row['id_tanggal'] . '">' . $row['tanggal'] . '</option>';
            $options_tuk .= '<option value="' . $row['id_tuk'] . '">' . $row['nama_tuk'] . '</option>';
            $id_asesmen = $row['id_asesmen'];
        }

        $response = [
            'options_tanggal' => $options_tanggal,
            'options_tuk' => $options_tuk,
            'id_asesmen' => $id_asesmen
        ];

        echo json_encode($response);
    }



    public function getTuk()
    {
        $id_skema = $this->request->getPost('id_skema');

        $tuk = $this->asesmen->getTuk($id_skema);

        foreach ($tuk as $key => $row) {
            echo  '<option value="' . $row['id_tuk'] . '">' . $row['nama_tuk'] . '</option>';
        }
    }
}
