<?php

namespace App\Services;

use TCPDF;
use Exception;

class PDFService
{
    /**
     * Generate a multi-page PDF and stream it to the browser.
     *
     * @param array  $views    Array of view files to render.
     * @param array  $data     Data to pass to the views.
     * @param string $filename The desired filename for the download.
     * @return void
     */
    public function generateMultiPagePdf(array $views, array $data, string $filename): void
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('LSP');
            $pdf->SetTitle($data['pmo']['nama_skema'] ?? $filename);

            // Set header and footer
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI', PDF_HEADER_STRING);
            $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
            $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

            // Set margins and auto page breaks
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

            // Render views and add to PDF
            foreach ($views as $view) {
                $html = view($view, $data);
                $pdf->AddPage();
                $pdf->writeHTML($html, true, false, true, false, '');
            }

            // Output the PDF to the browser
            $pdf->Output($filename . '.pdf', 'I');
            exit(); // Stop script execution after sending the file
        } catch (Exception $e) {
            log_message('error', 'PDFService::generateMultiPagePdf Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a multi-page PDF and save it to a specified file path.
     *
     * @param array  $views    Array of view files to render.
     * @param array  $data     Data to pass to the views.
     * @param string $filePath The absolute path to save the PDF file, including the filename.
     * @return bool True on success, false on failure.
     */
    public function generateAndSave(array $views, array $data, string $filePath): bool
    {
        try {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('LSP');
            $pdf->SetTitle($data['pmo']['nama_skema'] ?? 'Laporan PMO');
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI', PDF_HEADER_STRING);
            $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
            $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

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
}
