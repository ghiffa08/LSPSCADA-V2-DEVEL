<?php

namespace App\Controllers;

use Config\Database;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
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
git 
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
}
