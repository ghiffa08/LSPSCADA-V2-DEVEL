<?php

namespace App\Services;

use Config\Database;

/**
 * FeedbackService - Handles business logic for feedback operations
 *
 * This service handles operations related to asesi (student) feedback
 * including retrieving data and processing feedback submissions
 */
class FeedbackService
{
    protected $db;
    protected $asesiModel;
    protected $asesmenModel;
    protected $feedbackAsesiModel;
    protected $komponenFeedbackModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->asesiModel = model('AsesiModel');
        $this->asesmenModel = model('AsesmenModel');
        $this->feedbackAsesiModel = model('FeedbackAsesiModel');
        $this->komponenFeedbackModel = model('KomponenFeedbackModel');
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

    /**
     * Get active assessment for an asesi
     *
     * @param string $id_asesi Asesi ID
     * @return array|null Assessment data or null if not found
     */
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
     * Get all feedback data for PDF generation or detailed view
     *
     * @param int $id_feedback Feedback ID
     * @return array Feedback data with details
     * @throws \Exception If feedback not found
     */
    public function getFeedbackData(int $id_feedback): array
    {
        // Get feedback master data
        $feedback = $this->feedbackAsesiModel->getById($id_feedback);

        if (!$feedback) {
            throw new \Exception('Data umpan balik tidak ditemukan');
        }

        // Get feedback details with komponen information
        $detailFeedback = $this->feedbackAsesiModel->getFeedbackDetailsByIdWithKomponen($id_feedback);

        // Format existing data for the view
        $existing_data = [];
        foreach ($detailFeedback as $detail) {
            $existing_data[$detail['id_komponen']] = [
                'jawaban' => $detail['jawaban'],
                'komentar' => $detail['komentar'],
                'pernyataan' => $detail['pernyataan']
            ];
        }

        return [
            'feedback' => $feedback,
            'detailFeedback' => $detailFeedback,
            'existing_data' => $existing_data
        ];
    }

    /**
     * Save feedback data
     *
     * @param array $masterData Master feedback data
     * @param array|null $detailData Detail feedback data
     * @return array Result with success status and ID
     * @throws \Exception If saving fails
     */
    public function saveFeedbackData(array $masterData, ?array $detailData = null): array
    {
        return $this->feedbackAsesiModel->saveFeedbackData($masterData, $detailData);
    }

    /**
     * Check if feedback exists for asesi and skema
     *
     * @param string $id_asesi Asesi ID
     * @param string $id_skema Skema ID
     * @return array Result with exists status and ID if found
     */
    public function checkExistingFeedback(string $id_asesi, string $id_skema): array
    {
        $feedback = $this->feedbackAsesiModel
            ->where([
                'id_asesi' => $id_asesi,
                'id_skema' => $id_skema,
            ])
            ->first();

        return [
            'success' => true,
            'exists' => !empty($feedback),
            'id_feedback' => $feedback ? $feedback['id_feedback'] : null
        ];
    }

    /**
     * Get all komponen feedback
     *
     * @return array Array of komponen items
     */
    public function getAllKomponen(): array
    {
        return $this->komponenFeedbackModel->getAllKomponen();
    }
}
