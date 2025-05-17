<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use TCPDF;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class AKController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => 'Persetujuan Asesmen Dan Kerahasiaan',
            'listAk' => $this->apl2Model->AllpersetujuanAsesmen(),
        ];
        // dd($data);
        return view('admin/kelola_ak1', $data);
    }

    public function pdf($id)
    {
        // PDF PERSETJUAN ASESMEN

        $dataAPL1 = $this->apl2Model->persetujuanAsesmen($id);

        $jenis_sertifikasi = '';

        if ($dataAPL1['jenis_skema'] == "KKNI") {
            $jenis_sertifikasi = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span> / <span style="text-decoration: line-through;">Klaster</span>';
        } elseif ($dataAPL1['jenis_skema'] == "Okupasi") {
            $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/ Okupasi / <span style="text-decoration: line-through;">Klaster</span>';
        } else {
            $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span> / <span style="text-decoration: line-through;">Okupasi</span>/Klaster';
        }

        $jenis_tuk = '';

        if ($dataAPL1['jenis_tuk'] == "Sewaktu") {
            $jenis_tuk = 'Sewaktu / <span style="text-decoration: line-through;">Tempat Kerja</span> / <span style="text-decoration: line-through;">Mandiri</span>';
        } elseif ($dataAPL1['jenis_skema'] == "Tempat Kerja") {
            $jenis_tuk = '<span style="text-decoration: line-through;">Sewaktu/span> / Tempat Kerja</<span style="text-decoration: line-through;">Mandiri</span>';
        } else {
            $jenis_tuk = '<span style="text-decoration: line-through;">Sewaktu</span> / <span style="text-decoration: line-through;">Tempat Kerja</span> / Mandiri';
        }

        $writer = new PngWriter();

        // Create QR code
        $ttd_asesi = QrCode::create(base_url('/scan-tanda-tangan-asesi/' . $dataAPL1['tanda_tangan_asesi']))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(200)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $logo = Logo::create('logolsp.png')
            ->setResizeToWidth(50);

        // Create generic label
        // $label = Label::create($dataAPL1['nama_siswa'])
        //     ->setTextColor(new Color(000, 0, 0));

        // $result = $writer->write($ttd_asesi, $logo, $label);
        $result = $writer->write($ttd_asesi, $logo);

        $qr_asesi = $result->getDataUri();

        $ttd_asesor = QrCode::create(base_url('/scan-tanda-tangan-asesor/' . $dataAPL1['ttd_validator']))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(200)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($ttd_asesor, $logo);

        $qr_asesor = $result->getDataUri();

        // Create a new PDF document
        $pdf = new TCPDF(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            'A4',
            true,
            'UTF-8',
            false
        );

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

        // Set title based on user's full name
        $pdf->SetTitle('FR.AK.01. ' . $dataAPL1['nama_siswa']);

        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN', PDF_HEADER_STRING);

        // Set header and footer fonts
        $pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        // $pdf->SetAutoPageBreak(true, 5);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add a page
        $pdf->AddPage();

        $html = '
                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
                </head>
                <style>
                    table, th, td {
                        border: 1px solid black;
                        border-collapse: collapse;
                        padding: 2.5px;
                    }
                </style>
                <body>
                ';

        $html .= '
            <h4>FR.AK.01. PERSETUJUAN ASESMEN DAN KERAHASIAAN</h4>
            <table>
            <tr>
                <td>Persetujuan Asesmen ini untuk menjamin bahwa asesi telah diberi arahan secara rinci tentang perencanaan dan proses asesmen</td>
            </tr>
           <tr>
                  <td style="width: 30%;" rowspan="2">Skema Sertifikasi<br>(' . $jenis_sertifikasi . ')   
                  </td>
                  <td style="width: 10%;">Judul</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
                </tr>
            <tr>
                <td>Nomor</td>
                <td style="text-align:center;">:</td>
            </tr>
           <tr>
                  <td style="width: 40%;">TUK</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 55%;">' . $jenis_tuk . '</td>
                </tr>
           <tr>
                  <td style="width: 40%;">Nama Asesor</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 55%;">' . $dataAPL1['validator'] . '</td>
                </tr>
           <tr>
                  <td style="width: 40%;">Nama Asesi</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 55%;">' . $dataAPL1['nama_siswa'] . '</td>
                </tr>
          <tr>
                  <td style="width: 30%;" rowspan="3">Pelaksanaan asesmen disepakati pada:</td>
                  <td style="width: 10%;">Tanggal</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 55%;">' . date('d/m/Y', strtotime($dataAPL1['tanggal'])) . '</td>
                </tr>
             <tr>
                <td>Waktu</td>
                <td style="text-align:center;">:</td>
                <td>' . date('H:i', strtotime($dataAPL1['tanggal'])) . '</td>
            </tr>
            <tr>
                <td>TUK</td>
                <td style="text-align:center;">:</td>
                <td>' . $dataAPL1['nama_tuk'] . '</td>
            </tr>
              <tr>
                  <td style="width:40%;" rowspan="5">Bukti yang akan dikumpulkan</td>
                  <td style="width: 5%; text-align:center;" rowspan="5">:</td>
                  <td style="width: 25%;"><span style="text-decoration: line-through;">Hasil Verifikasi Portofolio</span></td>
                  <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Reviu Produk</span></td>
                </tr>

            <tr>
               <td style="width: 25%;">Hasil Obeservasi Langsung</td>
                  <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Kegiatan Terstruktur</span></td>
            </tr>
            <tr>
               <td style="width: 25%;"><span style="text-decoration: line-through;">Tanya Jawab</span></td>
                  <td style="width: 30%;">Hasil Pertanyaan Tertulis</td>
            </tr>
            <tr>
               <td style="width: 25%;"><span style="text-decoration: line-through;">Hasil Pertanyaan Lisan</span></td>
                  <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Pertanyaan Wawancara</span></td>
            </tr>
            <tr>
               <td style="width: 25%;"><span style="text-decoration: line-through;">Lainya...</span></td>
                  <td style="width: 30%;"></td>
            </tr>
            <tr>
                <td style="width: 100%;"><b>Asesi:</b><br>Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.
                </td>
            </tr>
            <tr>
                <td style="width: 100%;"><b>Asor:</b><br>Menytakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
                </td>
            </tr>
            <tr>
                <td style="width: 100%;"><b>Asesi:</b><br>Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya di gunakan untuk pengembangan profesional dan hanya dapat di akses oleh orang tertentu saja.
                </td>
            </tr>
            <tr>
                  <td style="width: 30%;">Tanda Tangan Asesi</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 65%; text-align: center;"><img style="width: 140px;" src="' . $qr_asesi . '" ></td>
                </tr>
            <tr>
                      <td style="width: 30%;">Tanggal</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 65%; text-align: center;">' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>
                </tr>
            <tr>
                  <td style="width: 30%;">Tanda Tangan Asesor</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 65%; text-align: center;"><img style="width: 140px;" src="' . $qr_asesor . '" ></td>
                </tr>
            <tr>
                      <td style="width: 30%;">Tanggal</td>
                  <td style="width: 5%; text-align:center;">:</td>
                  <td style="width: 65%; text-align: center;">' . date('d/m/Y', strtotime($dataAPL1['updated_at'])) . '</td>
                </tr>
            </table>
                ';

        $html .= '
                </body>
                </html>';


        $pdf->lastPage();

        $pdf->writeHTML($html, true, false, true, false, '');

        // Output the PDF as attachment to browser
        $this->response->setContentType('application/pdf');
        $pdf->Output('FR.AK.01. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');


        // END PDF PERSETJUAN ASESMEN
    }
}
// {
//     $dataAPL1 = $this->apl1->persetujuanAsesmen('FR-APL-01-bcfeee57');

//     $jenis_sertifikasi = '';

//     if ($dataAPL1['jenis_skema'] == "KKNI") {
//         $jenis_sertifikasi = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>';
//     } elseif ($dataAPL1['jenis_skema'] == "Okupasi") {
//         $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>';
//     } else {
//         $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster';
//     }

//     $jenis_tuk = '';

//     if ($dataAPL1['jenis_tuk'] == "Sewaktu") {
//         $jenis_tuk = 'Sewaktu / <span style="text-decoration: line-through;">Tempat Kerja</span> / <span style="text-decoration: line-through;">Mandiri</span>';
//     } elseif ($dataAPL1['jenis_skema'] == "Tempat Kerja") {
//         $jenis_tuk = '<span style="text-decoration: line-through;">Sewaktu/span> / Tempat Kerja</<span style="text-decoration: line-through;">Mandiri</span>';
//     } else {
//         $jenis_tuk = '<span style="text-decoration: line-through;">Sewaktu</span> / <span style="text-decoration: line-through;">Tempat Kerja</span> / Mandiri';
//     }

//     $writer = new PngWriter();

//     // Create QR code
//     $ttd_asesi = QrCode::create(base_url('/scan-tanda-tangan-asesi/' . $dataAPL1['tanda_tangan_asesi']))
//         ->setEncoding(new Encoding('UTF-8'))
//         ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
//         ->setSize(200)
//         ->setMargin(10)
//         ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
//         ->setForegroundColor(new Color(0, 0, 0))
//         ->setBackgroundColor(new Color(255, 255, 255));

//     $logo = Logo::create('logolsp.png')
//         ->setResizeToWidth(50);

//     // Create generic label
//     // $label = Label::create($dataAPL1['nama_siswa'])
//     //     ->setTextColor(new Color(000, 0, 0));

//     // $result = $writer->write($ttd_asesi, $logo, $label);
//     $result = $writer->write($ttd_asesi, $logo);

//     $qr_asesi = $result->getDataUri();

//     // Create a new PDF document
//     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

//     // Set document information
//     $pdf->SetCreator(PDF_CREATOR);
//     $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

//     // Set title based on user's full name
//     $pdf->SetTitle('FR.AK.01. ' . $dataAPL1['nama_siswa']);

//     // Set default header data
//     $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN', PDF_HEADER_STRING);

//     // Set header and footer fonts
//     $pdf->SetHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//     $pdf->SetFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

//     // Set default monospaced font
//     $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//     // Set margins
//     $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//     $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//     $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//     // Set auto page breaks
//     $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
//     // $pdf->SetAutoPageBreak(true, 5);

//     // Set image scale factor
//     $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//     // Set some language-dependent strings (optional)
//     if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
//         require_once(dirname(__FILE__) . '/lang/eng.php');
//         $pdf->setLanguageArray($l);
//     }

//     // Set font
//     $pdf->SetFont('helvetica', '', 10);

//     // Add a page
//     $pdf->AddPage();

//     $html = '
//             <!DOCTYPE html>
//             <html lang="en">
            
//             <head>
//                 <meta charset="UTF-8">
//                 <meta name="viewport" content="width=device-width, initial-scale=1.0">
//                 <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
//             </head>
//             <style>
//                 table, th, td {
//                     border: 1px solid black;
//                     border-collapse: collapse;
//                     padding: 3px;
//                 }
//             </style>
//             <body>
//             ';

//     $html .= '
//         <h4>FR.AK.01. PERSETUJUAN ASESMEN DAN KERAHASIAAN</h4>
//         <table>
//         <tr>
//             <td>Persetujuan Asesmen ini untuk menjamin bahwa asesi telah diberi arahan secara rinci tentang perencanaan dan proses asesmen</td>
//         </tr>
//        <tr>
//               <td style="width: 30%;" rowspan="2">Skema Sertifikasi
//            (' . $jenis_sertifikasi . ')   
//               </td>
//               <td style="width: 10%;">Judul</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
//             </tr>
//         <tr>
//             <td>Nomor</td>
//             <td style="text-align:center;">:</td>
//         </tr>
//        <tr>
//               <td style="width: 40%;">TUK</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 55%;">' . $jenis_tuk . '</td>
//             </tr>
//        <tr>
//               <td style="width: 40%;">Nama Asesor</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 55%;"></td>
//             </tr>
//        <tr>
//               <td style="width: 40%;">Nama Asesi</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 55%;">' . $dataAPL1['nama_siswa'] . '</td>
//             </tr>
//       <tr>
//               <td style="width: 30%;" rowspan="3">Pelaksanaan asesmen disepakati pada:</td>
//               <td style="width: 10%;">Tanggal</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 55%;">' . date('d/m/Y', strtotime($dataAPL1['tanggal'])) . '</td>
//             </tr>
//          <tr>
//             <td>Waktu</td>
//             <td style="text-align:center;">:</td>
//             <td>' . date('H:i', strtotime($dataAPL1['tanggal'])) . '</td>
//         </tr>
//         <tr>
//             <td>TUK</td>
//             <td style="text-align:center;">:</td>
//             <td>' . $dataAPL1['nama_tuk'] . '</td>
//         </tr>
//           <tr>
//               <td style="width:40%;" rowspan="5">Bukti yang akan dikumpulkan</td>
//               <td style="width: 5%; text-align:center;" rowspan="5">:</td>
//               <td style="width: 25%;"><span style="text-decoration: line-through;">Hasil Verifikasi Portofolio</span></td>
//               <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Reviu Produk</span></td>
//             </tr>

//         <tr>
//            <td style="width: 25%;"><span style="text-decoration: line-through;">Hasil Obeservasi Langsung</span></td>
//               <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Kegiatan Terstruktur</span></td>
//         </tr>
//         <tr>
//            <td style="width: 25%;"><span style="text-decoration: line-through;">Tanya Jawab</span></td>
//               <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Pertanyaan Tertulis</span></td>
//         </tr>
//         <tr>
//            <td style="width: 25%;"><span style="text-decoration: line-through;">Hasil Pertanyaan Lisan</span></td>
//               <td style="width: 30%;"><span style="text-decoration: line-through;">Hasil Pertanyaan Wawancara</span></td>
//         </tr>
//         <tr>
//            <td style="width: 25%;"><span style="text-decoration: line-through;">Lainya...</span></td>
//               <td style="width: 30%;"></td>
//         </tr>
//         <tr>
//             <td style="width: 100%;"><b>Asesi:</b><br>Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.
//             </td>
//         </tr>
//         <tr>
//             <td style="width: 100%;"><b>Asor:</b><br>Menytakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
//             </td>
//         </tr>
//         <tr>
//             <td style="width: 100%;"><b>Asesi:</b><br>Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya di gunakan untuk pengembangan profesional dan hanya dapat di akses oleh orang tertentu saja.
//             </td>
//         </tr>
//         <tr>
//               <td style="width: 30%;">Tanda Tangan Asesor</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 65%; text-align: center;"><img style="width: 140px;" src="' . $qr_asesi . '" ></td>
//             </tr>
//         <tr>
//                   <td style="width: 30%;">Tanggal</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 65%; text-align: center;">' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>
//             </tr>
//         <tr>
//               <td style="width: 30%;">Tanda Tangan Admin</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 65%; text-align: center;"><img style="width: 140px;" src="' . $qr_asesi . '" ></td>
//             </tr>
//         <tr>
//                   <td style="width: 30%;">Tanggal</td>
//               <td style="width: 5%; text-align:center;">:</td>
//               <td style="width: 65%; text-align: center;">' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>
//             </tr>
//         </table>
//             ';

//     $html .= '
//             </body>
//             </html>';


//     $pdf->lastPage();

//     $pdf->writeHTML($html, true, false, true, false, '');

//     // Output the PDF as attachment to browser
//     $this->response->setContentType('application/pdf');
//     $pdf->Output('FR.AK.01. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
// }
