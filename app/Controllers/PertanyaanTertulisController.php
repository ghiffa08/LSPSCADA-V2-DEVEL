<?php

namespace App\Controllers;

use App\Models\APL1Model;
use CodeIgniter\RESTful\ResourceController;

class PertanyaanTertulisController extends ResourceController
{
    public function __construct()
    {
        helper('auth');
    }

    /**
     * Menampilkan halaman daftar sesi ujian tertulis.
     */
    public function index()
    {
        $data = ['siteTitle' => 'Manajemen Ujian Tertulis'];
        return view('admin/pertanyaan_tertulis_list', $data);
    }

    /**
     * Menampilkan halaman antarmuka CBT untuk asesi.
     */
    public function show($id_apl1 = null)
    {
        // try {
        if (!$id_apl1) throw new \Exception('ID APL1 (Asesi) tidak disediakan.');

        $apl1Model = new APL1Model();
        $apl1Data = $apl1Model
            ->select('apl1.*, skema.id_skema, skema.nama_skema, skema.kode_skema, asesor.id_asesor, user_asesor.nama_lengkap as nama_asesor')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->join('asesor', 'asesor.id_skema = skema.id_skema', 'left')
            ->join('users as user_asesor', 'user_asesor.id = asesor.id_user', 'left')
            ->find($id_apl1);

        // if (!$apl1Data) throw new \Exception('Data Asesi (APL1) tidak ditemukan.');

        $data = [
            'siteTitle' => 'Pertanyaan Tertulis',
            'apl1_data' => $apl1Data,
            'id_skema' => $apl1Data['id_skema'],
            'id_asesor' => $apl1Data['id_asesor'],
        ];

        return view('asesi/pertanyaan_tertulis_cbt', $data);
        // } catch (\Exception $e) {
        //     return redirect()->back()->with('error', $e->getMessage());
        // }
    }
}
