<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class InstansiController extends BaseController
{
    /**
     * Menampilkan halaman utama manajemen instansi.
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Manajemen Instansi',
        ];

        return view('admin/instansi', $data);
    }
}
