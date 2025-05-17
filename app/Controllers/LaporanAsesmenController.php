<?php

namespace App\Controllers;

use App\Services\PDFService;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class LaporanAsesmenController extends BaseController
{
    private $pdfService;

    public function __construct()
    {
        $this->pdfService = new PDFService();
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
            $data = [];
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
            'pdf/laporan_page1',
            // 'pdf/pmo_page2',
        ];

        $filename = 'FR.AK.05. LAPORAN ASESMEN';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }
}
