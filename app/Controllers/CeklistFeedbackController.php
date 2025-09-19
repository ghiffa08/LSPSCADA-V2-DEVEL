<?php

namespace App\Controllers;

use App\Services\PDFService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use App\Models\FeedbackAsesiModel;
use App\Models\AsesmenModel;
use App\Models\SkemaModel;
use App\Models\AsesorModel;

class CeklistFeedbackController extends ResourceController
{
    use ResponseTrait;

    protected int $id_asesor_user;
    protected FeedbackAsesiModel $feedbackAsesiModel;
    protected AsesmenModel $asesmenModel;
    protected SkemaModel $skemaModel;
    protected PDFService $pdfService;

    public function __construct()
    {
        helper('auth');

        $this->feedbackAsesiModel = new FeedbackAsesiModel();
        $this->asesmenModel = new AsesmenModel();
        $this->skemaModel = new SkemaModel();
        $this->pdfService = new PDFService();
        $this->id_asesor_user = user()->id; // Ini adalah ID dari tabel 'users'
    }

    /**
     * Menampilkan halaman utama untuk daftar feedback asesi.
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Ceklis Feedback Asesi',
            // Data lain yang mungkin diperlukan untuk filter di halaman daftar
        ];

        // Ganti dengan view untuk daftar feedback
        return view('asesor/ceklist_feedback', $data);
    }

    /**
     * Menampilkan halaman form untuk membuat atau mengedit feedback.
     */
    public function create()
    {
        try {
            $asesorId = $this->getCurrentAsesorId();
            if (!$asesorId) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ini.');
            }

            $asesorModel = new AsesorModel();
            $asesorInfo = $asesorModel->getWithSkema($asesorId);

            if (!$asesorInfo || empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan.');
            }

            $id_skema = $asesorInfo['id_skema'];
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema dengan ID ' . $id_skema . ' tidak ditemukan.');
            }

            $asesmen = $this->asesmenModel->where('id_skema', $id_skema)->findAll();

            $data = [
                'siteTitle' => 'Form Feedback Asesi',
                'asesor'    => $asesorInfo,
                'skema'     => $skema,
                'asesmen'   => $asesmen
            ];

            // Ganti dengan view untuk form feedback
            return view('asesor/ceklist_feedback_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'CeklistFeedbackController::create - Exception: ' . $e->getMessage());
            return view('asesor/ceklist_feedback_form', [
                'siteTitle' => 'Form Feedback Asesi',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Menghasilkan PDF untuk data feedback.
     *
     * @param int $id_feedback
     */
    public function pdf(int $id_feedback)
    {
        try {
            log_message('info', 'CeklistFeedbackController: Memulai pembuatan PDF untuk feedback ID: ' . $id_feedback);

            if (!$id_feedback || !filter_var($id_feedback, FILTER_VALIDATE_INT)) {
                throw new \Exception('ID Feedback tidak valid.');
            }

            // Gunakan metode yang sudah kita buat di model
            $result = $this->feedbackAsesiModel->getFeedbackForPDF($id_feedback);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Data feedback tidak dapat diambil.');
            }

            $data = $result['data'];
            // dd($data); // Gunakan untuk debugging jika perlu

            if (empty($data['feedback']) || empty($data['details'])) {
                throw new \Exception('Data feedback atau detailnya tidak lengkap.');
            }

            // Generate PDF dengan data yang didapat
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'CeklistFeedbackController PDF Error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal membuat PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Logika inti untuk menghasilkan dan menampilkan PDF.
     *
     * @param array $data Data untuk view PDF
     * @return void
     */
    private function generatePdf(array $data): void
    {
        // Sesuaikan nama view PDF Anda
        $view = 'pdf/feedback_report';

        // Sesuaikan nama file PDF yang akan di-download
        $filename = 'FR.AK.05. UMPAN BALIK DAN CATATAN ASESMEN';

        $this->pdfService->generatePdf($view, $data, $filename);
    }

    /**
     * Helper untuk mendapatkan ID Asesor dari ID User yang login.
     *
     * @return int|null
     */
    private function getCurrentAsesorId(): ?int
    {
        $asesorModel = new AsesorModel();
        $asesor = $asesorModel->where('id_user', $this->id_asesor_user)->first();
        return $asesor ? (int)$asesor['id_asesor'] : null;
    }
}
