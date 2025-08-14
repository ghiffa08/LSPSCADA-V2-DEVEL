<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

/**
 * RekamanAsesmenController - Enhanced Version with Auto-Save
 * 
 * Controller untuk mengelola rekaman asesmen kompetensi
 * dengan auto-save functionality seperti observasi
 */
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
    protected int $id_asesor; // User ID dari session
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

        // Validate user has asesor role
        if (!in_groups(['Asesor', 'Admin'])) {
            throw new \RuntimeException('Akses ditolak: Anda tidak memiliki izin sebagai asesor');
        }
    }

    private function initializeDatabase(): void
    {
        $this->db = Database::connect();
    }

    /**
     * Display listing of rekaman asesmen
     */
    public function index()
    {
        try {
            // Get current user ID
            $userId = user()->id ?? 0;

            // Get asesor info with their skema
            $asesorInfo = $this->asesorModel->getWithSkema($this->getCurrentAsesorId());

            if (!$asesorInfo) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ID: ' . $userId);
            }

            // Check if asesor has assigned skema
            if (empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan');
            }

            $id_skema = $asesorInfo['id_skema'];

            // Get skema data
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema sertifikasi dengan ID ' . $id_skema . ' tidak ditemukan');
            }

            // Get asesmen data with fallback approach
            $asesmen = [];
            $method_used = '';

            // Method 1: Try with JOIN
            try {
                $asesmen = $this->db->table('asesmen')
                    ->select('asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, skema.nama_skema, skema.kode_skema')
                    ->join('skema', 'asesmen.id_skema = skema.id_skema', 'left')
                    ->where('asesmen.id_skema', $id_skema)
                    ->get()
                    ->getResultArray();

                $method_used = 'JOIN Query';
            } catch (\Exception $e) {
                log_message('error', 'RekamanAsesmen::index - Method 1 failed: ' . $e->getMessage());
            }

            // Method 2: Fallback simple query
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
                        $item['id_skema'] = $id_skema;
                    }
                } catch (\Exception $e) {
                    log_message('error', 'RekamanAsesmen::index - Method 2 failed: ' . $e->getMessage());
                }
            }

            // Validate asesmen data structure
            $validAsesmen = [];
            foreach ($asesmen as $a) {
                if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) {
                    $validAsesmen[] = $a;
                }
            }

            if (empty($validAsesmen)) {
                log_message('warning', 'No valid asesmen found for asesor ID: ' . $this->getCurrentAsesorId() . ', skema ID: ' . $id_skema);
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
                    'asesor_id' => $this->getCurrentAsesorId(),
                    'user_id' => $userId,
                    'skema_id' => $id_skema,
                    'asesmen_count' => count($validAsesmen),
                    'method_used' => $method_used
                ]
            ];

            return view('asesor/rekaman_kompetensi', $data);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmen::index - Exception: ' . $e->getMessage());
            log_message('error', 'RekamanAsesmen::index - Stack trace: ' . $e->getTraceAsString());

            // Return view with error info for debugging
            return view('asesor/rekaman_kompetensi', [
                'siteTitle' => 'Rekaman Asesmen Kompetensi',
                'asesor' => ['nama_lengkap' => 'N/A', 'nomor_registrasi' => 'N/A'],
                'skema' => ['nama_skema' => 'N/A', 'kode_skema' => 'N/A'],
                'asesmen' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show form for creating new rekaman asesmen
     */
    public function create()
    {
        try {
            // Get current user ID
            $userId = user()->id ?? 0;

            // Get asesor info with their skema
            $asesorInfo = $this->asesorModel->getWithSkema($this->getCurrentAsesorId());

            if (!$asesorInfo) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ID: ' . $userId);
            }

            // Check if asesor has assigned skema
            if (empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan');
            }

            $id_skema = $asesorInfo['id_skema'];

            // Get skema data
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema sertifikasi dengan ID ' . $id_skema . ' tidak ditemukan dalam database');
            }

            // Get asesmen data with fallback approach
            $asesmen = [];
            $method_used = '';

            // Method 1: Try with JOIN
            try {
                $asesmen = $this->db->table('asesmen')
                    ->select('asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, skema.nama_skema, skema.kode_skema')
                    ->join('skema', 'asesmen.id_skema = skema.id_skema', 'left')
                    ->where('asesmen.id_skema', $id_skema)
                    ->get()
                    ->getResultArray();

                $method_used = 'JOIN Query';
            } catch (\Exception $e) {
                log_message('error', 'RekamanAsesmen::create - Method 1 failed: ' . $e->getMessage());
            }

            // Method 2: Fallback simple query
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
                        $item['id_skema'] = $id_skema;
                    }
                } catch (\Exception $e) {
                    log_message('error', 'RekamanAsesmen::create - Method 2 failed: ' . $e->getMessage());
                }
            }

            // Validate asesmen data structure
            $validAsesmen = [];
            foreach ($asesmen as $a) {
                if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) {
                    $validAsesmen[] = $a;
                }
            }

            if (empty($validAsesmen)) {
                log_message('warning', 'No valid asesmen found for asesor ID: ' . $this->getCurrentAsesorId() . ', skema ID: ' . $id_skema);
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
                    'asesor_id' => $this->getCurrentAsesorId(),
                    'user_id' => $userId,
                    'skema_id' => $id_skema,
                    'asesmen_count' => count($validAsesmen),
                    'method_used' => $method_used
                ]
            ];

            return view('asesor/ceklist_rekaman_asesmen', $data);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmen::create - Exception: ' . $e->getMessage());
            log_message('error', 'RekamanAsesmen::create - Stack trace: ' . $e->getTraceAsString());

            // Return view with error info for debugging
            return view('asesor/ceklist_rekaman_asesmen', [
                'siteTitle' => 'Rekaman Asesmen Kompetensi',
                'asesor' => ['nama_lengkap' => 'N/A', 'nomor_registrasi' => 'N/A'],
                'skema' => ['nama_skema' => 'N/A', 'kode_skema' => 'N/A'],
                'asesmen' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store rekaman asesmen data (complete form submission)
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $this->db->transBegin();

            // Get form data
            $id_pengajuan = $this->request->getPost('id_pengajuan');
            $rekomendasi = $this->request->getPost('rekomendasi');
            $komentar = $this->request->getPost('catatan');
            $tindak_lanjut = $this->request->getPost('tindak_lanjut');

            // Validate required fields
            if (empty($id_pengajuan)) {
                throw new \Exception('ID Pengajuan tidak ditemukan. Pastikan Anda telah memilih asesi.');
            }

            if (empty($rekomendasi)) {
                throw new \Exception('Rekomendasi harus dipilih (Kompeten/Belum Kompeten)');
            }

            // Check if rekaman exists
            $existing = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $id_pengajuan)
                ->where('deleted_at', null)
                ->first();

            if ($existing) {
                // Update existing record
                $rekamanData = [
                    'rekomendasi' => $rekomendasi,
                    'komentar' => $komentar,
                    'tindak_lanjut' => $tindak_lanjut,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $this->rekamanAsesmenModel->update($existing['id'], $rekamanData);
                if (!$result) {
                    throw new \Exception('Gagal mengupdate rekaman yang ada');
                }
                $id_rekaman = $existing['id'];
            } else {
                // Create new record
                $rekamanData = [
                    'id_pengajuan' => $id_pengajuan,
                    'rekomendasi' => $rekomendasi,
                    'komentar' => $komentar,
                    'tindak_lanjut' => $tindak_lanjut,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $id_rekaman = $this->rekamanAsesmenModel->insert($rekamanData);

                if (!$id_rekaman) {
                    $error = $this->rekamanAsesmenModel->errors();
                    throw new \Exception('Gagal menyimpan data rekaman ke database: ' . json_encode($error));
                }
            }

            // Process competency data
            $kompetensi_data = $this->request->getPost('kompetensi');

            if (!empty($kompetensi_data)) {
                // Delete existing kompetensi data
                $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman', $id_rekaman)
                    ->delete();

                // Insert new kompetensi data
                foreach ($kompetensi_data as $id_unit => $methods) {
                    $kompetensiRecord = [
                        'id_rekaman' => $id_rekaman,
                        'id_unit' => $id_unit,
                        'metode_observasi' => isset($methods['observasi']) ? 1 : 0,
                        'metode_portofolio' => isset($methods['portofolio']) ? 1 : 0,
                        'metode_pihak_ketiga' => isset($methods['pihak_ketiga']) ? 1 : 0,
                        'metode_lisan' => isset($methods['lisan']) ? 1 : 0,
                        'metode_tertulis' => isset($methods['tertulis']) ? 1 : 0,
                        'metode_proyek' => isset($methods['proyek']) ? 1 : 0,
                        'metode_lainnya' => isset($methods['lainnya']) ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $insertResult = $this->rekamanAsesmenKompetensiModel->insert($kompetensiRecord);
                    if (!$insertResult) {
                        throw new \Exception('Gagal menyimpan data kompetensi untuk unit ID: ' . $id_unit);
                    }
                }
            }

            $this->db->transCommit();

            return $this->respond([
                'status' => 'success',
                'message' => 'Rekaman asesmen berhasil disimpan',
                'data' => [
                    'id_rekaman' => $id_rekaman
                ],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmen Store - Error: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan rekaman asesmen: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Auto-save method untuk checkbox kompetensi (seperti observasi)
     */
    public function saveMethod()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(['success' => false, 'message' => 'Invalid request'], 400);
        }

        try {
            $id_pengajuan = $this->request->getPost('id_pengajuan');
            $id_unit = $this->request->getPost('id_unit');
            $method = $this->request->getPost('method');
            $checked = $this->request->getPost('checked') === 'true';

            if (empty($id_pengajuan) || empty($id_unit) || empty($method)) {
                return $this->respond(['success' => false, 'message' => 'Data tidak lengkap'], 400);
            }

            // Get or create rekaman
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $id_pengajuan)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                // Create new rekaman
                $rekamanData = [
                    'id_pengajuan' => $id_pengajuan,
                    'rekomendasi' => '',
                    'komentar' => '',
                    'tindak_lanjut' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $id_rekaman = $this->rekamanAsesmenModel->insert($rekamanData);
                if (!$id_rekaman) {
                    throw new \Exception('Gagal membuat rekaman baru');
                }
            } else {
                $id_rekaman = $rekaman['id'];
            }

            // Get or create kompetensi record
            $kompetensi = $this->rekamanAsesmenKompetensiModel
                ->where('id_rekaman', $id_rekaman)
                ->where('id_unit', $id_unit)
                ->first();

            if (!$kompetensi) {
                // Create new kompetensi record
                $kompetensiData = [
                    'id_rekaman' => $id_rekaman,
                    'id_unit' => $id_unit,
                    'metode_observasi' => 0,
                    'metode_portofolio' => 0,
                    'metode_pihak_ketiga' => 0,
                    'metode_lisan' => 0,
                    'metode_tertulis' => 0,
                    'metode_proyek' => 0,
                    'metode_lainnya' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $kompetensiData['metode_' . $method] = $checked ? 1 : 0;

                $result = $this->rekamanAsesmenKompetensiModel->insert($kompetensiData);
                if (!$result) {
                    throw new \Exception('Gagal menyimpan data kompetensi');
                }
            } else {
                // Update existing kompetensi record
                $updateData = [
                    'metode_' . $method => $checked ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $this->rekamanAsesmenKompetensiModel->update($kompetensi['id'], $updateData);
                if (!$result) {
                    throw new \Exception('Gagal mengupdate data kompetensi');
                }
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmen SaveMethod Error: ' . $e->getMessage());
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Auto-save recommendation untuk form rekomendasi
     */
    public function saveRecommendation()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(['success' => false, 'message' => 'Invalid request'], 400);
        }

        try {
            $id_pengajuan = $this->request->getPost('id_pengajuan');
            $field = $this->request->getPost('field');
            $value = $this->request->getPost('value');

            if (empty($id_pengajuan) || empty($field)) {
                return $this->respond(['success' => false, 'message' => 'Data tidak lengkap'], 400);
            }

            // Validate field
            $allowedFields = ['rekomendasi', 'komentar', 'tindak_lanjut'];
            if (!in_array($field, $allowedFields)) {
                return $this->respond(['success' => false, 'message' => 'Field tidak valid'], 400);
            }

            // Get or create rekaman
            $rekaman = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $id_pengajuan)
                ->where('deleted_at', null)
                ->first();

            if (!$rekaman) {
                // Create new rekaman
                $rekamanData = [
                    'id_pengajuan' => $id_pengajuan,
                    'rekomendasi' => '',
                    'komentar' => '',
                    'tindak_lanjut' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $rekamanData[$field] = $value;

                $id_rekaman = $this->rekamanAsesmenModel->insert($rekamanData);
                if (!$id_rekaman) {
                    throw new \Exception('Gagal membuat rekaman baru');
                }
            } else {
                // Update existing rekaman
                $updateData = [
                    $field => $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $this->rekamanAsesmenModel->update($rekaman['id'], $updateData);
                if (!$result) {
                    throw new \Exception('Gagal mengupdate rekaman');
                }
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'RekamanAsesmen SaveRecommendation Error: ' . $e->getMessage());
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Load rekaman data for editing
     */
    public function loadRekamanAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->failValidationErrors('Request tidak valid');
        }

        try {
            $id_skema = $this->request->getGet('id_skema');
            $id_asesmen = $this->request->getGet('id_asesmen');
            $id_asesi = $this->request->getGet('id_asesi');

            if (empty($id_skema) || empty($id_asesmen) || empty($id_asesi)) {
                return $this->fail('Parameter tidak lengkap: id_skema, id_asesmen, dan id_asesi diperlukan');
            }

            // Get pengajuan data
            $pengajuan = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_skema,
                    u.nama_lengkap as nama_asesi,
                    s.nama_skema,
                    s.kode_skema
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'left')
                ->join('users u', 'u.id = a.id_user', 'left')
                ->join('skema s', 's.id_skema = pa.id_skema', 'left')
                ->where('pa.id_asesi', $id_asesi)
                ->where('pa.id_skema', $id_skema)
                ->where('pa.status_pengajuan', 'diterima')
                ->get()
                ->getRowArray();

            if (!$pengajuan) {
                return $this->fail('Data pengajuan tidak ditemukan untuk asesi dan skema yang dipilih');
            }

            // Get unit kompetensi untuk skema yang dipilih
            $units = $this->unitModel->getUnitsByScheme($id_skema);

            if (empty($units)) {
                return $this->fail('Tidak ada unit kompetensi ditemukan untuk skema ini');
            }

            // Get existing rekaman
            $existingRekaman = $this->rekamanAsesmenModel
                ->where('id_pengajuan', $pengajuan['id_pengajuan'])
                ->where('deleted_at', null)
                ->first();

            $existingData = [];
            $existingRecommendation = null;

            if ($existingRekaman) {
                // Get existing kompetensi data
                $existingKompetensi = $this->rekamanAsesmenKompetensiModel
                    ->where('id_rekaman', $existingRekaman['id'])
                    ->findAll();

                foreach ($existingKompetensi as $komp) {
                    $existingData[$komp['id_unit']] = [
                        'observasi' => $komp['metode_observasi'],
                        'portofolio' => $komp['metode_portofolio'],
                        'pihak_ketiga' => $komp['metode_pihak_ketiga'],
                        'lisan' => $komp['metode_lisan'],
                        'tertulis' => $komp['metode_tertulis'],
                        'proyek' => $komp['metode_proyek'],
                        'lainnya' => $komp['metode_lainnya']
                    ];
                }

                $existingRecommendation = [
                    'rekomendasi' => $existingRekaman['rekomendasi'],
                    'komentar' => $existingRekaman['komentar'],
                    'tindak_lanjut' => $existingRekaman['tindak_lanjut']
                ];
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data berhasil dimuat',
                'data' => [
                    'pengajuan' => $pengajuan,
                    'rekaman_asesmen' => $units,
                    'existing_data' => $existingData,
                    'existing_recommendation' => $existingRecommendation,
                    'totalUnits' => count($units)
                ],
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading rekaman asesmen: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Get asesi by asesmen
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
            // Get asesi data for this asesmen
            $asesiData = $this->pengajuanAsesmenModel->getAsesiByAsesmen($id_asesmen);

            $count = count($asesiData);
            log_message('info', 'Found ' . $count . ' asesi for asesmen ' . $id_asesmen);

            // Provide informative messages based on data availability
            $message = '';
            if ($count === 0) {
                $message = 'Belum ada asesi yang terdaftar untuk asesmen ini. Pastikan asesi sudah mengajukan permohonan dan statusnya telah disetujui.';
            } else {
                $message = 'Ditemukan ' . $count . ' asesi untuk asesmen ini.';
            }

            return $this->respond([
                'success' => true,
                'message' => $message,
                'asesi' => $asesiData,
                'isEmpty' => $count === 0,
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi by asesmen ID ' . $id_asesmen . ': ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->respond([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data asesi: ' . $e->getMessage(),
                'asesi' => [],
                'isEmpty' => true
            ], 500);
        }
    }

    /**
     * Generate PDF
     */
    public function pdf(int $id = null)
    {
        try {
            if (!$id) {
                throw new \Exception('ID rekaman tidak ditemukan');
            }

            $data = $this->getRekamanAsesmenData($id);

            // Validate required data
            if (empty($data['rekaman'])) {
                throw new \Exception('Data rekaman tidak ditemukan untuk ID: ' . $id);
            }

            // Generate QR codes if signatures exist
            if (!empty($data['rekaman']['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generateQRCode($data['rekaman']['ttd_asesi']);
            } else {
                $data['qr_asesi'] = '';
            }

            if (!empty($data['rekaman']['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generateQRCode($data['rekaman']['ttd_asesor']);
            } else {
                $data['qr_asesor'] = '';
            }

            // Add additional data needed for PDF views
            $this->enhanceDataForPdf($data);

            // Validate final data structure before PDF generation
            $this->validateDataForPdf($data);

            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal menggenerate PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate PDF output
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/rekaman_page1',
            'pdf/rekaman_page2',
        ];

        $filename = 'FR.AK.02. REKAMAN ASESMEN KOMPETENSI';
        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Get rekaman data for PDF
     */
    private function getRekamanAsesmenData(int $id_rekaman): array
    {
        try {
            // Get basic rekaman data with details
            $rekaman = $this->rekamanAsesmenModel->getRekamanWithDetails($id_rekaman);
            if (!$rekaman) {
                throw new \Exception('Rekaman dengan ID ' . $id_rekaman . ' tidak ditemukan');
            }

            // Get unit kompetensi untuk skema ini
            $units = $this->unitModel->getUnitsByScheme($rekaman['id_skema']);

            // Get existing kompetensi data
            $existingKompetensi = [];
            if (!empty($rekaman['kompetensi'])) {
                foreach ($rekaman['kompetensi'] as $komp) {
                    $existingKompetensi[$komp['id_unit']] = $komp;
                }
            }

            // Transform units to match PDF structure
            $unitStructure = [];
            foreach ($units as $unit) {
                $unitData = $unit;
                $unitData['methods'] = $existingKompetensi[$unit['id_unit']] ?? [];
                $unitStructure[] = $unitData;
            }

            // Get TUK info if available
            $tukInfo = [];
            if (!empty($rekaman['id_tuk'])) {
                $tukModel = model('TUKModel');
                $tukInfo = $tukModel->find($rekaman['id_tuk']);
            }

            return [
                'rekaman' => $rekaman,
                'units' => $unitStructure,
                'tuk' => $tukInfo,
                'observasi' => [
                    'nama_skema' => $rekaman['nama_skema'],
                    'kode_skema' => $rekaman['kode_skema'],
                    'nama_asesi' => $rekaman['nama_asesi'],
                    'nama_asesor' => $rekaman['nama_asesor'] ?? 'N/A',
                    'nama_tuk' => $tukInfo['nama_tuk'] ?? 'N/A'
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting rekaman data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add additional data needed for PDF views
     */
    private function enhanceDataForPdf(array &$data): void
    {
        // Get asesor information
        if (!isset($data['asesor'])) {
            $asesorId = $this->getCurrentAsesorId();
            $data['asesor'] = $this->asesorModel->find($asesorId);
        }

        // Get asesi information if not already included
        if (!isset($data['asesi']) && isset($data['rekaman']['id_asesi'])) {
            $asesiModel = model('AsesiModel');
            $data['asesi'] = $asesiModel->find($data['rekaman']['id_asesi']);
        }

        // Get skema information if not already included
        if (!isset($data['skema']) && isset($data['rekaman']['id_skema'])) {
            $data['skema'] = $this->skemaModel->find($data['rekaman']['id_skema']);
        }

        // Set PDF title
        $data['title'] = 'FR.AK.02. REKAMAN ASESMEN KOMPETENSI';

        // Format dates for PDF display
        if (isset($data['rekaman']['created_at'])) {
            $data['formatted_date'] = date('d F Y', strtotime($data['rekaman']['created_at']));
        } else {
            $data['formatted_date'] = date('d F Y');
        }
    }

    /**
     * Validate data structure before PDF generation
     */
    private function validateDataForPdf(array &$data): void
    {
        $requiredKeys = ['rekaman', 'units'];

        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                throw new \Exception("Required data key '{$key}' is missing");
            }
        }

        // Validate rekaman data
        if (empty($data['rekaman'])) {
            throw new \Exception('Rekaman data is empty');
        }

        // Initialize units structure if missing
        if (!isset($data['units'])) {
            $data['units'] = [];
        }

        if (!is_array($data['units'])) {
            $data['units'] = [];
        }

        // Ensure observasi data exists for template compatibility
        if (!isset($data['observasi'])) {
            $data['observasi'] = [
                'nama_skema' => $data['rekaman']['nama_skema'] ?? 'N/A',
                'kode_skema' => $data['rekaman']['kode_skema'] ?? 'N/A',
                'nama_asesi' => $data['rekaman']['nama_asesi'] ?? 'N/A',
                'nama_asesor' => 'N/A',
                'nama_tuk' => 'N/A'
            ];
        }

        // Ensure skema data exists
        if (!isset($data['skema'])) {
            $data['skema'] = [
                'nama_skema' => $data['rekaman']['nama_skema'] ?? 'N/A',
                'kode_skema' => $data['rekaman']['kode_skema'] ?? 'N/A'
            ];
        }
    }

    /**
     * Helper methods
     */
    private function getCurrentAsesorId(): ?int
    {
        $asesor = $this->asesorModel->getByUserId($this->id_asesor);
        return $asesor ? $asesor['id_asesor'] : null;
    }
}
