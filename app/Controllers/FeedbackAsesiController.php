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
     * [FUNGSI BARU] Menampilkan halaman daftar feedback untuk Asesi.
     */
    public function listAsesi()
    {
        $data = [
            'siteTitle' => 'Daftar Umpan Balik Asesmen',
        ];
        // Hanya menampilkan view, data akan di-load via AJAX
        return view('asesi/list-feedback-asesi', $data);
    }

    /**
     * [FUNGSI BARU] Endpoint AJAX untuk filter daftar feedback.
     */
    public function filterFeedback()
    {
        if ($this->request->isAJAX()) {
            $filter = $this->request->getGet('filter') ?? 'terbaru';
            $userId = user()->id;

            // Panggil method baru di model untuk mendapatkan data
            $data = $this->feedbackAsesiModel->getListByUserId($userId, $filter);

            return $this->response->setJSON($data);
        }
        return $this->response->setStatusCode(403, 'Forbidden Access');
    }

    /**
     * Menampilkan halaman pengisian feedback untuk asesi yang sedang login.
     * Mengoptimalkan pengambilan data untuk menghindari N+1 query.
     */
    public function asesiIndex($id_pengajuan = null)
    {
        helper('auth');
        $id_user = user()->id;

        $pengajuanModel = new \App\Models\PengajuanAsesmenModel();
        $pengajuan = $pengajuanModel
            ->select('
                pengajuan_asesmen.id_pengajuan, 
                pengajuan_asesmen.id_asesi, 
                pengajuan_asesmen.id_asesor,
                skema.id_skema,
                skema.nama_skema, 
                skema.kode_skema,
                asesor_user.nama_lengkap as nama_asesor,
                asesi_user.nama_lengkap as nama_asesi 
            ')
            ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
            ->join('users as asesi_user', 'asesi_user.id = asesi.id_user') // Join untuk nama asesi
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            // PENYESUAIAN: Join langsung ke tabel users untuk mendapatkan nama asesor
            ->join('users as asesor_user', 'asesor_user.id = pengajuan_asesmen.id_asesor', 'left')
            ->where('asesi.id_user', $id_user)
            ->whereIn('pengajuan_asesmen.status_pengajuan', ['diterima', 'selesai'])
            ->orderBy('pengajuan_asesmen.created_at', 'DESC')
            ->first();

        // if (!$pengajuan) { ... }

        $feedbackModel = new \App\Models\FeedbackAsesiModel();
        $feedback = $pengajuan ? $feedbackModel->where('id_pengajuan', $pengajuan['id_pengajuan'])->first() : null;

        $existingAnswers = [];
        if ($feedback) {
            $existingAnswers = $feedbackModel->getExistingFeedback($feedback['id_feedback']);
        }

        $komponen = $feedbackModel->getKomponenFeedback();

        $data = [
            'siteTitle'       => 'Umpan Balik Asesi',
            'pengajuan'       => $pengajuan,
            'feedback'        => $feedback,
            'komponen'        => $komponen,
            'existingAnswers' => $existingAnswers
        ];

        return view('asesi/feedback_asesi', $data);
    }
}
