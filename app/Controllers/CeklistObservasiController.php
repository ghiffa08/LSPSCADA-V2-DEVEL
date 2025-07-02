<?php

namespace App\Controllers;

use CodeIgniter\Model;
use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

class CeklistObservasiController extends ResourceController
{

    use ResponseTrait;

    protected QRCodeService $qrCodeService;
    protected int $id_asesor;
    protected string $nama_asesor;
    protected object $asesmenModel;
    protected object $skemaModel;
    protected object $observasiModel;
    protected PDFService $pdfService;


    public function __construct()
    {
        helper('auth');

        $this->qrCodeService = new QRCodeService();
        $this->asesmenModel = model('AsesmenModel');
        $this->skemaModel = model('SkemaModel');
        $this->observasiModel = model('ObservasiModel');
        $this->pdfService = new PDFService();
        $this->id_asesor = user()->id;
    }

    public function index()
    {

        $data = [
            'siteTitle' => 'Ceklis Observasi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('admin/observasi', $data);
    }

    public function create()
    {
        try {
            // Get current user ID
            $userId = user()->id ?? 0;

            // Get asesor info with their skema (one-to-one)
            $asesorModel = model('AsesorModel');
            $asesorInfo = $asesorModel->getWithSkema($this->getCurrentAsesorId());

            if (!$asesorInfo) {
                throw new \Exception('Data asesor tidak ditemukan untuk user ID: ' . $userId);
            }

            // Check if asesor has assigned skema
            if (empty($asesorInfo['id_skema'])) {
                throw new \Exception('Asesor belum memiliki skema kompetensi yang ditetapkan');
            }

            $id_skema = $asesorInfo['id_skema'];

            // Get skema data
            $skemaModel = model('SkemaModel');
            $skema = $skemaModel->find($id_skema);

            if (!$skema) {
                throw new \Exception('Skema sertifikasi dengan ID ' . $id_skema . ' tidak ditemukan dalam database');
            }

            // Multiple fallback approach for getting asesmen data
            $asesmen = [];
            $method_used = '';

            // Method 1: Try with JOIN
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
                log_message('error', 'CeklistObservasi::create - Method 1 failed: ' . $e->getMessage());
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
                    }
                } catch (\Exception $e) {
                    log_message('error', 'CeklistObservasi::create - Method 2 failed: ' . $e->getMessage());
                }
            }

            // Method 3: Check total asesmen in database
            if (empty($asesmen)) {
                $totalAsesmen = $this->asesmenModel->countAll();
                $method_used = 'No Data Found';

                if ($totalAsesmen == 0) {
                    log_message('error', 'CeklistObservasi::create - Asesmen table is completely empty');
                }
            }

            // Validate asesmen data structure
            $validAsesmen = [];
            foreach ($asesmen as $a) {
                if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) {
                    // Ensure required fields
                    if (!isset($a['nama_skema'])) $a['nama_skema'] = $skema['nama_skema'];
                    if (!isset($a['kode_skema'])) $a['kode_skema'] = $skema['kode_skema'];
                    $validAsesmen[] = $a;
                }
            }

            // Prepare data for view
            $data = [
                'siteTitle' => 'Ceklis Observasi',
                'asesor' => $asesorInfo,
                'skema' => [
                    'id_skema' => $id_skema,
                    'nama_skema' => $asesorInfo['nama_skema'] ?? $skema['nama_skema'],
                    'kode_skema' => $asesorInfo['kode_skema'] ?? $skema['kode_skema'],
                    'jenis_skema' => $asesorInfo['jenis_skema'] ?? $skema['jenis_skema'] ?? ''
                ],
                'asesmen' => $validAsesmen
            ];

            return view('asesor/ceklist_observasi', $data);
        } catch (\Exception $e) {
            log_message('error', 'CeklistObservasi::create - Exception: ' . $e->getMessage());
            log_message('error', 'CeklistObservasi::create - Stack trace: ' . $e->getTraceAsString());

            // Return view with error info for debugging
            return view('asesor/ceklist_observasi', [
                'siteTitle' => 'Ceklis Observasi',
                'asesor' => $asesorInfo ?? [],
                'skema' => [],
                'asesmen' => [],
                'error_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get current asesor ID from session user
     *
     * @return int|null
     */
    private function getCurrentAsesorId(): ?int
    {
        $asesorModel = model('AsesorModel');
        $asesor = $asesorModel->getByUserId($this->id_asesor);
        return $asesor ? $asesor['id_asesor'] : null;
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
     * Get asesi data by asesmen ID
     * AJAX endpoint for loading asesi dropdown
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
            $pengajuanModel = model('PengajuanAsesmenModel');
            $asesiData = $pengajuanModel->getAsesiByAsesmen($id_asesmen);

            $count = count($asesiData);

            // Provide informative messages based on data availability
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
}
