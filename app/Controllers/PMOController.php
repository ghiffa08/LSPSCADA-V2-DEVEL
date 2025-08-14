<?php
// app/Controllers/PMOController.php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class PMOController extends ResourceController
{
    use ResponseTrait;

    // Services
    protected QRCodeService $qrCodeService;
    protected PDFService $pdfService;

    // Models
    protected object $asesmenModel;
    protected object $skemaModel;
    protected object $pmoModel;
    protected object $pmoPertanyaanModel;
    protected object $pmoJawabanModel;
    protected object $pengajuanAsesmenModel;
    protected object $asesorModel;
    protected object $unitModel;

    // Context
    protected int $id_asesor;
    protected $db;

    public function __construct()
    {
        helper(['auth', 'form']);
        $this->initializeServices();
        $this->initializeModels();
        $this->initializeUserContext();
        $this->initializeDatabase();
    }

    private function initializeServices(): void
    {
        $this->qrCodeService = new QRCodeService();
        $this->pdfService = new PDFService();
    }

    private function initializeModels(): void
    {
        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->pmoModel = model('PMOModel');
        $this->pmoPertanyaanModel = model('PMOPertanyaanModel');
        $this->pmoJawabanModel = model('PMOJawabanModel');
        $this->pengajuanAsesmenModel = model('PengajuanAsesmenModel');
        $this->asesorModel = model('AsesorModel');
        $this->unitModel = model('UnitModel');
    }

    private function initializeUserContext(): void
    {
        $this->id_asesor = user()->id ?? 0;

        if ($this->id_asesor === 0) {
            throw new \RuntimeException('User tidak terautentikasi');
        }

        if (!in_groups(['Asesor', 'Admin'])) {
            throw new \RuntimeException('Akses ditolak: Anda tidak memiliki izin sebagai asesor');
        }
    }

    private function initializeDatabase(): void
    {
        $this->db = Database::connect();
    }

    /**
     * Display listing PMO - SAMA PERSIS SEPERTI OBSERVASI
     */
    public function index()
    {
        try {
            // Get current user ID
            $userId = user()->id ?? 0;

            // Get asesor info with their skema (one-to-one) - SAMA SEPERTI OBSERVASI
            $asesorModel = model('AsesorModel');
            $asesorInfo = $asesorModel->getWithSkema($this->getCurrentAsesorId());

            if (!$asesorInfo) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ID: ' . $userId);
            }

            // Check if asesor has assigned skema - SAMA SEPERTI OBSERVASI
            if (empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan');
            }

            $id_skema = $asesorInfo['id_skema'];

            // Get skema data - SAMA SEPERTI OBSERVASI
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema sertifikasi dengan ID ' . $id_skema . ' tidak ditemukan dalam database');
            }

            // Multiple fallback approach for getting asesmen data - SAMA PERSIS SEPERTI OBSERVASI
            $asesmen = [];
            $method_used = '';

            // Method 1: Try with JOIN - SAMA SEPERTI OBSERVASI
            try {
                $db = \Config\Database::connect();
                $asesmen = $db->table('asesmen')
                    ->select('asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, skema.nama_skema, skema.kode_skema')
                    ->join('skema', 'asesmen.id_skema = skema.id_skema', 'left')
                    ->where('asesmen.id_skema', $id_skema)
                    ->get()
                    ->getResultArray();

                $method_used = 'JOIN Query';
            } catch (\Exception $e) {
                log_message('error', 'PMO::index - Method 1 failed: ' . $e->getMessage());
            }

            // Method 2: Fallback simple query - SAMA SEPERTI OBSERVASI
            if (empty($asesmen)) {
                try {
                    $asesmen = $this->asesmenModel
                        ->where('id_skema', $id_skema)
                        ->findAll();

                    $method_used = 'Simple Query + Manual Join';

                    // Manually add skema info
                    foreach ($asesmen as &$item) {
                        $item['nama_skema'] = $skema['nama_skema'];
                        $item['kode_skema'] = $skema['kode_skema'];
                    }
                } catch (\Exception $e) {
                    log_message('error', 'PMO::index - Method 2 failed: ' . $e->getMessage());
                }
            }

            // Method 3: Check total asesmen in database - SAMA SEPERTI OBSERVASI
            if (empty($asesmen)) {
                $totalAsesmen = $this->asesmenModel->countAll();
                $method_used = 'No Data Found';

                if ($totalAsesmen == 0) {
                    log_message('error', 'PMO::index - Asesmen table is completely empty');
                }
            }

            // Validate asesmen data structure - SAMA SEPERTI OBSERVASI
            $validAsesmen = [];
            foreach ($asesmen as $a) {
                if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) {
                    // Ensure required fields
                    if (!isset($a['nama_skema'])) $a['nama_skema'] = $skema['nama_skema'];
                    if (!isset($a['kode_skema'])) $a['kode_skema'] = $skema['kode_skema'];
                    $validAsesmen[] = $a;
                }
            }

            // Prepare data for view - SAMA SEPERTI OBSERVASI
            $data = [
                'siteTitle' => 'Pertanyaan Mendukung Observasi',
                'asesor' => $asesorInfo,
                'skema' => [
                    'id_skema' => $id_skema,
                    'nama_skema' => $asesorInfo['nama_skema'] ?? $skema['nama_skema'],
                    'kode_skema' => $asesorInfo['kode_skema'] ?? $skema['kode_skema'],
                    'jenis_skema' => $asesorInfo['jenis_skema'] ?? $skema['jenis_skema'] ?? ''
                ],
                'asesmen' => $validAsesmen
            ];

            return view('asesor/pmo_form', $data);
        } catch (\Exception $e) {
            log_message('error', 'PMO::index - Exception: ' . $e->getMessage());
            log_message('error', 'PMO::index - Stack trace: ' . $e->getTraceAsString());

            // Return view with error info for debugging
            return view('asesor/pmo_form', [
                'title' => 'Pertanyaan Mendukung Observasi',
                'asesor' => $asesorInfo ?? [],
                'skema' => [],
                'asesmen' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get asesi by asesmen - SAMA PERSIS SEPERTI OBSERVASI
     */
    public function getAsesiByAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Not found');
        }

        $id_asesmen = $this->request->getGet('id_asesmen');

        if (!$id_asesmen) {
            return $this->fail('ID Asesmen required');
        }

        try {
            // Get asesi data for this asesmen - SAMA SEPERTI OBSERVASI
            $pengajuanModel = model('PengajuanAsesmenModel');
            $asesiData = $pengajuanModel->getAsesiByAsesmen($id_asesmen);

            $count = count($asesiData);

            // Provide informative messages based on data availability - SAMA SEPERTI OBSERVASI
            $message = '';
            if ($count === 0) {
                $message = 'Belum ada asesi yang terdaftar untuk asesmen ini. Pastikan asesi sudah mengajukan permohonan dan statusnya telah disetujui.';
            } else {
                $message = "Ditemukan {$count} asesi yang terdaftar untuk asesmen ini.";
            }

            return $this->respond([
                'success' => true,
                'asesi' => $asesiData,
                'count' => $count,
                'message' => $message,
                'isEmpty' => $count === 0
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi by asesmen ID ' . $id_asesmen . ': ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage(),
                'asesi' => [],
                'count' => 0,
                'isEmpty' => true
            ], 500);
        }
    }

    /**
     * Load PMO data
     */
    public function loadPMOData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak valid'
            ])->setStatusCode(400);
        }

        try {
            $id_skema = $this->request->getGet('id_skema');
            $id_asesmen = $this->request->getGet('id_asesmen');
            $id_asesi = $this->request->getGet('id_asesi');
            $id_pengajuan = $this->request->getGet('id_pengajuan');

            // Validation
            if (!$id_skema || !$id_asesmen || !$id_asesi || !$id_pengajuan) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap'
                ]);
            }

            // Check if PMO already exists
            $existingPMO = $this->pmoModel->getByPengajuan($id_pengajuan);

            $id_pmo = null;
            $pertanyaan = [];
            $jawaban_existing = [];

            if ($existingPMO) {
                $id_pmo = $existingPMO['id_pmo'];
                // Get existing pertanyaan and jawaban
                $pertanyaan = $this->pmoPertanyaanModel->getPertanyaanByPMO($id_pmo);
                $jawaban_existing = $this->pmoJawabanModel->getJawabanByPMO($id_pmo);
            } else {
                // Get unit struktur untuk create new pertanyaan
                $units = $this->unitModel->getUnitsByScheme($id_skema);

                // Transform units data untuk frontend
                foreach ($units as $unit) {
                    $pertanyaan[] = [
                        'id_unit' => $unit['id_unit'],
                        'kode_unit' => $unit['kode_unit'],
                        'nama_unit' => $unit['nama_unit'],
                        'kategori' => 'pengetahuan',
                        'tipe_pertanyaan' => 'ya_tidak',
                        'pertanyaan' => "Apakah asesi mampu mendemonstrasikan unit kompetensi: {$unit['nama_unit']}?",
                        'is_default' => true
                    ];
                }
            }

            // Format jawaban existing sebagai lookup
            $jawaban_lookup = [];
            foreach ($jawaban_existing as $jaw) {
                $jawaban_lookup[$jaw['id_pertanyaan']] = $jaw;
            }

            return $this->response->setJSON([
                'success' => true,
                'id_pmo' => $id_pmo,
                'pertanyaan' => $pertanyaan,
                'jawaban_existing' => $jawaban_lookup,
                'message' => $existingPMO ? 'Data PMO berhasil dimuat' : 'Template pertanyaan berhasil digenerate'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading PMO data: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat data PMO: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store PMO data dengan auto-save system
     */
    public function store()
    {
        // Security check
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak valid'
            ])->setStatusCode(400);
        }

        // Get request data
        $rawInput = $this->request->getBody();
        $jsonData = json_decode($rawInput, true);
        $postData = $this->request->getPost();

        // Determine request type
        $requestType = $postData['save_type'] ?? ($jsonData['save_type'] ?? 'full');

        try {
            switch ($requestType) {
                case 'settings':
                    return $this->saveSettings($postData, $jsonData);

                case 'pertanyaan':
                    return $this->savePertanyaan($postData, $jsonData);

                case 'jawaban':
                    return $this->saveJawaban($postData, $jsonData);

                case 'batch_jawaban':
                    return $this->saveBatchJawaban($postData, $jsonData);

                case 'full':
                default:
                    return $this->saveFullPMO($postData, $jsonData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in PMO save: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save PMO settings
     */
    private function saveSettings($postData, $jsonData)
    {
        $data = array_merge($postData, $jsonData ?? []);

        if (empty($data['id_pengajuan'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Pengajuan diperlukan'
            ]);
        }

        try {
            $this->db->transBegin();

            // Get current asesor ID
            $currentAsesorId = $this->getCurrentAsesorId();

            // Check if PMO exists
            $existing = $this->pmoModel->getByPengajuan($data['id_pengajuan']);

            $pmoData = [
                'id_pengajuan' => $data['id_pengajuan'],
                'id_asesor' => $currentAsesorId,
                'id_asesi' => $data['id_asesi'] ?? '',
                'tanggal_pmo' => $data['tanggal_pmo'] ?? date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $this->pmoModel->update($existing['id_pmo'], $pmoData);
                $id_pmo = $existing['id_pmo'];
            } else {
                $pmoData['created_at'] = date('Y-m-d H:i:s');
                $pmoData['status'] = 'draft';
                $id_pmo = $this->pmoModel->insert($pmoData);

                // Generate default pertanyaan if new PMO
                if (isset($data['id_skema'])) {
                    $this->pmoPertanyaanModel->generateDefaultPertanyaan($id_pmo, $data['id_skema']);
                }
            }

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PMO berhasil disimpan',
                'data' => [
                    'id_pmo' => $id_pmo
                ],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'PMO saveSettings Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan PMO: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save single jawaban
     */
    private function saveJawaban($postData, $jsonData)
    {
        $data = array_merge($postData, $jsonData ?? []);

        $required = ['id_pmo', 'id_pertanyaan', 'jawaban_nilai'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Field {$field} diperlukan"
                ]);
            }
        }

        try {
            $this->db->transBegin();

            // Save jawaban
            $result = $this->pmoJawabanModel->saveJawaban($data);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Update PMO progress
            $this->pmoModel->updateProgress($data['id_pmo']);

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'PMO saveJawaban Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save batch jawaban
     */
    private function saveBatchJawaban($postData, $jsonData)
    {
        $data = $jsonData ?? $postData;

        if (empty($data['id_pmo']) || empty($data['jawaban'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID PMO dan jawaban diperlukan'
            ]);
        }

        try {
            $this->db->transBegin();

            // Save batch jawaban
            $result = $this->pmoJawabanModel->batchSaveJawaban($data['id_pmo'], $data['jawaban']);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Update PMO progress
            $this->pmoModel->updateProgress($data['id_pmo']);

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => $result['message'],
                'saved_count' => $result['saved_count'],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'PMO saveBatchJawaban Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban batch: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate PDF untuk PMO
     */
    public function pdf(int $id = null)
    {
        try {
            log_message('info', 'PMOController: Starting PDF generation for PMO ID: ' . $id);

            if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
                throw new \Exception('ID PMO tidak valid');
            }

            // Get PMO data
            $pmoData = $this->pmoModel->getPMOWithDetails($id);

            if (!$pmoData) {
                throw new \Exception('Data PMO tidak ditemukan');
            }

            // Format data untuk PDF
            $data = [
                'pmo' => $pmoData,
                'observasi' => [
                    'nama_skema' => $pmoData['nama_skema'],
                    'kode_skema' => $pmoData['kode_skema'],
                    'nama_tuk' => $pmoData['nama_tuk'],
                    'jenis_tuk' => $pmoData['jenis_tuk'],
                    'nama_asesor' => $pmoData['nama_asesor'],
                    'nama_asesi' => $pmoData['nama_asesi'],
                    'tanggal_observasi' => $pmoData['tanggal_pmo']
                ],
                'jenisSertifikasiFormatted' => $pmoData['jenis_skema'] ?? 'KKNI',
                'pertanyaan' => $pmoData['pertanyaan'] ?? []
            ];

            // Generate QR codes if signatures exist
            if (!empty($pmoData['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan/tanda-tangan-asesi/' . $pmoData['ttd_asesi']),
                    'logolsp.png'
                );
            }

            if (!empty($pmoData['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan/tanda-tangan-asesor/' . $pmoData['ttd_asesor']),
                    'logolsp.png'
                );
            }

            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'PMOController PDF Error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal generate PDF: ' . $e->getMessage());
            header('Location: ' . previous_url());
            exit();
        }
    }

    /**
     * Generate PDF output
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/pmo_page1',
            'pdf/pmo_page2'
        ];

        $filename = 'FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI';
        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Helper untuk mendapatkan current asesor ID
     */
    private function getCurrentAsesorId(): ?int
    {
        $asesorModel = model('AsesorModel');
        $asesor = $asesorModel->getByUserId($this->id_asesor);
        return $asesor ? $asesor['id_asesor'] : null;
    }
}
