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

            return view('asesor/observasi/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in observasi index: ' . $e->getMessage());

            return view('asesor/observasi/index', [
                'siteTitle' => 'Ceklist Observasi',
                'pageTitle' => 'Dashboard Observasi Asesor',
                'skemaList' => [],
                'statistics' => [],
                'error' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }

    /**
     * Create new observasi checklist
     */
    public function create(): string
    {
        try {
            // Get available asesmen for this asesor
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

    /**
     * Generate PDF for observation data
     */
    public function pdf(int $observasiId): void
    {
        try {
            // Verify access
            $observasi = $this->observasiModel->find($observasiId);
            if (!$observasi) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Observasi tidak ditemukan');
            }

            // Check if asesor has access to this observasi
            if (!$this->asesorAsesmenService->asesorHasAccessToAsesmen($this->asesorId, $observasi['id_asesmen'])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
            }

            // Get observasi data
            $data = $this->getObservasiData($observasiId);

            // Generate QR codes for digital signatures
            if (!empty($data['observasi']['ttd_asesi'])) {
                $data['qr_asesi'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesi/' . $data['observasi']['ttd_asesi']),
                    'logolsp.png'
                );
            }

            if (!empty($data['observasi']['ttd_asesor'])) {
                $data['qr_asesor'] = $this->qrCodeService->generate(
                    base_url('/scan-tanda-tangan-asesor/' . $data['observasi']['ttd_asesor']),
                    'logolsp.png'
                );
            }

            // Generate PDF
            $this->generatePdf($data);
        } catch (\Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Error generating PDF');
        }
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
     * Generate and output PDF
     */
    private function generatePdf(array $data): void
    {
        $views = [
            'pdf/observasi_page1',
            // Add more pages as needed
        ];

        $filename = 'FR.IA.01_CEKLIST_OBSERVASI_' . date('Y-m-d_H-i-s');

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Get all required observation data for PDF and display
     */
    private function getObservasiData(int $observasiId): array
    {
        try {
            $detailObservasi = $this->observasiModel->getStrukturById($observasiId);
            $existingData = $this->observasiModel->getExistingById($observasiId);
            $observasi = $this->observasiModel->getById($observasiId);
            $kelompokWithUnit = $this->observasiModel->getWorkGroupsWithUnitsById($observasiId);

            return [
                'kelompokWithUnit' => $kelompokWithUnit,
                'observasi' => $observasi,
                'detailObservasi' => $detailObservasi,
                'existing_data' => $existingData,
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting observasi data: ' . $e->getMessage());
            return [];
        }
    }
}
