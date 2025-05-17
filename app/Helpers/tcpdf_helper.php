<?php

// use TCPDF;
require_once APPPATH . '../vendor/tecnickcom/tcpdf/tcpdf.php';

if (!function_exists('initTCPDF')) {
    function initTCPDF($title = 'Dokumen PDF')
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');
        $pdf->SetTitle($title);
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN', PDF_HEADER_STRING);
        $pdf->SetHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->SetFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/../Libraries/TCPDF/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/../Libraries/TCPDF/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // $pdf->SetFont('helvetica', '', 10);
        // $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddFont('notosanssymbols2', '', 'notosanssymbols2.php');
        $pdf->SetFont('notosanssymbols2', '', 10);


        return $pdf;
    }
}
