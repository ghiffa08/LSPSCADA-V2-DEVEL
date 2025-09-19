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
     * Menampilkan halaman pengisian feedback untuk asesi yang sedang login.
     * Mengoptimalkan pengambilan data untuk menghindari N+1 query.
     */
    public function asesiIndex()
    {
        // if (!in_groups('asesi')) {
        //     return redirect()->to('dashboard')->with('error', 'Anda tidak memiliki akses.');
        // }

        // Pastikan helper 'auth' sudah di-load
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
            // PERBAIKAN 1: Tambahkan JOIN ke tabel users dengan alias 'asesi_user' untuk mendapatkan nama asesi
            ->join('users as asesi_user', 'asesi_user.id = asesi.id_user')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->join('asesor', 'asesor.id_asesor = pengajuan_asesmen.id_asesor', 'left')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'left')
            ->where('asesi.id_user', $id_user)
            ->whereIn('pengajuan_asesmen.status_pengajuan', ['diterima', 'selesai'])
            ->orderBy('pengajuan_asesmen.created_at', 'DESC')
            ->first();

        // if (!$pengajuan) {
        //     return redirect()->to('dashboard')->with('error', 'Tidak ada jadwal asesmen aktif yang ditemukan untuk Anda.');
        // }

        $feedbackModel = new \App\Models\FeedbackAsesiModel();

        $feedback = $feedbackModel->where('id_pengajuan', $pengajuan['id_pengajuan'])->first();

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

        // Hapus atau beri komentar pada dd() setelah selesai debugging
        // dd($data); 

        // Ganti nama view jika berbeda
        return view('asesi/feedback_asesi', $data);
    }
}
