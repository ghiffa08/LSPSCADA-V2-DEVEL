<?php

namespace App\Controllers;

use App\Services\PDFService;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FeedbackController extends BaseController
{
    private $pdfService;

    public function __construct()
    {
        $this->pdfService = new PDFService();
    }


    public function index()
    {
        return view('dashboard/feedback', [
            'siteTitle' => 'Umpan Balik',
            'listFeedback' => $this->feedbackModel->findAll()
        ]);
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->feedbackModel->delete($id);
        session()->setFlashdata('pesan', 'Umpan Balik berhasil dihapus!');
        return redirect()->to('/umpan-balik');
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
            'pdf/feedback_page1',
        ];

        $filename = 'FR.AK.03. UMPAN BALIK DAN CATATAN ASESMEN';

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }
}
