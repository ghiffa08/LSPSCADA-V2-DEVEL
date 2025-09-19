<?php

namespace App\Services;

use App\Models\FeedbackAsesiModel;
use App\Models\DetailFeedbackAsesiModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Cache\CacheInterface;

/**
 * FeedbackService
 * Class service untuk mengelola business logic terkait feedback asesi.
 */
class FeedbackService
{
    protected FeedbackAsesiModel $feedbackAsesiModel;
    protected DetailFeedbackAsesiModel $detailFeedbackModel;
    protected BaseConnection $db;
    protected CacheInterface $cache;

    public function __construct()
    {
        $this->feedbackAsesiModel = new FeedbackAsesiModel();
        $this->detailFeedbackModel = new DetailFeedbackAsesiModel();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Membuat atau memperbarui data feedback beserta detailnya.
     *
     * @param array $data Data dari controller
     * @return array Hasil operasi
     */
    public function saveFeedback(array $data): array
    {
        $validatedData = $this->validateAndSanitizeInput($data);
        if (isset($validatedData['error'])) {
            log_message('error', 'FeedbackService validation error: ' . $validatedData['error']);
            return ['success' => false, 'message' => $validatedData['error']];
        }

        $this->db->transStart();

        try {
            $this->validateForeignKeys($validatedData);

            $feedbackData = [
                'id_asesor'       => $validatedData['id_asesor'],
                'id_skema'        => $validatedData['id_skema'],
                'id_asesi'        => $validatedData['id_asesi'],
                'tanggal_mulai'   => $validatedData['tanggal_mulai'],
                'tanggal_selesai' => $validatedData['tanggal_selesai'],
                'catatan_lain'    => $validatedData['catatan_lain'],
            ];

            // Menggunakan metode save dari model yang menangani insert/update
            // Cek apakah ada record yang sudah ada terlebih dahulu
            $existing = $this->feedbackAsesiModel
                ->where('id_asesor', $feedbackData['id_asesor'])
                ->where('id_skema', $feedbackData['id_skema'])
                ->where('id_asesi', $feedbackData['id_asesi'])
                ->first();

            $id_feedback = $existing['id_feedback'] ?? null;

            if ($id_feedback) {
                // Update
                $this->feedbackAsesiModel->update($id_feedback, $feedbackData);
            } else {
                // Insert
                $id_feedback = $this->feedbackAsesiModel->insert($feedbackData);
            }

            if (!$id_feedback) {
                throw new \Exception('Gagal mendapatkan ID Feedback setelah operasi save.');
            }

            // Proses detail feedback (Delete then Insert)
            if (isset($validatedData['details'])) {
                $this->upsertDetails($id_feedback, $validatedData['details']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal.');
            }

            // Hapus cache yang relevan
            $this->clearFeedbackCaches($id_feedback, $validatedData['id_asesor'], $validatedData['id_asesi']);

            return [
                'success' => true,
                'message' => 'Feedback berhasil disimpan.',
                'data'    => ['id_feedback' => $id_feedback]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'FeedbackService saveFeedback Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan feedback: ' . $e->getMessage()];
        }
    }

    /**
     * Mengambil data feedback lengkap dengan detailnya.
     *
     * @param int $id_feedback
     * @return array
     */
    public function getFeedbackWithDetails(int $id_feedback): array
    {
        $cacheKey = "feedback_details_{$id_feedback}";
        if ($cached = $this->cache->get($cacheKey)) {
            return ['success' => true, 'data' => $cached];
        }

        try {
            $feedback = $this->feedbackAsesiModel->getById($id_feedback);
            if (!$feedback) {
                return ['success' => false, 'message' => 'Feedback tidak ditemukan.'];
            }

            $details = $this->detailFeedbackModel->getDetailsWithKomponen($id_feedback);
            $summary = $this->calculateFeedbackSummary($id_feedback);

            $result = [
                'feedback' => $feedback,
                'details' => $details,
                'summary' => $summary
            ];

            $this->cache->save($cacheKey, $result, 3600); // Cache selama 1 jam

            return ['success' => true, 'data' => $result];
        } catch (\Exception $e) {
            log_message('error', 'FeedbackService getFeedbackWithDetails Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengambil data: ' . $e->getMessage()];
        }
    }

    /**
     * Get asesi data by user ID
     *
     * @param int $userId User ID
     * @return array|null Asesi data or null if not found
     */
    public function getAsesiDataByUserId(int $userId): ?array
    {
        return $this->db->table('asesi')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
    }


    public function getActiveAssessment(string $id_asesi): ?array
    {
        return $this->db->table('pengajuan_asesmen')
            ->select('
                pengajuan_asesmen.id_asesmen,
                pengajuan_asesmen.id_asesi,
                asesmen.id_skema,
                asesmen.id_asesor,
                skema.kode_skema,
                skema.nama_skema,
                users.fullname AS nama_asesor
            ')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->join('users', 'users.id = asesmen.id_asesor', 'left')
            ->where('pengajuan_asesmen.id_asesi', $id_asesi)
            ->get()
            ->getRowArray();
    }

    /**
     * Menghapus data feedback dan detailnya secara transaksional.
     *
     * @param int $id_feedback
     * @param int $id_asesor_pemilik
     * @return array
     */
    public function deleteFeedback(int $id_feedback, int $id_asesor_pemilik): array
    {
        $this->db->transStart();
        try {
            $feedback = $this->feedbackAsesiModel->find($id_feedback);
            if (!$feedback) {
                return ['success' => false, 'message' => 'Feedback tidak ditemukan.'];
            }

            // Verifikasi kepemilikan
            if ($feedback['id_asesor'] != $id_asesor_pemilik) {
                return ['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk menghapus data ini.'];
            }

            // Hapus detail
            $this->db->table('detail_feedback_asesi')->where('id_feedback', $id_feedback)->delete();

            // Hapus master
            $this->feedbackAsesiModel->delete($id_feedback);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi database untuk penghapusan gagal.');
            }

            // Hapus cache
            $this->clearFeedbackCaches($id_feedback, $feedback['id_asesor'], $feedback['id_asesi']);

            return ['success' => true, 'message' => 'Feedback berhasil dihapus.'];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'FeedbackService deleteFeedback Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus feedback: ' . $e->getMessage()];
        }
    }

    // --- METODE PRIVATE HELPER ---

    /**
     * Validasi dan sanitasi input data.
     */
    private function validateAndSanitizeInput(array $data): array
    {
        $sanitized = [];
        $requiredFields = ['id_asesor', 'id_skema', 'id_asesi', 'tanggal_mulai', 'tanggal_selesai'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field {$field} wajib diisi."];
            }
        }

        $sanitized['id_asesor'] = filter_var($data['id_asesor'], FILTER_VALIDATE_INT);
        $sanitized['id_skema'] = filter_var($data['id_skema'], FILTER_VALIDATE_INT);
        $sanitized['id_asesi'] = trim(strip_tags($data['id_asesi']));
        $sanitized['tanggal_mulai'] = date('Y-m-d', strtotime($data['tanggal_mulai']));
        $sanitized['tanggal_selesai'] = date('Y-m-d', strtotime($data['tanggal_selesai']));
        $sanitized['catatan_lain'] = isset($data['catatan_lain']) ? trim(strip_tags($data['catatan_lain'])) : null;

        if (isset($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $detail) {
                $sanitized['details'][] = [
                    'id_komponen' => filter_var($detail['id_komponen'], FILTER_VALIDATE_INT),
                    'jawaban'     => in_array($detail['jawaban'], [0, 1]) ? $detail['jawaban'] : null,
                    'komentar'    => isset($detail['komentar']) ? substr(trim(strip_tags($detail['komentar'])), 0, 1000) : ''
                ];
            }
        }
        return $sanitized;
    }

    /**
     * Validasi keberadaan foreign keys.
     */
    private function validateForeignKeys(array $data): void
    {
        if (!$this->db->table('asesor')->where('id_asesor', $data['id_asesor'])->get()->getRow()) {
            throw new \Exception("Asesor dengan ID {$data['id_asesor']} tidak ditemukan.");
        }
        if (!$this->db->table('skema')->where('id_skema', $data['id_skema'])->get()->getRow()) {
            throw new \Exception("Skema dengan ID {$data['id_skema']} tidak ditemukan.");
        }
        if (!$this->db->table('apl1')->where('id_apl1', $data['id_asesi'])->get()->getRow()) {
            throw new \Exception("Asesi (APL1) dengan ID {$data['id_asesi']} tidak ditemukan.");
        }
    }

    /**
     * Menghapus detail lama dan menyisipkan yang baru (UPSERT).
     */
    private function upsertDetails(int $id_feedback, array $details): void
    {
        // Hapus detail yang sudah ada
        $this->db->table('detail_feedback_asesi')->where('id_feedback', $id_feedback)->delete();

        // Siapkan data baru untuk batch insert
        $batchData = [];
        foreach ($details as $detail) {
            if ($detail['id_komponen'] && $detail['jawaban'] !== null) {
                $batchData[] = [
                    'id_feedback' => $id_feedback,
                    'id_komponen' => $detail['id_komponen'],
                    'jawaban'     => $detail['jawaban'],
                    'komentar'    => $detail['komentar']
                ];
            }
        }

        if (!empty($batchData)) {
            $this->db->table('detail_feedback_asesi')->insertBatch($batchData);
        }
    }

    /**
     * Menghitung ringkasan statistik jawaban.
     */
    private function calculateFeedbackSummary(int $id_feedback): array
    {
        return $this->db->table('detail_feedback_asesi')
            ->select('
                COUNT(*) as total_komponen,
                SUM(CASE WHEN jawaban = 1 THEN 1 ELSE 0 END) as jawaban_ya,
                SUM(CASE WHEN jawaban = 0 THEN 1 ELSE 0 END) as jawaban_tidak
            ')
            ->where('id_feedback', $id_feedback)
            ->get()->getRowArray() ?? ['total_komponen' => 0, 'jawaban_ya' => 0, 'jawaban_tidak' => 0];
    }

    /**
     * Menghapus cache yang terkait dengan feedback.
     */
    private function clearFeedbackCaches(int $id_feedback, int $id_asesor, string $id_asesi): void
    {
        $this->cache->delete("feedback_details_{$id_feedback}");
        // Tambahkan key cache lain jika ada (misal, statistik per asesor)
        // $this->cache->delete("feedback_stats_{$id_asesor}");
    }
}
