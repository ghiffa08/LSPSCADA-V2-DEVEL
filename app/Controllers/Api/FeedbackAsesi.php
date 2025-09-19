<?php

namespace App\Controllers\Api;

use Config\Database;
use App\Models\ObservasiModel; // Digunakan untuk mengambil daftar asesi
use App\Models\FeedbackAsesiModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;

/**
 * Feedback Asesi API Controller
 * Diadaptasi dari Observasi API Controller untuk mengelola feedback dari asesi.
 */
class FeedbackAsesi extends DataTableController
{
    private $id_asesor;
    protected $db;

    public function __construct()
    {
        parent::__construct();

        helper('auth');

        // Inisialisasi model yang akan digunakan
        $this->model = new FeedbackAsesiModel(); // Model utama untuk DataTable
        $this->db = Database::connect();

        // Mengambil id_asesor dari user yang sedang login
        $user_id = user()->id;
        $asesorModel = new \App\Models\AsesorModel();
        $asesor = $asesorModel->where('id_user', $user_id)->first();

        // if (!$asesor) {
        //     // Sebaiknya ditangani dengan exception atau redirect di production
        //     throw new \RuntimeException('User tidak terdaftar sebagai asesor.');
        // }

        $this->id_asesor = $asesor['id_asesor'] ?? '';

        // Mapping kolom untuk server-side ordering DataTable
        $this->columnMap = [
            0 => null, // Kolom nomor
            1 => 'apl1.nama_siswa',
            2 => 'asesor_user.nama_lengkap',
            3 => 'skema.nama_skema',
            4 => 'feedback_asesi.tanggal_selesai',
            5 => null // Kolom aksi
        ];
    }

    /**
     * Mengambil detail Skema dan daftar Asesi (APL1) yang sudah divalidasi.
     * Logikanya sama dengan di Observasi.
     */
    public function getSkemaDetails(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses langsung tidak diizinkan.');
        }

        $id_skema = $this->request->getGet('id_skema');

        if (!$id_skema || !filter_var($id_skema, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Skema diperlukan dan harus berupa angka.', 400);
        }

        try {
            $skemaModel = new \App\Models\SkemaModel();
            $skema = $skemaModel->find($id_skema);
            if (!$skema) {
                return $this->failNotFound('Skema tidak ditemukan.');
            }

            // Menggunakan ObservasiModel untuk mengambil daftar APL1 yang valid, karena fungsinya sudah ada
            $observasiModel = new ObservasiModel();
            $apl1List = $observasiModel->getValidatedApl1BySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'apl1_list' => $apl1List,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[FeedbackAsesi] ' . $e->getMessage());
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
     * Memuat data untuk form feedback via AJAX.
     * Mengambil komponen pertanyaan dan jawaban yang sudah ada (jika ada).
     */
    public function loadFeedback(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses langsung tidak diizinkan.');
        }

        $id_skema = $this->request->getGet('id_skema');
        $id_apl1 = $this->request->getGet('id_apl1'); // id_asesi di tabel feedback = id_apl1

        if (!$id_skema || !$id_apl1) {
            return $this->fail('ID Skema dan ID APL1 diperlukan.', 400);
        }

        try {
            // Cek apakah feedback sudah ada
            $existingFeedback = $this->model
                ->where('id_asesor', $this->id_asesor)
                ->where('id_skema', $id_skema)
                ->where('id_asesi', $id_apl1)
                ->first();

            $id_feedback = $existingFeedback['id_feedback'] ?? null;
            $existing_data = [];

            if ($id_feedback) {
                $existing_data = $this->model->getExistingFeedback($id_feedback);
            }

            // Ambil daftar komponen/pertanyaan feedback
            $komponen = $this->model->getKomponenFeedback();

            return $this->respond([
                'success' => true,
                'komponen' => $komponen,
                'feedback' => $existingFeedback,
                'existing_data' => $existing_data,
                'id_feedback' => $id_feedback,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[FeedbackAsesi] ' . $e->getMessage());
            return $this->fail('Gagal memuat data feedback: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan data feedback dari asesi.
     * Menangani create dan update (UPSERT) secara transaksional.
     */
    public function save()
    {
        if (! $this->request->isAJAX()) {
            return $this->failUnauthorized('Akses langsung tidak diizinkan.');
        }

        $postData = $this->request->getPost();

        // Validasi
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_pengajuan' => 'required|string|max_length[36]',
            // 'id_asesi'     => 'required|integer',
            // 'id_asesor'    => 'required|integer',
            'id_skema'     => 'required|integer',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors(), 400);
        }

        try {
            $model = new FeedbackAsesiModel();

            // Data untuk tabel master 'feedback_asesi'
            $masterData = [
                'id_pengajuan'    => $postData['id_pengajuan'],
                // 'id_asesi'        => $postData['id_asesi'],
                // 'id_asesor'       => $postData['id_asesor'],
                'id_skema'        => $postData['id_skema'],
                'tanggal_mulai'   => $postData['tanggal_mulai'],
                'tanggal_selesai' => $postData['tanggal_selesai'],
                'catatan_lain'    => $postData['catatan_lain'] ?? null,
            ];

            // Data untuk tabel detail 'detail_feedback_asesi'
            $detailData = [
                'jawaban'  => $postData['jawaban'] ?? [],
                'komentar' => $postData['komentar'] ?? [],
            ];

            // Panggil metode di model yang sudah transaksional
            $id_feedback = $model->saveFeedbackData($masterData, $detailData);

            if ($id_feedback) {
                return $this->respondCreated([
                    'success'     => true,
                    'message'     => 'Data feedback berhasil disimpan.',
                    'id_feedback' => $id_feedback,
                    'token'       => csrf_hash()
                ]);
            } else {
                return $this->fail('Gagal menyimpan data feedback karena kesalahan transaksi.');
            }
        } catch (\Exception $e) {
            log_message('error', '[API/FeedbackController] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada sistem: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data feedback beserta detailnya.
     */
    public function deleteFeedback($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses langsung tidak diizinkan.');
        }

        if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Feedback tidak valid', 400);
        }

        try {
            // Verifikasi kepemilikan feedback oleh asesor yang login
            $feedback = $this->model
                ->where('id_feedback', $id)
                ->where('id_asesor', $this->id_asesor)
                ->first();

            if (!$feedback) {
                return $this->failNotFound('Data feedback tidak ditemukan atau Anda tidak memiliki akses.');
            }

            // Hapus data dalam transaksi
            $this->db->transStart();
            $this->db->table('detail_feedback_asesi')->where('id_feedback', $id)->delete();
            $this->model->delete($id);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->fail('Gagal menghapus data feedback.');
            }

            return $this->respondDeleted([
                'success' => true,
                'message' => 'Data feedback berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[FeedbackAsesi] ' . $e->getMessage());
            return $this->fail('Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
