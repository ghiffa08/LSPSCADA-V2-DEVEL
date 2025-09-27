<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InstansiModel; // Ganti dengan InstansiModel

class HeaderKonfigurasiController extends BaseController
{
    /**
     * Menampilkan halaman utama manajemen kop surat.
     */
    public function index()
    {
        $instansiModel = new InstansiModel();

        $data = [
            'siteTitle' => 'Manajemen Kop Surat',
            // Mengambil daftar instansi untuk dropdown di modal
            'instansiList' => $instansiModel->orderBy('nama_instansi', 'ASC')->findAll()
        ];

        return view('admin/header_konfigurasi', $data);
    }
}
