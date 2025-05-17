<?php

namespace App\Services;

class PDFService
{
    /**
     * Generate a multi-page PDF from multiple views
     *
     * @param array $views List of view paths
     * @param array $data Data to pass to views
     * @param string $filename Filename without extension
     * @return void
     */
    public function generateMultiPagePdf(array $views, array $data, string $filename): void
    {
        $pdf = $this->initTCPDF($filename);

        foreach ($views as $view) {
            $pdf->AddPage();
            $html = view($view, $data);
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        $pdf->Output($filename . '.pdf', 'I');
        exit;
    }

    /**
     * Initialize TCPDF with standard settings
     *
     * @param string $title PDF title
     * @return \TCPDF
     */
    private function initTCPDF(string $title)
    {
        // This function should contain the same logic as your existing initTCPDF helper
        // You can move the helper logic here or keep using the helper
        return initTCPDF($title);
    }
}
