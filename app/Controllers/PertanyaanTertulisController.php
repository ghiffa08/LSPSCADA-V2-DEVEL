<?php

namespace App\Controllers;

use App\Models\PengajuanAsesmenModel;
use App\Models\PertanyaanTertulisPilihanModel;
use App\Models\PertanyaanTertulisSoalModel;
use App\Models\PertanyaanTertulisModel;
use CodeIgniter\RESTful\ResourceController;

class PertanyaanTertulisController extends ResourceController
{
    protected PertanyaanTertulisModel $pertanyaanTertulisModel;
    protected PertanyaanTertulisSoalModel $pertanyaanTertulisSoalModel;
    protected PertanyaanTertulisPilihanModel $pertanyaanTertulisPilihanModel;

    public function __construct()
    {
        helper('auth');
        $this->pertanyaanTertulisModel = new PertanyaanTertulisModel();
        $this->pertanyaanTertulisSoalModel = new PertanyaanTertulisSoalModel();
        $this->pertanyaanTertulisPilihanModel = new PertanyaanTertulisPilihanModel();
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
     * @param string|null $id_pengajuan ID dari tabel pengajuan_asesmen
     */
    public function show($id_pengajuan = null)
    {
        if (!$id_pengajuan) {
            return redirect()->back()->with('error', 'ID Pengajuan Asesmen tidak disediakan.');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen');

        $pengajuanData = $builder
            ->select('
                pengajuan_asesmen.id_pengajuan, pengajuan_asesmen.id_asesor,
                user_asesi.nama_lengkap,
                user_asesor.nama_lengkap as nama_asesor,
                skema.id_skema, skema.nama_skema, skema.kode_skema
            ')
            ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
            ->join('users as user_asesi', 'user_asesi.id = asesi.id_user')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->join('users as user_asesor', 'user_asesor.id = pengajuan_asesmen.id_asesor', 'left')
            ->where('pengajuan_asesmen.id_pengajuan', $id_pengajuan)
            ->get()->getRowArray();

        if (!$pengajuanData) {
            return redirect()->back()->with('error', 'Data Pengajuan Asesmen tidak ditemukan.');
        }

        // Ambil struktur soal untuk hitung total soal
        $struktur = $this->pertanyaanTertulisModel->getStrukturUjianSkema($pengajuanData['id_skema']);
        $totalSoal = count($struktur['soal_list']);

        // Cek apakah sudah ada jawaban (sudah submit)
        $pertanyaanTertulisModel = new \App\Models\PertanyaanTertulisModel();
        $ujian = $pertanyaanTertulisModel->where('id_pengajuan', $id_pengajuan)
            ->where('id_skema', $pengajuanData['id_skema'])
            ->first();

        $dataJawaban = null;
        $jawabanDijawab = 0;
        $benar = 0;
        if ($ujian) {
            $dataJawaban = $pertanyaanTertulisModel->getExistingJawaban($ujian['id_ujian']);
            $jawabanDijawab = count($dataJawaban);
            // Hitung jumlah jawaban benar berdasarkan is_benar di pilihan
            foreach ($dataJawaban as $jawaban) {
                $soal = $this->pertanyaanTertulisSoalModel->find($jawaban['id_soal']);
                if ($soal['jenis_soal'] == 'PILIHAN_GANDA') {
                    $pilihan = $this->pertanyaanTertulisPilihanModel->find($jawaban['jawaban_pilihan']);
                    if ($pilihan && $pilihan['is_benar'] == 'Y') {
                        $benar++;
                    }
                } elseif ($soal['jenis_soal'] == 'BENAR_SALAH') {
                    if ($jawaban['jawaban_benar_salah'] == 'Y') {
                        $benar++;
                    }
                }
                // Untuk ESSAY, tidak dihitung benar/salah
            }
        }

        $data = [
            'siteTitle'      => 'Pertanyaan Tertulis',
            'pengajuan_data' => $pengajuanData,
            'id_skema'       => $pengajuanData['id_skema'],
            'id_asesor'      => $pengajuanData['id_asesor'],
            'dataJawaban'    => $dataJawaban,
            'totalSoal'      => $totalSoal,
            'jawabanDijawab' => $jawabanDijawab,
            'benar'          => $benar,
        ];

        return view('asesi/pertanyaan_tertulis_cbt', $data);
    }

    /**
     * [FUNGSI BARU] Menampilkan halaman daftar ujian tertulis untuk Asesi.
     */
    public function listAsesi()
    {
        $data = [
            'siteTitle' => 'Daftar Ujian Tertulis',
        ];
        // Hanya menampilkan view, data akan di-load via AJAX
        return view('asesi/list-pertanyaan-tertulis', $data);
    }

    /**
     * [FUNGSI BARU] Endpoint AJAX untuk filter daftar ujian tertulis.
     */
    public function filterUjian()
    {
        // Pastikan ini adalah AJAX request
        if ($this->request->isAJAX()) {
            $filter = $this->request->getGet('filter') ?? 'terbaru';
            $userId = user()->id; // Mengambil ID user yang sedang login

            // Panggil method baru di model untuk mendapatkan data
            $data = $this->pertanyaanTertulisModel->getByUserId($userId, $filter);

            // Kembalikan data dalam format JSON
            return $this->response->setJSON($data);
        }

        // Jika bukan AJAX, tolak akses
        return $this->response->setStatusCode(403, 'Forbidden Access');
    }
}
