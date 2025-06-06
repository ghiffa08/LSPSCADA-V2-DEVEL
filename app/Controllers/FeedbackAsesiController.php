<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use App\Services\FeedbackService;

class FeedbackAsesiController extends ResourceController
{
    use ResponseTrait;

    protected QRCodeService $qrCodeService;
    protected int $id_user;
    protected $db;
    protected object $asesmenModel;
    protected object $skemaModel;
    protected object $feedbackAsesiModel;
    protected object $komponenFeedbackModel;
    protected PDFService $pdfService;
    protected FeedbackService $feedbackService;

    public function __construct()
    {
        helper('auth');

        // Use service() helper for proper dependency injection
        $this->qrCodeService = service('qrcode');
        $this->pdfService = service('pdf');
        $this->feedbackService = service('feedback');

        // Load models
        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->feedbackAsesiModel = model('FeedbackAsesiModel');
        $this->komponenFeedbackModel = model('KomponenFeedbackModel');

        // Database connection
        $this->db = Database::connect();
        $this->id_user = user()->id ?? 0;
    }

    /**
     * Admin view for managing feedback data
     */
    public function index()
    {
        // Check admin role
        if (!in_groups('admin')) {
            return redirect()->to('dashboard')->with('error', 'Anda tidak memiliki akses');
        }

        $data = [
            'siteTitle' => 'Umpan Balik Asesi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('admin/feedback_asesi', $data);
    }

    /**
     * Asesi view for filling feedback form
     */
    public function asesiIndex()
    {
        // Check asesi role
        if (!in_groups('asesi')) {
            return redirect()->to('dashboard')->with('error', 'Anda tidak memiliki akses');
        }

        // Get current user (asesi) details using the feedback service
        $asesiData = $this->feedbackService->getAsesiDataByUserId($this->id_user);
        if (!$asesiData) {
            return redirect()->to('dashboard')->with('error', 'Data asesi tidak ditemukan');
        }

        // Get active assessment information
        $pengajuanAsesmen = $this->feedbackService->getActiveAssessment($asesiData['id_asesi']);
        if (!$pengajuanAsesmen) {
            return redirect()->to('dashboard')->with('error', 'Tidak ada asesmen aktif yang ditemukan');
        }

        // Check if feedback already exists
        $feedback = $this->feedbackAsesiModel
            ->where('id_asesi', $asesiData['id_asesi'])
            ->where('id_skema', $pengajuanAsesmen['id_skema'])
            ->first();

        $data = [
            'siteTitle' => 'Umpan Balik Asesi',
            'asesi' => $asesiData,
            'pengajuanAsesmen' => $pengajuanAsesmen,
            'feedback' => $feedback,
        ];
        return view('asesi/feedback', $data);
    }
}
