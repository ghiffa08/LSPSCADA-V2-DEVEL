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
        // Get asesor info and their skema
        $asesorModel = model('AsesorModel');
        $asesorInfo = $asesorModel->getByUserIdWithUser($this->id_asesor);

        if (!$asesorInfo) {
            return redirect()->back()->with('error', 'Data asesor tidak ditemukan. Silakan hubungi administrator.');
        }

        // Get skema based on asesor's bidang_kompetensi
        $skemaModel = model('SkemaModel');
        $skemaList = $skemaModel->where('nama_skema', $asesorInfo['bidang_kompetensi'])->findAll();

        if (empty($skemaList)) {
            return redirect()->back()->with('error', 'Skema sertifikasi asesor tidak ditemukan dalam database.');
        }

        // Get asesmen data for the skema
        $asesmen = [];
        foreach ($skemaList as $skema) {
            $asesmenData = $this->asesmenModel->where('id_skema', $skema['id_skema'])->findAll();
            foreach ($asesmenData as $data) {
                $data['nama_skema'] = $skema['nama_skema'];
                $data['kode_skema'] = $skema['kode_skema'];
                $asesmen[] = $data;
            }
        }

        $data = [
            'siteTitle' => 'Ceklis Observasi',
            'asesor' => $asesorInfo,
            'skemaList' => $skemaList,
            'asesmen' => $asesmen
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
            log_message('info', "Loaded " . $count . " asesi for asesmen ID: " . $id_asesmen);

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
