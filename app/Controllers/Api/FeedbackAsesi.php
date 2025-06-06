<?php

namespace App\Controllers\Api;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\FeedbackService;

class FeedbackAsesi extends DataTableController
{
    private int $id_user;
    protected $feedbackService;

    public function __construct()
    {
        parent::__construct();

        $this->model = $this->feedbackAsesiModel;
        $this->id_user = user()->id ?? 0;
        $this->feedbackService = service('feedback');

        // Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'asesi_user.fullname',
            2 => 'asesor.fullname',
            3 => 'skema.nama_skema',
            4 => 'feedback_asesi.tanggal_mulai',
            5 => 'feedback_asesi.tanggal_selesai',
            6 => null // No ordering for action column
        ];
    }

    /**
     * Get all komponen umpan balik
     */
    public function getKomponen(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        try {
            // Use feedback service to get komponen data
            $komponen = $this->feedbackService->getAllKomponen();

            return $this->respond([
                'success' => true,
                'komponen' => $komponen
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting komponen umpan balik: ' . $e->getMessage());
            return $this->fail('Gagal memuat komponen umpan balik: ' . $e->getMessage());
        }
    }

    /**
     * Check if feedback already exists for the given parameters
     */
    public function checkExisting()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        try {
            $id_asesi = $this->request->getGet('id_asesi');
            $id_skema = $this->request->getGet('id_skema');

            if (empty($id_asesi) || empty($id_skema)) {
                return $this->fail('ID Asesi dan ID Skema diperlukan', 400);
            }

            // Use feedback service to check for existing feedback
            $result = $this->feedbackService->checkExistingFeedback($id_asesi, $id_skema);

            return $this->respond($result);
        } catch (\Exception $e) {
            log_message('error', 'Error checking existing feedback: ' . $e->getMessage());
            return $this->fail('Gagal memeriksa data: ' . $e->getMessage());
        }
    }

    /**
     * Get skema details and available asesi
     */
    public function getSkemaDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_skema = $this->request->getGet('id_skema');

        if (!$id_skema) {
            return $this->fail('ID Skema diperlukan', 400);
        }

        try {
            // Get skema details
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                return $this->fail('Skema tidak ditemukan', 404);
            }

            // Get asesi list for this skema
            $asesi = $this->observasiModel->getAsesiBySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'asesi' => $asesi
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema details: ' . $e->getMessage());
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
     * Load feedback data via AJAX
     */
    public function loadFeedback()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_feedback = $this->request->getGet('id_feedback');

        if (!$id_feedback) {
            return $this->fail('ID Feedback diperlukan', 400);
        }

        try {
            // Use feedback service to get feedback data
            $data = $this->feedbackService->getFeedbackData($id_feedback);
            return $this->respond([
                'success' => true,
                'feedback' => $data['feedback'],
                'komponen' => $this->feedbackService->getAllKomponen(),
                'existing_data' => $data['existing_data']
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading feedback data: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Save feedback data
     */
    public function save()
    {
        // Determine request type
        $isAjax = $this->request->isAJAX();

        // Get data from request
        $id_feedback = $this->request->getPost('id_feedback');
        $id_asesi = $this->request->getPost('id_asesi');
        $id_skema = $this->request->getPost('id_skema');
        $id_asesmen = $this->request->getPost('id_asesmen');
        $id_asesor = $this->request->getPost('id_asesor');
        $tanggal_mulai = $this->request->getPost('tanggal_mulai');
        $tanggal_selesai = $this->request->getPost('tanggal_selesai');
        $catatan_lain = $this->request->getPost('catatan_lain');
        $komponen = $this->request->getPost('komponen');
        $komentar = $this->request->getPost('komentar');

        // Validate required fields
        if (empty($id_asesi) || empty($id_skema)) {
            return $this->handleErrorResponse('ID Asesi dan ID Skema diperlukan', $isAjax);
        }

        try {
            // Prepare master data
            $masterData = [
                'id_feedback' => $id_feedback,
                'id_asesor' => $id_asesor,
                'id_asesi' => $id_asesi,
                'id_skema' => $id_skema,
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai,
                'catatan_lain' => $catatan_lain
            ];

            // Prepare detail data
            $detailData = [
                'komponen' => $komponen,
                'komentar' => $komentar
            ];

            // Process the data using the feedback service
            $result = $this->feedbackService->saveFeedbackData($masterData, $detailData);

            // Handle success response
            return $this->handleSuccessResponse('Data umpan balik berhasil disimpan', $isAjax, $result);
        } catch (\Exception $e) {
            log_message('error', 'Error saving feedback: ' . $e->getMessage());
            return $this->handleErrorResponse('Gagal menyimpan data: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Delete feedback
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Delete detail records first
            $db->table('detail_feedback_asesi')->where('id_feedback', $id)->delete();

            // Then delete master record
            $deleted = $this->feedbackAsesiModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Feedback berhasil dihapus'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Gagal menghapus feedback'
                ], 400);
            }
        } catch (\Exception $e) {
            $db->transRollback();

            return $this->dataService->response([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feedback by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $feedback = $this->feedbackAsesiModel->getById($id);

        if (!$feedback) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $feedback
        ]);
    }

    /**
     * Helper method to handle error responses
     */
    private function handleErrorResponse($message, $isAjax)
    {
        if ($isAjax) {
            return $this->fail($message, 400);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Helper method to handle success responses
     */
    private function handleSuccessResponse($message, $isAjax, $result = null)
    {
        if ($isAjax) {
            return $this->respond([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'token' => csrf_hash() // Return new CSRF token
            ]);
        }

        return redirect()->to('dashboard')
            ->with('success', $message);
    }
}
