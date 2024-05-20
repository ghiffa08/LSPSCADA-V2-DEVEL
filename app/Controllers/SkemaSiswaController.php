<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SkemaSiswaController extends BaseController
{
    public function index()
    {

        $data = [

            'dataSkemaSiswa' => $this->skema_siswa->getByIdSiswa(user()->id),
            'listSkema' => $this->skema->findAll(),
            'siteTitle' => 'Skema Siswa',
        ];

        return view('dashboard/skema_siswa', $data);
        // dd($data);
    }

    public function store()
    {
        $existingDataCount = $this->skema_siswa->where('id_siswa', $this->request->getVar('id_siswa'))->countAllResults();

        $data = [
            'id_skema' => $this->request->getVar('id_skema'),
            'id_siswa' => $this->request->getVar('id_siswa'),
            'email_siswa' => $this->request->getVar('email_siswa'),
            'id_skema' => $this->request->getVar('id_skema'),
        ];


        if ($existingDataCount > 0) {
            // Jika data dengan id_siswa yang sama sudah ada, lakukan pembaruan data
            $this->skema_siswa->update($this->request->getVar('id'), $data);
            session()->setFlashdata('pesan', 'Data APL1 berhasil diperbarui');
        } else {
            // Jika tidak, lakukan penyimpanan data baru
            $this->skema_siswa->save($data);
            session()->setFlashdata('pesan', 'Skema Siswa berhasil ditambahkan!');
        }

        return redirect()->to('/skema-siswa');
    }
}
