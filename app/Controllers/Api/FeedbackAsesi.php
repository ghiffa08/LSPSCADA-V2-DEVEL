<?php

namespace App\Controllers\Api;

use Config\Database;
use App\Models\FeedbackAsesiModel;
use App\Models\PengajuanAsesmenModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;

class FeedbackAsesi extends DataTableController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        helper('auth');
        $this->model = new FeedbackAsesiModel();
        $this->db = Database::connect();

        // PENYESUAIAN: columnMap diubah untuk menggunakan relasi baru
        $this->columnMap = [
            1 => 'asesi_user.nama_lengkap',
            2 => 'asesor_user.nama_lengkap',
            3 => 'skema.nama_skema',
            4 => 'feedback_asesi.tanggal_selesai',
        ];
    }

    /**
     * PENYESUAIAN: Tidak lagi menggunakan ObservasiModel.
     * Mengambil daftar asesi yang pengajuannya sudah diterima untuk skema tertentu.
     */
    public function getSkemaDetails(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        $id_skema = $this->request->getGet('id_skema');
        if (!$id_skema) return $this->fail('ID Skema diperlukan.', 400);

        try {
            $skemaModel = new \App\Models\SkemaModel();
            $skema = $skemaModel->find($id_skema);
            if (!$skema) return $this->failNotFound('Skema tidak ditemukan.');

            // Mengambil daftar pengajuan yang relevan
            $pengajuanModel = new PengajuanAsesmenModel();
            $pengajuanList = $pengajuanModel
                ->select('pengajuan_asesmen.id_pengajuan, users.nama_lengkap')
                ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                ->join('users', 'users.id = asesi.id_user')
                ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                ->where('asesmen.id_skema', $id_skema)
                ->whereIn('pengajuan_asesmen.status_pengajuan', ['diterima', 'selesai'])
                ->findAll();

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'pengajuan_list' => $pengajuanList, // Mengganti nama variabel
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
     * PENYESUAIAN: Menggunakan id_pengajuan sebagai kunci utama.
     */
    public function loadFeedback(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        $id_pengajuan = $this->request->getGet('id_pengajuan');
        if (!$id_pengajuan) return $this->fail('ID Pengajuan diperlukan.', 400);

        try {
            $existingFeedback = $this->model->where('id_pengajuan', $id_pengajuan)->first();
            $id_feedback = $existingFeedback['id_feedback'] ?? null;
            $existing_data = $id_feedback ? $this->model->getExistingFeedback($id_feedback) : [];
            $komponen = $this->model->getKomponenFeedback();

            return $this->respond([
                'success' => true,
                'komponen' => $komponen,
                'feedback' => $existingFeedback,
                'existing_data' => $existing_data,
                'id_feedback' => $id_feedback,
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal memuat data feedback: ' . $e->getMessage());
        }
    }

    /**
     * PENYESUAIAN: Logika save disederhanakan dan diperkuat.
     */
    public function save()
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        $postData = $this->request->getPost();

        $rules = [
            'id_pengajuan' => 'required|string|max_length[36]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) return $this->fail($this->validator->getErrors(), 400);

        try {
            // Ambil data penting dari pengajuan untuk melengkapi data master
            $pengajuanModel = new PengajuanAsesmenModel();
            $pengajuan = $pengajuanModel
                ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                ->where('id_pengajuan', $postData['id_pengajuan'])->first();

            if (!$pengajuan) return $this->failNotFound('Data pengajuan tidak ditemukan.');

            $masterData = [
                'id_pengajuan'    => $postData['id_pengajuan'],
                'id_asesor'       => $pengajuan['id_asesor'],
                'id_skema'        => $pengajuan['id_skema'],
                'tanggal_mulai'   => $postData['tanggal_mulai'],
                'tanggal_selesai' => $postData['tanggal_selesai'],
                'catatan_lain'    => $postData['catatan_lain'] ?? null,
            ];

            $detailData = ['jawaban'  => $postData['jawaban'] ?? [], 'komentar' => $postData['komentar'] ?? []];

            $id_feedback = $this->model->saveFeedbackData($masterData, $detailData);

            if ($id_feedback) {
                return $this->respondCreated(['success' => true, 'message' => 'Data feedback berhasil disimpan.', 'id_feedback' => $id_feedback, 'token' => csrf_hash()]);
            }
            return $this->fail('Gagal menyimpan data feedback karena kesalahan transaksi.');
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan pada sistem: ' . $e->getMessage());
        }
    }

    /**
     * PENYESUAIAN: Logika delete disederhanakan.
     */
    public function deleteFeedback($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        if (!$id) return $this->fail('ID Feedback tidak valid', 400);

        try {
            $feedback = $this->model->find($id);
            if (!$feedback) return $this->failNotFound('Data feedback tidak ditemukan.');

            // Hapus data dalam transaksi
            $this->db->transStart();
            $this->db->table('detail_feedback_asesi')->where('id_feedback', $id)->delete();
            $this->model->delete($id);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) return $this->fail('Gagal menghapus data feedback.');

            return $this->respondDeleted(['success' => true, 'message' => 'Data feedback berhasil dihapus.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
