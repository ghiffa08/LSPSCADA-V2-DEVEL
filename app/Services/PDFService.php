<?php

namespace App\Services;

use TCPDF;
use Exception;
use App\Services\HeaderService; // Impor HeaderService

class PDFService
{
    protected $headerService;

    public function __construct()
    {
        $this->headerService = new HeaderService();
    }

    /**
     * Generate a multi-page PDF and stream it to the browser.
     * DIUBAH: Menambahkan parameter $assessorId.
     *
     * @param array    $views    Array of view files to render.
     * @param array    $data     Data to pass to the views.
     * @param string   $filename The desired filename for the download.
     * @param int|null $assessorId ID asesor untuk menentukan header.
     * @return void
     */
    public function generateMultiPagePdf(array $views, array $data, string $filename, ?int $assessorId = null): void
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Panggil metode konfigurasi dengan meneruskan assessorId
            $this->configurePdfInstance($pdf, $data, $assessorId, $filename);

            foreach ($views as $view) {
                $html = view($view, $data);
                $pdf->AddPage();
                $pdf->writeHTML($html, true, false, true, false, '');
            }

            $pdf->Output($filename . '.pdf', 'I');
            exit();
        } catch (Exception $e) {
            log_message('error', 'PDFService::generateMultiPagePdf Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a multi-page PDF and save it to a specified file path.
     * DIUBAH: Menambahkan parameter $assessorId.
     *
     * @param array    $views    Array of view files to render.
     * @param array    $data     Data to pass to the views.
     * @param string   $filePath The absolute path to save the PDF file.
     * @param int|null $assessorId ID asesor untuk menentukan header.
     * @return bool True on success, false on failure.
     */
    public function generateAndSave(array $views, array $data, string $filePath, ?int $assessorId = null): bool
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

            $this->configurePdfInstance($pdf, $data, $assessorId);

            foreach ($views as $view) {
                $html = view($view, $data);
                $pdf->AddPage();
                $pdf->writeHTML($html, true, false, true, false, '');
            }

            $pdf->Output($filePath, 'F');
            return file_exists($filePath);
        } catch (Exception $e) {
            log_message('error', 'PDFService::generateAndSave Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Metode terpusat untuk konfigurasi dasar PDF.
     * DIUBAH: Menerima $assessorId.
     */
    private function configurePdfInstance(TCPDF &$pdf, array $data, ?int $assessorId, string $fallbackTitle = 'Laporan'): void
    {
        // Ambil konfigurasi header dinamis berdasarkan ID asesor
        $headerConfig = $this->headerService->getHeaderForAssessor($assessorId);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP');
        $pdf->SetTitle($data['pmo']['nama_skema'] ?? $fallbackTitle);

        // Path logo sekarang menuju folder uploads
        $logoPath = ROOTPATH . 'public/uploads/logos/' . $headerConfig->logo;

        // Cek jika file logo ada, jika tidak, jangan tampilkan gambar rusak
        if (!file_exists($logoPath) || empty($headerConfig->logo)) {
            $logoPath = ''; // Kosongkan path jika file tidak ada
        }

        $pdf->SetHeaderData($logoPath, $headerConfig->logo_width, $headerConfig->title, $headerConfig->header_string);

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    }
}
