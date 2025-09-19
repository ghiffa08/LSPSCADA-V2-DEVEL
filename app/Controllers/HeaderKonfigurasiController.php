<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AsesorModel;

class HeaderKonfigurasiController extends BaseController
{
    /**
     * Menampilkan halaman utama manajemen kop surat.
     */
    public function index()
    {
        $asesorModel = new AsesorModel();

        $data = [
            'siteTitle' => 'Manajemen Kop Surat',
            // Mengambil daftar asesor untuk dropdown di modal
            'assessors' => $asesorModel
                ->select('asesor.id_asesor, users.nama_lengkap')
                ->join('users', 'users.id = asesor.id_user', 'left')
                ->findAll()
        ];

        return view('asesor/ListheaderKonfigurasi', $data);
    }
}
