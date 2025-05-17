<?php

namespace App\Controllers;

use App\Services\PDFService;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PMOController extends BaseController
{
    private $pdfService;
    private $formatterService;

    public function __construct()
    {
        $this->pdfService = new PDFService();
        $this->formatterService = new FormatterService();
    }

    public function index()
    {
        //
    }

    /**
     * Generate PDF for observation data
     *
     * @param int $id_skema Schema ID
     * @param int $id_asesmen Assessment ID (optional)
     * @param int $id_asesi Assessee ID
     * @return void
     */
    public function pdf(): void
    {
        try {
            $data = [
                'jenisSertifikasiFormatted' => $this->formatterService->formatJenisSertifikasi($dataAPL1['asesmen']['jenis_skema']),
            ];
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
            'pdf/pmo_page1',
            'pdf/pmo_page2',
        ];

        $filename = 'FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }
}
