<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class RekamanAsesmenController extends ResourceController
{
    use ResponseTrait;

    // Services
    protected QRCodeService $qrCodeService;
    protected PDFService $pdfService;

    // Models
    protected object $asesmenModel;
    protected object $skemaModel;
    protected object $rekamanAsesmenModel;
    protected object $rekamanAsesmenKompetensiModel;
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
        $this->rekamanAsesmenModel = model('RekamanAsesmenModel');
        $this->rekamanAsesmenKompetensiModel = model('RekamanAsesmenKompetensiModel');
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
     * Display listing of rekaman asesmen - SAMA SEPERTI OBSERVASI
     */
    public function index()
    {
        try {
            // Get asesor info dengan skema (SAMA SEPERTI OBSERVASI)
            $asesorInfo = $this->asesorModel->getWithSkema($this->getCurrentAsesorId());

            if (!$asesorInfo) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ID: ' . $this->id_asesor);
            }

            if (empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan');
            }

            $id_skema = $asesorInfo['id_skema'];

            // Get skema data
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema sertifikasi dengan ID ' . $id_skema . ' tidak ditemukan');
            }

            // Get asesmen data (SAMA SEPERTI OBSERVASI)
            $asesmen = [];
            $method_used = '';

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
                log_message('error', 'RekamanAsesmen::index - Method 1 failed: ' . $e->getMessage());
            }

            if (empty($asesmen)) {
                try {
                    $asesmen = $this->asesmenModel
                        ->where('id_skema', $id_skema)
                        ->findAll();

                    $method_used = 'Simple Query + Manual Join';

                    foreach ($asesmen as &$item) {
                        $item['nama_skema'] = $skema['nama_skema'];
                        $item['kode_skema'] = $skema['kode_skema'];
                    }
                } catch (\Exception $e) {
                    log_message('error', 'RekamanAsesmen::index - Method 2 failed: ' . $e->getMessage());
                }
            }

            // Validate asesmen data structure
            $validAsesmen = [];
            foreach ($asesmen as $a) {
                if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) {
                    if (!isset($a['nama_skema'])) $a['nama_skema'] = $skema['nama_skema'];
                    if (!isset($a['kode_skema'])) $a['kode_skema'] = $skema['kode_skema'];
                    $validAsesmen[] = $a;
                }
            }

            if (empty($validAsesmen)) {
                throw new \Exception('Tidak ada asesmen yang tersedia untuk skema ini');
            }

            // Prepare data for view
            $data = [
                'siteTitle' => 'Rekaman Asesmen Kompetensi',
                'asesor' => $asesorInfo,
                'skema' => [
                    'id_skema' => $id_skema,
                    'nama_skema' => $asesorInfo['nama_skema'] ?? $skema['nama_skema'],
                    'kode_skema' => $asesorInfo['kode_skema'] ?? $skema['kode_skema'],
                    'jenis_skema' => $asesorInfo['jenis_skema'] ?? $skema['jenis_skema'] ?? ''
                ],
                'asesmen' => $validAsesmen,
                'debug_info' => [
                    'user_id' => $this->id_asesor,
                    'asesor_id' => $this->getCurrentAsesorId(),
                    'method_used' => $method_used
                ]
            ];

            return view('asesor/rekaman_kompetensi', $data);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmen::index - Exception: ' . $e->getMessage());
            log_message('error', 'RekamanAsesmen::index - Stack trace: ' . $e->getTraceAsString());

            return view('asesor/rekaman_kompetensi', [
                'siteTitle' => 'Rekaman Asesmen Kompetensi',
                'asesor' => $asesorInfo ?? [],
                'skema' => [],
                'asesmen' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get asesi by asesmen - PERBAIKAN: Tanpa asesor_asesmen
     */
    public function getAsesiByAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak valid'
            ])->setStatusCode(400);
        }

        $id_asesmen = $this->request->getGet('id_asesmen');

        if (!$id_asesmen) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Asesmen diperlukan'
            ]);
        }

        try {
            // PERBAIKAN: Query tanpa asesor_asesmen
            // Validasi bahwa asesmen ini sesuai dengan skema asesor
            $currentAsesorId = $this->getCurrentAsesorId();
            $asesorInfo = $this->asesorModel->getWithSkema($currentAsesorId);

            if (!$asesorInfo || !$asesorInfo['id_skema']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Asesor belum memiliki skema yang ditetapkan'
                ]);
            }

            // Validasi bahwa asesmen ini untuk skema yang sama dengan asesor
            $asesmen = $this->asesmenModel->find($id_asesmen);
            if (!$asesmen || $asesmen['id_skema'] != $asesorInfo['id_skema']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Asesmen tidak sesuai dengan skema asesor'
                ]);
            }

            // Query asesi untuk asesmen ini
            $asesiList = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_asesmen,
                    pa.status_pengajuan,
                    a.nik,
                    u.nama_lengkap as nama_asesi,
                    u.email
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'inner')
                ->join('users u', 'u.id = a.id_user', 'inner')
                ->where('pa.id_asesmen', $id_asesmen)
                ->where('pa.status_pengajuan', 'diterima')
                ->orderBy('u.nama_lengkap', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($asesiList)) {
                return $this->response->setJSON([
                    'success' => true,
                    'asesi' => [],
                    'message' => 'Belum ada asesi terdaftar untuk asesmen ini'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'asesi' => $asesiList,
                'message' => 'Data asesi berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi by asesmen: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat data asesi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Load rekaman data - PERBAIKAN: Tanpa asesor_asesmen
     */
    public function loadRekamanAsesmen()
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

            // PERBAIKAN: Validasi pengajuan tanpa asesor_asesmen
            $pengajuan = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_asesmen,
                    pa.status_pengajuan,
                    u.nama_lengkap as nama_asesi,
                    u.email,
                    asm.id_skema,
                    s.nama_skema,
                    s.kode_skema
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'inner')
                ->join('users u', 'u.id = a.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'inner')
                ->join('skema s', 's.id_skema = asm.id_skema', 'inner')
                ->where('pa.id_pengajuan', $id_pengajuan)
                ->where('pa.id_asesi', $id_asesi)
                ->where('asm.id_asesmen', $id_asesmen)
                ->where('asm.id_skema', $id_skema)
                ->where('pa.status_pengajuan', 'diterima')
                ->get()
                ->getRowArray();

            if (!$pengajuan) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data pengajuan asesmen tidak ditemukan'
                ]);
            }

            // Validasi bahwa asesmen ini sesuai dengan skema asesor
            $currentAsesorId = $this->getCurrentAsesorId();
            $asesorInfo = $this->asesorModel->getWithSkema($currentAsesorId);

            if (!$asesorInfo || $asesorInfo['id_skema'] != $id_skema) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Asesmen tidak sesuai dengan skema asesor'
                ]);
            }

            // Get unit kompetensi for the schema
            $units = $this->unitModel->getUnitsByScheme($id_skema);

            if (empty($units)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada unit kompetensi ditemukan untuk skema ini'
                ]);
            }

            // Get existing rekaman asesmen if any
            $existingRekaman = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $id_pengajuan)
                ->where('deleted_at', null)
                ->first();

            $existingData = [];
            $existingRecommendation = null;

            if ($existingRekaman) {
                // Get kompetensi data
                $kompetensiData = $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman', $existingRekaman['id'])
                    ->findAll();

                foreach ($kompetensiData as $item) {
                    $existingData[$item['id_unit']] = [
                        'observasi' => (int)$item['metode_observasi'],
                        'portofolio' => (int)$item['metode_portofolio'],
                        'pihak_ketiga' => (int)$item['metode_pihak_ketiga'],
                        'lisan' => (int)$item['metode_lisan'],
                        'tertulis' => (int)$item['metode_tertulis'],
                        'proyek' => (int)$item['metode_proyek'],
                        'lainnya' => (int)$item['metode_lainnya']
                    ];
                }

                // Get recommendation data
                $existingRecommendation = [
                    'rekomendasi' => $existingRekaman['rekomendasi'],
                    'tindak_lanjut' => $existingRekaman['tindak_lanjut'],
                    'komentar' => $existingRekaman['komentar']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'rekaman_asesmen' => $units,
                'existing_data' => $existingData,
                'existing_recommendation' => $existingRecommendation,
                'totalUnits' => count($units),
                'pengajuan' => $pengajuan
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading rekaman asesmen: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memuat data rekaman: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate PDF for rekaman asesmen - PERBAIKAN: Handle missing signatures
     */
    public function pdf(int $id = null)
    {
        try {
            log_message('info', 'RekamanAsesmenController: Starting PDF generation for rekaman ID: ' . $id);

            // Validate input
            if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
                throw new \Exception('ID Rekaman tidak valid');
            }

            // PERBAIKAN: Get data dengan method yang diperbaiki
            $result = $this->getRekamanAsesmenForPDF($id);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Data rekaman tidak ditemukan');
            }

            $data = $result['data'];

            // Validasi data yang diperlukan untuk PDF
            if (empty($data['rekaman'])) {
                throw new \Exception('Data rekaman kosong');
            }

            // Debug: Log data structure
            log_message('info', 'RekamanAsesmenController: rekaman data: ' . json_encode($data['rekaman']));
            log_message('info', 'RekamanAsesmenController: kompetensi count: ' . count($data['kompetensi'] ?? []));

            // Ensure helper is loaded
            helper('observasi');

            // PERBAIKAN: Generate QR codes hanya jika ada signature dan tidak kosong
            if (!empty($data['rekaman']['ttd_asesi']) && trim($data['rekaman']['ttd_asesi']) !== '') {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan/tanda-tangan-asesi/' . $data['rekaman']['ttd_asesi']),
                    'logolsp.png'
                );
            } else {
                $data['qr_asesi'] = null; // Tidak ada QR code jika tidak ada tanda tangan
            }

            if (!empty($data['rekaman']['ttd_asesor']) && trim($data['rekaman']['ttd_asesor']) !== '') {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan/tanda-tangan-asesor/' . $data['rekaman']['ttd_asesor']),
                    'logolsp.png'
                );
            } else {
                $data['qr_asesor'] = null; // Tidak ada QR code jika tidak ada tanda tangan
            }

            // Generate PDF dengan data yang sudah diperbaiki
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmenController PDF Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            // Set flash message dan redirect
            session()->setFlashdata('error', 'Gagal generate PDF: ' . $e->getMessage());
            header('Location: ' . previous_url());
            exit();
        }
    }

    /**
     * Get rekaman data for PDF - PERBAIKAN: Handle missing signature fields
     */
    private function getRekamanAsesmenForPDF(int $id_rekaman): array
    {
        try {
            // Get main rekaman data dengan semua relasi
            $rekaman = $this->db->table('rekaman_asesmen ra')
                ->select('
                    ra.*,
                    pa.id_asesi,
                    pa.id_asesmen,
                    a.nik,
                    u.nama_lengkap as nama_asesi,
                    u.email as email_asesi,
                    asm.tujuan,
                    asm.id_skema,
                    asm.id_tuk,
                    s.nama_skema,
                    s.kode_skema,
                    s.jenis_skema,
                    tuk.nama_tuk,
                    tuk.jenis_tuk
                ')
                ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = ra.id_pengajuan', 'inner')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'inner')
                ->join('users u', 'u.id = a.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'inner')
                ->join('skema s', 's.id_skema = asm.id_skema', 'inner')
                ->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'inner')
                ->where('ra.id', $id_rekaman)
                ->where('ra.deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$rekaman) {
                return [
                    'success' => false,
                    'message' => 'Data rekaman tidak ditemukan'
                ];
            }

            // PERBAIKAN: Get asesor info berdasarkan skema
            $asesorInfo = $this->db->table('asesor')
                ->select('
                    asesor.nomor_registrasi,
                    asesor_user.nama_lengkap as nama_asesor,
                    asesor_user.email as email_asesor
                ')
                ->join('users asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->where('asesor.id_skema', $rekaman['id_skema'])
                ->get()
                ->getRowArray();

            if (!$asesorInfo) {
                return [
                    'success' => false,
                    'message' => 'Data asesor tidak ditemukan untuk skema ini'
                ];
            }

            // Merge asesor info ke rekaman
            $rekaman = array_merge($rekaman, $asesorInfo);

            // PERBAIKAN: Handle missing signature fields - set default values
            $rekaman['ttd_asesi'] = $rekaman['ttd_asesi'] ?? '';
            $rekaman['ttd_asesor'] = $rekaman['ttd_asesor'] ?? '';
            $rekaman['tanggal_asesmen'] = $rekaman['tanggal_asesmen'] ?? $rekaman['created_at'] ?? date('Y-m-d');

            // Get kompetensi details
            $kompetensi = $this->db->table('rekaman_asesmen_kompetensi rak')
                ->select('
                    rak.*,
                    u.kode_unit,
                    u.nama_unit
                ')
                ->join('unit u', 'u.id_unit = rak.id_unit', 'inner')
                ->where('rak.id_rekaman', $id_rekaman)
                ->orderBy('u.kode_unit', 'ASC')
                ->get()
                ->getResultArray();

            // Format data untuk PDF (sama seperti observasi)
            $pdfData = [
                'success' => true,
                'data' => [
                    'rekaman' => $rekaman,
                    'kompetensi' => $kompetensi,
                    // Format sama seperti observasi untuk compatibility
                    'observasi' => [
                        'nama_skema' => $rekaman['nama_skema'],
                        'kode_skema' => $rekaman['kode_skema'],
                        'nama_tuk' => $rekaman['nama_tuk'],
                        'jenis_tuk' => $rekaman['jenis_tuk'],
                        'nama_asesor' => $rekaman['nama_asesor'],
                        'nama_asesi' => $rekaman['nama_asesi'],
                        'tanggal_asesmen' => $rekaman['tanggal_asesmen'],
                        'ttd_asesi' => $rekaman['ttd_asesi'], // Sudah di-handle di atas
                        'ttd_asesor' => $rekaman['ttd_asesor'] // Sudah di-handle di atas
                    ],
                    'skema' => [
                        'nama_skema' => $rekaman['nama_skema'],
                        'kode_skema' => $rekaman['kode_skema'],
                        'jenis_skema' => $rekaman['jenis_skema']
                    ]
                ]
            ];

            return $pdfData;
        } catch (\Exception $e) {
            log_message('error', 'Error getting rekaman for PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memuat data rekaman: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate PDF output - SAMA SEPERTI OBSERVASI
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/rekaman_page1',
            'pdf/rekaman_page2'
        ];

        $filename = 'FR.AK.02. REKAMAN ASESMEN KOMPETENSI';
        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Helper method untuk mendapatkan current asesor ID
     */
    private function getCurrentAsesorId(): ?int
    {
        $asesorModel = model('AsesorModel');
        $asesor = $asesorModel->getByUserId($this->id_asesor);
        return $asesor ? $asesor['id_asesor'] : null;
    }

    /**
     * Store rekaman asesmen data - AUTO SAVE SYSTEM - PERBAIKAN LENGKAP
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

        // Get request data - support both POST and JSON
        $rawInput = $this->request->getBody();
        $jsonData = json_decode($rawInput, true);
        $postData = $this->request->getPost();

        // Determine request type
        $requestType = $postData['save_type'] ?? ($jsonData['save_type'] ?? 'full');

        // Log incoming request for debugging
        log_message('info', 'RekamanAsesmen store() called with type: ' . $requestType);
        log_message('info', 'POST data: ' . json_encode($postData));
        log_message('info', 'JSON data: ' . json_encode($jsonData));

        try {
            switch ($requestType) {
                case 'settings':
                    return $this->saveSettings($postData, $jsonData);

                case 'method':
                    return $this->saveSingleMethod($postData, $jsonData);

                case 'batch':
                    return $this->saveBatchMethods($postData, $jsonData);

                case 'recommendation':
                    return $this->saveRecommendation($postData, $jsonData);

                case 'full':
                default:
                    return $this->saveFullRekaman($postData, $jsonData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in rekaman save: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save rekaman settings (master data only)
     */
    private function saveSettings($postData, $jsonData)
    {
        $data = array_merge($postData, $jsonData ?? []);

        // Validate required fields
        if (empty($data['id_pengajuan'])) {
            return $this->fail('ID Pengajuan diperlukan');
        }

        try {
            $this->db->transBegin();

            // Check if rekaman exists
            $existing = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $data['id_pengajuan'])
                ->where('deleted_at', null)
                ->first();

            $rekamanData = [
                'id_pengajuan' => $data['id_pengajuan'],
                'tanggal_asesmen' => $data['tanggal_asesmen'] ?? date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                $this->rekamanAsesmenModel->update($existing['id'], $rekamanData);
                $id_rekaman = $existing['id'];
            } else {
                $rekamanData['created_at'] = date('Y-m-d H:i:s');
                $rekamanData['status'] = 'draft';
                $id_rekaman = $this->rekamanAsesmenModel->insert($rekamanData);
            }

            $this->db->transCommit();

            return $this->respond([
                'success' => true,
                'message' => 'Rekaman asesmen berhasil disimpan',
                'data' => [
                    'id_rekaman' => $id_rekaman
                ],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmen saveSettings Error: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan rekaman: ' . $e->getMessage());
        }
    }

    /**
     * Save single method change
     */
    private function saveSingleMethod($postData, $jsonData)
    {
        $data = array_merge($postData, $jsonData ?? []);

        // Validate required fields
        $required = ['id_pengajuan', 'id_unit', 'method', 'value'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        try {
            $this->db->transBegin();

            // Get or create rekaman
            $rekaman = $this->getOrCreateRekaman($data['id_pengajuan']);
            if (!$rekaman) {
                return $this->fail('Gagal membuat rekaman asesmen');
            }

            // Update single method
            $this->updateSingleMethod($rekaman['id'], $data['id_unit'], $data['method'], $data['value']);

            $this->db->transCommit();

            return $this->respond([
                'success' => true,
                'message' => 'Metode berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmen saveSingleMethod Error: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan metode: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get or create rekaman
     */
    private function getOrCreateRekaman($id_pengajuan)
    {
        $existing = $this->rekamanAsesmenModel
            ->where('id_pengajuan', $id_pengajuan)
            ->where('deleted_at', null)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Create new rekaman
        $rekamanData = [
            'id_pengajuan' => $id_pengajuan,
            'tanggal_asesmen' => date('Y-m-d'),
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $id_rekaman = $this->rekamanAsesmenModel->insert($rekamanData);

        return $this->rekamanAsesmenModel->find($id_rekaman);
    }

    /**
     * Helper: Update single method
     */
    private function updateSingleMethod($id_rekaman, $id_unit, $method, $value)
    {
        // Get existing kompetensi record
        $existing = $this->rekamanAsesmenKompetensiModel
            ->where('id_rekaman', $id_rekaman)
            ->where('id_unit', $id_unit)
            ->first();

        $methodData = [
            'metode_observasi' => 0,
            'metode_portofolio' => 0,
            'metode_pihak_ketiga' => 0,
            'metode_lisan' => 0,
            'metode_tertulis' => 0,
            'metode_proyek' => 0,
            'metode_lainnya' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            // Update existing - preserve other methods
            $methodData = [
                'metode_observasi' => $existing['metode_observasi'],
                'metode_portofolio' => $existing['metode_portofolio'],
                'metode_pihak_ketiga' => $existing['metode_pihak_ketiga'],
                'metode_lisan' => $existing['metode_lisan'],
                'metode_tertulis' => $existing['metode_tertulis'],
                'metode_proyek' => $existing['metode_proyek'],
                'metode_lainnya' => $existing['metode_lainnya'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update specific method
            $methodData['metode_' . $method] = $value ? 1 : 0;

            $this->rekamanAsesmenKompetensiModel->update($existing['id'], $methodData);
        } else {
            // Create new record
            $methodData['id_rekaman'] = $id_rekaman;
            $methodData['id_unit'] = $id_unit;
            $methodData['metode_' . $method] = $value ? 1 : 0;
            $methodData['created_at'] = date('Y-m-d H:i:s');

            $this->rekamanAsesmenKompetensiModel->insert($methodData);
        }
    }

    /**
     * Save batch methods
     */
    private function saveBatchMethods($postData, $jsonData)
    {
        $data = $jsonData ?? $postData;

        if (empty($data['id_pengajuan']) || empty($data['items'])) {
            return $this->fail('ID Pengajuan dan items diperlukan');
        }

        try {
            $this->db->transBegin();

            // Get or create rekaman
            $rekaman = $this->getOrCreateRekaman($data['id_pengajuan']);
            if (!$rekaman) {
                return $this->fail('Gagal membuat rekaman asesmen');
            }

            // Process batch items
            foreach ($data['items'] as $id_unit => $methods) {
                $this->upsertUnitMethods($rekaman['id'], $id_unit, $methods);
            }

            $this->db->transCommit();

            return $this->respond([
                'success' => true,
                'message' => 'Metode batch berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmen saveBatchMethods Error: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan metode batch: ' . $e->getMessage());
        }
    }

    /**
     * Save recommendation data
     */
    private function saveRecommendation($postData, $jsonData)
    {
        $data = array_merge($postData, $jsonData ?? []);

        if (empty($data['id_pengajuan'])) {
            return $this->fail('ID Pengajuan diperlukan');
        }

        try {
            $this->db->transBegin();

            // Get or create rekaman
            $rekaman = $this->getOrCreateRekaman($data['id_pengajuan']);
            if (!$rekaman) {
                return $this->fail('Gagal membuat rekaman asesmen');
            }

            // Update recommendation
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($data['rekomendasi'])) {
                $updateData['rekomendasi'] = $data['rekomendasi'];
            }
            if (isset($data['komentar'])) {
                $updateData['komentar'] = $data['komentar'];
            }
            if (isset($data['tindak_lanjut'])) {
                $updateData['tindak_lanjut'] = $data['tindak_lanjut'];
            }

            $this->rekamanAsesmenModel->update($rekaman['id'], $updateData);

            $this->db->transCommit();

            return $this->respond([
                'success' => true,
                'message' => 'Rekomendasi berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmen saveRecommendation Error: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan rekomendasi: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Upsert unit methods
     */
    private function upsertUnitMethods($id_rekaman, $id_unit, $methods)
    {
        // Get existing kompetensi record
        $existing = $this->rekamanAsesmenKompetensiModel
            ->where('id_rekaman', $id_rekaman)
            ->where('id_unit', $id_unit)
            ->first();

        $methodData = [
            'metode_observasi' => isset($methods['observasi']) ? 1 : 0,
            'metode_portofolio' => isset($methods['portofolio']) ? 1 : 0,
            'metode_pihak_ketiga' => isset($methods['pihak_ketiga']) ? 1 : 0,
            'metode_lisan' => isset($methods['lisan']) ? 1 : 0,
            'metode_tertulis' => isset($methods['tertulis']) ? 1 : 0,
            'metode_proyek' => isset($methods['proyek']) ? 1 : 0,
            'metode_lainnya' => isset($methods['lainnya']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->rekamanAsesmenKompetensiModel->update($existing['id'], $methodData);
        } else {
            $methodData['id_rekaman'] = $id_rekaman;
            $methodData['id_unit'] = $id_unit;
            $methodData['created_at'] = date('Y-m-d H:i:s');

            $this->rekamanAsesmenKompetensiModel->insert($methodData);
        }
    }
}
