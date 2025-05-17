<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;

class CeklistObservasiController extends ResourceController
{

    use ResponseTrait;

    protected $qrCodeService;
    protected $id_asesor;
    protected $nama_asesor;
    protected $asesmenModel;
    protected $skemaModel;
    protected $observasiModel;
    protected $pdfService;


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

        $data = [
            'siteTitle' => 'Ceklis Observasi',
            'skema' => $this->asesmenModel->getAllAsesmen()
        ];

        return view('asesor/ceklist_observasi', $data);
    }

    /**
     * Get skema details and available asesi
     */
    public function getSkemaDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_skema = $this->request->getGet('id_skema');

        if (!$id_skema) {
            return $this->fail('ID Skema diperlukan', 400);
        }

        try {
            // Get skema details
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                return $this->fail('Skema tidak ditemukan', 404);
            }

            // Get asesi list for this skema
            $asesi = $this->observasiModel->getAsesiBySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'asesi' => $asesi
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema details: ' . $e->getMessage());
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
     * Load observation data via AJAX
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function loadObservasi()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_skema = $this->request->getGet('id_skema');
        $id_asesi = $this->request->getGet('id_asesi');

        if (!$id_skema || !$id_asesi) {
            return $this->fail('ID Skema dan ID Asesi diperlukan', 400);
        }

        try {
            // Fetch data for both AJAX response and PDF generation
            $data = $this->getObservasiData($id_skema, $id_asesi);

            // Count total KUK for progress bar
            $totalKUK = 0;
            foreach ($data['detailObservasi'] as $row) {
                if (!empty($row['id_kuk'])) {
                    $totalKUK++;
                }
            }

            $data['totalKUK'] = $totalKUK;

            return $this->respond([
                'success' => true,
                'observasi' => $data['detailObservasi'],
                'existing_data' => $data['existing_data'],
                'totalKUK' => $totalKUK
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading observasi data: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Universal handler for all observation saves
     * This single endpoint handles settings, bulk save, single KUK and batch saves
     */
    public function save()
    {
        // Determine request type
        $isAjax = $this->request->isAJAX();

        // Get the raw input for JSON processing
        $rawInput = $this->request->getBody();
        $jsonData = json_decode($rawInput, true);

        // Determine request type from either POST or JSON
        $requestType = $this->request->getPost('save_type') ?? ($jsonData['save_type'] ?? 'full');

        // Common data collection - check both POST and JSON
        $id_asesi = $this->request->getPost('id_asesi') ?? ($jsonData['id_asesi'] ?? null);
        $id_skema = $this->request->getPost('id_skema') ?? ($jsonData['id_skema'] ?? null);
        $id_asesmen = $this->request->getPost('id_asesmen') ?? ($jsonData['id_asesmen'] ?? null);
        $tanggal_observasi = $this->request->getPost('tanggal_observasi') ?? ($jsonData['tanggal_observasi'] ?? date('Y-m-d'));
        $id_asesor = $this->request->getPost('id_asesor') ?? ($jsonData['id_asesor'] ?? $this->id_asesor);

        // Validate common required fields
        if (empty($id_asesi)) {
            return $this->handleErrorResponse('ID Asesi diperlukan', $isAjax);
        }

        try {
            // Get APL1 ID from pengajuan_asesmen
            $db = Database::connect();
            $pengajuan = $db->table('pengajuan_asesmen')
                ->select('id_apl1')
                ->where('id_asesi', $id_asesi)
                ->where('id_asesmen', $id_asesmen)
                ->where('deleted_at', null)
                ->get()
                ->getRow();

            $id_apl1 = $pengajuan->id_apl1 ?? '0';

            // Check for existing observation
            $observasi = null;
            if (!empty($id_asesi) && !empty($tanggal_observasi)) {
                $observasi = $db->table('observasi')
                    ->where('id_asesi', $id_asesi)
                    ->where('tanggal_observasi', $tanggal_observasi)
                    ->get()
                    ->getRow();
            }

            $id_observasi = $this->request->getPost('id_observasi') ?? ($observasi->id_observasi ?? null);

            // Prepare master data
            $masterData = [
                'id_observasi' => $id_observasi,
                'id_asesor' => $id_asesor,
                'id_asesi' => $id_asesi,
                'id_apl1' => $id_apl1,
                'tanggal_observasi' => $tanggal_observasi
            ];

            // Process based on request type
            $detailData = null;
            $singleKUK = false;

            switch ($requestType) {
                case 'settings':
                    // Only master data, no details
                    break;

                case 'kuk':
                    // Single KUK save
                    $id_skema = $this->request->getPost('id_skema');
                    $id_kuk = $this->request->getPost('id_kuk');
                    $kompeten = $this->request->getPost('kompeten');
                    $keterangan = $this->request->getPost('keterangan');

                    if (empty($id_skema) || empty($id_kuk) || empty($kompeten)) {
                        return $this->handleErrorResponse('Semua field KUK diperlukan', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $id_skema,
                        'id_kuk' => $id_kuk,
                        'kompeten' => $kompeten,
                        'keterangan' => $keterangan,
                        'tanggal_observasi' => $tanggal_observasi
                    ];
                    $singleKUK = true;
                    break;

                case 'batch':
                    // Batch KUK save from JSON
                    if (empty($jsonData['id_skema']) || empty($jsonData['items'])) {
                        return $this->handleErrorResponse('Data batch tidak lengkap', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $jsonData['id_skema'],
                        'items' => $jsonData['items']
                    ];
                    break;

                case 'full':
                default:
                    // Full form submission
                    $id_skema = $this->request->getPost('id_skema');
                    $kuk = $this->request->getPost('kuk') ?? [];
                    $keterangan = $this->request->getPost('keterangan') ?? [];

                    if (empty($id_skema)) {
                        return $this->handleErrorResponse('ID Skema diperlukan', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $id_skema,
                        'kuk' => $kuk,
                        'keterangan' => $keterangan
                    ];
                    break;
            }

            // Process the data
            $result = $this->observasiModel->saveObservasiData($masterData, $detailData, $singleKUK);

            // Handle success response
            return $this->handleSuccessResponse('Data observasi berhasil disimpan', $isAjax, $result);
        } catch (\Exception $e) {
            log_message('error', 'Error saving observasi: ' . $e->getMessage());
            return $this->handleErrorResponse('Gagal menyimpan data: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Generate PDF for observation data
     *
     * @param int $id_skema Schema ID
     * @param int $id_asesmen Assessment ID (optional)
     * @param int $id_asesi Assessee ID
     * @return void
     */
    public function pdf(int $id_skema, string $id_asesi): void
    {
        try {
            // Reuse the same data preparation method as loadObservasi
            $data = $this->getObservasiData($id_skema, $id_asesi);

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
    private function getObservasiData(int $id_skema, string $id_asesi): array
    {
        // Get observation structure data
        $detailObservasi = $this->observasiModel->getStrukturObservasiSkema($id_skema);

        // Get existing observation data if available
        $existing_data = $this->observasiModel->getExistingObservasi($id_asesi);

        // Get observation metadata
        $observasi = $this->observasiModel->getObservasiData($id_asesi, $id_skema);

        // Get kelompok kerja data
        $kelompokWithUnit = $this->skemaModel->getWorkGroupsWithUnits($id_skema);

        return [
            'kelompokWithUnit' => $kelompokWithUnit,
            'observasi' => $observasi,
            'detailObservasi' => $detailObservasi,
            'existing_data' => $existing_data,
        ];
    }

    /**
     * Helper method to handle error responses
     */
    private function handleErrorResponse($message, $isAjax)
    {
        if ($isAjax) {
            return $this->fail($message, 400);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Helper method to handle success responses
     */
    private function handleSuccessResponse($message, $isAjax, $result = null)
    {
        if ($isAjax) {
            return $this->respond([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'token' => csrf_hash() // Return new CSRF token
            ]);
        }

        return redirect()->to('asesmen')
            ->with('success', $message);
    }
}
