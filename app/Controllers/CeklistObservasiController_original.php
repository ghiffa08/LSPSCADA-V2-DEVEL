<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AsesorAsesmenService;
use App\Services\PDFService;
use App\Services\QRCodeService;
use App\Models\AsesmenModel;
use App\Models\SkemaModel;
use App\Models\ObservasiModel;
use CodeIgniter\HTTP\ResponseInterface;

class CeklistObservasiController extends BaseController
{
    protected AsesorAsesmenService $asesorAsesmenService;
    protected QRCodeService $qrCodeService;
    protected PDFService $pdfService;
    protected AsesmenModel $asesmenModel;
    protected SkemaModel $skemaModel;
    protected ObservasiModel $observasiModel;
    protected int $asesorId;
    protected object $currentAsesor;
    protected $db;

    public function __construct()
    {
        helper(['auth', 'form']);

        // Initialize services
        $this->asesorAsesmenService = new AsesorAsesmenService();
        $this->qrCodeService = new QRCodeService();
        $this->pdfService = new PDFService();
        
        // Initialize models
        $this->asesmenModel = new AsesmenModel();
        $this->skemaModel = new SkemaModel();
        $this->observasiModel = new ObservasiModel();
        
        // Initialize database
        $this->db = \Config\Database::connect();

        // Security check - Asesor only
        if (!in_groups(['Asesor', 'Admin'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        // Get current asesor info
        $this->currentAsesor = user();
        $this->asesorId = $this->currentAsesor->id;
    }

    /**
     * Display observasi dashboard for asesor
     */
    public function index(): string
    {
        try {
            // Get skema list for this asesor
            $skemaList = $this->asesorAsesmenService->getSkemaForAsesor($this->asesorId, [
                'status' => 'active'
            ]);

            // Get asesor statistics
            $statistics = $this->asesorAsesmenService->getAsesorStatistics($this->asesorId);

            $data = [
                'siteTitle' => 'Ceklist Observasi',
                'pageTitle' => 'Dashboard Observasi Asesor',
                'skemaList' => $skemaList,
                'statistics' => $statistics,
                'asesorInfo' => [
                    'id' => $this->asesorId,
                    'nama' => $this->currentAsesor->nama_lengkap,
                    'email' => $this->currentAsesor->email
                ],
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                    ['label' => 'Observasi', 'url' => '', 'active' => true]
                ]
            ];

            return view('asesor/observasi/skema_dashboard', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in observasi index: ' . $e->getMessage());
            
            return view('asesor/observasi/skema_dashboard', [
                'siteTitle' => 'Ceklist Observasi',
                'pageTitle' => 'Dashboard Observasi Asesor',
                'skemaList' => [],
                'statistics' => [],
                'asesorInfo' => [
                    'id' => $this->asesorId,
                    'nama' => $this->currentAsesor->nama_lengkap ?? 'Unknown',
                    'email' => $this->currentAsesor->email ?? 'unknown@email.com'
                ],
                'error' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }/**
     * Create new observasi checklist
     */
    public function create(): string
    {
        try {
            // Get available asesmen for this asesor
            $availableAsesmen = $this->getAvailableAsesmenForAsesor();

            // Get skema list for dropdown
            $skemaList = $this->asesorAsesmenService->getSkemaForAsesor($this->asesorId, [
                'status' => 'active'
            ]);

            $data = [
                'siteTitle' => 'Buat Ceklist Observasi',
                'pageTitle' => 'Buat Ceklist Observasi Baru',
                'availableAsesmen' => $availableAsesmen,
                'skemaList' => $skemaList,
                'asesorInfo' => [
                    'id' => $this->asesorId,
                    'nama' => $this->currentAsesor->nama_lengkap
                ],
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                    ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                    ['label' => 'Buat Ceklist', 'url' => '', 'active' => true]
                ]
            ];

            return view('asesor/observasi/create', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in observasi create: ' . $e->getMessage());
            
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Terjadi kesalahan saat memuat form.');
        }
    }

    /**
     * Get available asesmen for asesor
     */
    private function getAvailableAsesmenForAsesor(): array
    {
        try {
            return $this->db->table('asesmen a')
                ->select('a.id_asesmen, a.tujuan, s.nama_skema, s.kode_skema, 
                         t.nama_tuk, tg.tanggal_mulai, tg.tanggal_selesai,
                         COUNT(obs.id_observasi) as existing_observasi')
                ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
                ->join('tuk t', 'a.id_tuk = t.id_tuk', 'inner')
                ->join('tanggal_asesmen tg', 'a.id_tanggal = tg.id_tanggal', 'inner')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
                ->join('observasi obs', 'a.id_asesmen = obs.id_asesmen AND obs.id_asesor = ' . $this->asesorId, 'left')
                ->where('aa.id_asesor', $this->asesorId)
                ->where('s.status_skema', 'active')
                ->groupBy('a.id_asesmen')
                ->orderBy('tg.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error getting available asesmen: ' . $e->getMessage());
            return [];
        }
    }
            $availableAsesmen = $this->getAvailableAsesmenForAsesor();

            $data = [
                'siteTitle' => 'Buat Ceklist Observasi',
                'pageTitle' => 'Buat Ceklist Observasi Baru',
                'availableAsesmen' => $availableAsesmen,
                'asesorInfo' => [
                    'id' => $this->asesorId,
                    'nama' => $this->currentAsesor->nama_lengkap
                ],
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                    ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                    ['label' => 'Buat Ceklist', 'url' => '', 'active' => true]
                ]
            ];

            return view('asesor/observasi/create', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in observasi create: ' . $e->getMessage());
            
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Terjadi kesalahan saat memuat form.');
        }
    }

        $data = [
            'siteTitle' => 'Ceklis Observasi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('asesor/ceklist_observasi', $data);
    }

    /**
     * Generate PDF for observation data
     *
     * @param int $id_observasi Observasi ID
     * @return void
     */
    public function pdf(int $id_observasi): void
    {
        try {
            // Reuse the same data preparation method as loadObservasi
            $data = $this->getObservasiData($id_observasi);

            // Generate QR codes
            if (!empty($data['observasi']['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesi/' . $data['observasi']['ttd_asesi']),
                    'logolsp.png'
                );
            }

            if (!empty($data['observasi']['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesor/' .  $data['observasi']['ttd_asesor']),
                    'logolsp.png'
                );
            }

            // Generate PDF with the prepared data 
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            // Redirect with error message or handle error appropriately
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
            'pdf/observasi_page1',
            // 'pdf/observasi_page2',
        ];

        $filename = 'FR.IA.01. CEKLIST OBSERVASI';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Common method to get all required observation data
     * Reduces code duplication between AJAX and PDF methods
     *
     * @param int $id_skema Schema ID
     * @param int $id_asesi Assessee ID
     * @return array All data needed for both AJAX response and PDF generation
     */
    private function getObservasiData(int $id_observasi): array
    {
        $detailObservasi = $this->observasiModel->getStrukturById($id_observasi);

        $existing_data = $this->observasiModel->getExistingById($id_observasi);

        $observasi = $this->observasiModel->getById($id_observasi);

        $kelompokWithUnit = $this->observasiModel->getWorkGroupsWithUnitsById($id_observasi);

        return [
            'kelompokWithUnit' => $kelompokWithUnit,
            'observasi' => $observasi,
            'detailObservasi' => $detailObservasi,
            'existing_data' => $existing_data,
        ];
    }

    /**
     * Get available asesmen for current asesor
     */
    private function getAvailableAsesmenForAsesor(): array
    {
        try {
            return $this->asesorAsesmenService->getSkemaForAsesor($this->asesorId, [
                'status' => 'active',
                'limit' => 50
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error getting available asesmen: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Store new observasi checklist
     */
    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            // Validation rules
            $rules = [
                'id_asesmen' => [
                    'label' => 'Asesmen',
                    'rules' => 'required|integer|greater_than[0]'
                ],
                'checklist_data' => [
                    'label' => 'Data Checklist',
                    'rules' => 'required'
                ]
            ];

            if (!$this->validateData($this->request->getPost(), $rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak valid',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $asesmenId = $this->request->getPost('id_asesmen');
            
            // Verify asesor has access to this asesmen
            if (!$this->asesorAsesmenService->asesorHasAccessToAsesmen($this->asesorId, $asesmenId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ]);
            }

            // Prepare observasi data
            $observasiData = [
                'id_asesmen' => $asesmenId,
                'id_asesor' => $this->asesorId,
                'checklist_data' => json_encode($this->request->getPost('checklist_data')),
                'notes' => $this->request->getPost('notes') ?? '',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $observasiId = $this->observasiModel->insert($observasiData);

            if ($observasiId) {
                // Clear cache
                $this->asesorAsesmenService->clearAsesorCache($this->asesorId);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Ceklist observasi berhasil disimpan',
                    'data' => ['id' => $observasiId]
                ]);
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan ceklist observasi'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error storing observasi: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * View specific asesmen detail
     */
    public function viewAsesmen(int $asesmenId): string
    {
        try {
            // Verify access
            if (!$this->asesorAsesmenService->asesorHasAccessToAsesmen($this->asesorId, $asesmenId)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Asesmen tidak ditemukan');
            }

            // Get asesmen detail
            $asesmenDetail = $this->asesorAsesmenService->getAsesmenDetailForAsesor($this->asesorId, $asesmenId);
            
            if (!$asesmenDetail) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Asesmen tidak ditemukan');
            }

            $data = [
                'siteTitle' => 'Detail Asesmen',
                'pageTitle' => 'Detail Asesmen: ' . $asesmenDetail['nama_skema'],
                'asesmenDetail' => $asesmenDetail,
                'asesorInfo' => [
                    'id' => $this->asesorId,
                    'nama' => $this->currentAsesor->nama_lengkap
                ],
                'breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                    ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                    ['label' => 'Detail Asesmen', 'url' => '', 'active' => true]
                ]
            ];

            return view('asesor/observasi/detail_asesmen', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error viewing asesmen: ' . $e->getMessage());
            
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Asesmen tidak ditemukan atau terjadi kesalahan.');
        }
    }
}
