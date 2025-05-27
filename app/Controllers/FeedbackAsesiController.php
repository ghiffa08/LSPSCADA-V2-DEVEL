<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

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


    public function __construct()
    {
        helper('auth');

        $this->qrCodeService = new QRCodeService();
        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->feedbackAsesiModel = model('FeedbackAsesiModel');
        $this->komponenFeedbackModel = model('KomponenFeedbackModel');
        $this->pdfService = new PDFService();
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
//        if (!in_groups('asesi')) {
//            return redirect()->to('dashboard')->with('error', 'Anda tidak memiliki akses');
//        }

        // Get current user (asesi)
        $userId = $this->id_user;

        // Get asesi ID from user ID
        $asesi = $this->db->table('asesi')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$asesi) {
            return redirect()->to('dashboard')->with('error', 'Data asesi tidak ditemukan');
        }

        $id_asesi = $asesi['id_asesi'];

        // Get approved assessment for this asesi
        $pengajuanAsesmen = $this->db->table('pengajuan_asesmen')
            ->select('
                pengajuan_asesmen.id_asesmen,
                pengajuan_asesmen.id_asesi,
                asesmen.id_skema,
          
                skema.kode_skema,
                skema.nama_skema,
    
            ')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
//            ->join('users', 'users.id = asesmen.id_asesor', 'left')
            ->where('pengajuan_asesmen.id_asesi', $id_asesi)
//            ->where('pengajuan_asesmen.status', 'approved')
            ->get()
            ->getRowArray();

        if (!$pengajuanAsesmen) {
            return redirect()->to('dashboard')->with('error', 'Tidak ada asesmen aktif yang ditemukan');
        }

        // Check if feedback already exists
        $feedback = $this->feedbackAsesiModel
            ->where('id_asesi', $id_asesi)
            ->where('id_skema', $pengajuanAsesmen['id_skema'])
            ->first();

        $data = [
            'siteTitle' => 'Umpan Balik Asesi',
            'asesmen' => $pengajuanAsesmen,
            'id_asesi' => $id_asesi,
            'feedback' => $feedback
        ];

        return view('asesi/feedback_asesi', $data);
    }

    /**
     * Generate PDF for feedback data
     *
     * @param int $id_feedback Feedback ID
     * @return void
     */
    public function pdf(int $id_feedback): void
    {
        try {
            // Prepare data for PDF
            $data = $this->getFeedbackData($id_feedback);

            // Generate QR codes
            if (!empty($data['feedback']['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesi/' . $data['feedback']['ttd_asesi']),
                    'logolsp.png'
                );
            }

            if (!empty($data['feedback']['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesor/' . $data['feedback']['ttd_asesor']),
                    'logolsp.png'
                );
            }

            // Generate PDF with the prepared data
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Generate and output PDF
     *
     * @param array $data Data for PDF views
     * @return void
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/feedback_asesi'
        ];

        $filename = 'FR.IA.06. UMPAN BALIK DARI ASESI';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Common method to get all required feedback data
     * Reduces code duplication between AJAX and PDF methods
     *
     * @param int $id_feedback Feedback ID
     * @return array All data needed for both AJAX response and PDF generation
     */
    private function getFeedbackData(int $id_feedback): array
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
}
