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

class APL2Controller extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => "Asesmen Mandiri",
            'listAPL2' => $this->apl2->findAllAPL2(),
            'listEmailAPL2' => $this->apl2->getEmailValidasiToday(),
        ];
        // dd($data);
        return view('dashboard/kelola_apl2', $data);
    }

    public function asesmen($name)
    {
        $dataAPL1 = $this->apl1->getAPL1Name($name);
        $data = [
            'siteTitle' => "Asesmen Mandiri",
            'dataAPL1' => $dataAPL1,
            'listUnit' => $this->unit->getUnit($dataAPL1['id_skema']),
            'listElemen' => $this->elemen->AllElemen(),
            'listSubelemen' => $this->subelemen->getbySkema($dataAPL1['id_skema']),

        ];


        return view('dashboard/apl2', $data);
    }

    public function pdf($id_apl1)
    {
        $dataAPL1 = $this->apl2->getAllAsesmen($id_apl1);
        $listUnit = $this->unit->getUnit($dataAPL1['skema_id']);
        $listElemen = $this->elemen->AllElemen();
        $listAsesi = $this->apl2->getbySkema($dataAPL1['skema_id']);

        $status_apl1 = '';
        if ($dataAPL1['validasi_apl2'] == "validated") {
            $status_apl1 = '
                  Asesmen dapat dilanjutkan / <span style="text-decoration: line-through;">tidak dapat dilanjutkan</span>
                    ';
        } elseif ($dataAPL1['validasi_apl1'] == "unvalidated") {
            $status_apl1 = '
                   <span style="text-decoration: line-through;">Asesmen dapat dilanjutkan</span> / tidak dapat dilanjutkan
                    ';
        } else {
            $status_apl1 = '
                 Asesmen dapat dilanjutkan/tidak dapat dilanjutkan
                    ';
        }

        $nama_asesor = (isset($dataAPL1['validator_apl2'])) ? $dataAPL1['validator_apl2'] : '';

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

        $writer = new PngWriter();

        $result = $writer->write($ttd_asesi, $logo);

        $qr_asesi = $result->getDataUri();

        $ttd_asesor = QrCode::create(base_url('/scan-tanda-tangan-asesor/' . $dataAPL1['ttd_validator_apl2']))
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
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

        // Set title based on user's full name
        $pdf->SetTitle('FR.APL.02. ' . $dataAPL1['nama_siswa']);

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
            </head>
              <style>
            table,th, td {
                border: 1px solid black;
                border-collapse: collapse;
                padding:2px;
              }

            </style>
            <body>
            ';

        $html .= '
        <h4>FR.APL.02. ASESMEN MANDIRI</h4>

        <table class="table">
        <tr>
            <td rowspan="2" style="width: 25%;">Skema Sertifikasi
            (KKNI/Okupasi/Klaster)</td>  
            <td style="width: 10%;">Judul </td>
            <td style="width: 5%;">:</td>
            <td style="width: 60%;">' . $dataAPL1['nama_skema'] . '</td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td>:</td>
        </tr>
        </table>

        <p></p>
  
        <table>
        <tr>
            <td>PANDUAN ASESMEN MANDIRI</td>  
        </tr>
         <tr>
            <td>Instruksi:</td>
        </tr>
        <tr>
            <td>
            <ul>
            <li>Baca setiap pertanyaan di kolom sebelah kiri.</li>
            <li>Beri tanda centang pada kotak jika anda yakin dapat melakukan tugas yang dijelaskan.</li>
            <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunukan bahwa anda melakukan tugas-tugas ini.</li>
            </ul>
            </td>
        </tr>
        

        </table>

            ';
        foreach ($listUnit as $unit) {
            $html .= '
         <p></p>
        <table>
        <tr>
            <td rowspan="2" style="width: 25%;">Unit Kompetensi</td>  
            <td style="width: 10%;">Kode Unit</td>
            <td style="width: 5%;">:</td>
            <td style="width: 60%;">' . $unit['kode_unit'] . '</td>
        </tr>
        <tr>
            <td>Judul Unit</td>
            <td>:</td>
            <td>' . $unit['nama_unit'] .
                '</td>
        </tr>
        <tr>
            <td style="width: 60%;">Dapatkah Saya ...?</td>
            <td style="width:5%;">K</td>
            <td style="width: 5%;">BK</td>
            <td style="width: 30%;">Bukti Yang Relevan</td>
        </tr>
         ';
            $noElemen = 0;
            foreach ($listElemen as $elemen) {
                if ($elemen['id_unit'] == $unit['id_unit']) {
                    $noElemen++;

                    $html .= '
            <tr>
                <td><b>' . $noElemen . '. Elemen: ' . $elemen['nama_elemen'] . '</b></td>
                <td colspan="2"></td>
                <td></td>
            </tr>
         ';
                    $no = 0;
                    foreach ($listAsesi as $asesi) {
                        if ($asesi['id_apl1'] == $dataAPL1['id_apl1']) {
                            if ($asesi['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
                                $no++;

                                $html .=
                                    '
            <tr>
                <td>' . $noElemen . '.' . $no . '. ' . $asesi['pertanyaan'] . '</td>
                <td colspan="2" style="text-align: center;">' . $asesi['tk'] . '</td>
                <td>' . $asesi['bukti_pendukung'] . '</td>
            </tr>
         ';
                            }
                        }
                    }
                }
            }
            $html .=
                '
        </table>
        
         ';
        }

        $html .= '
        
            </body>
            </html>';


        // Output the HTML content to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->AddPage();

        $html2 = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
              <style>
            table,th, td {
                border: 1px solid black;
                border-collapse: collapse;
                padding:2px;
              }

            </style>
            <body>
            ';

        $html2 .= '
          <h4></h4>

                    <table>
                    <tr>
                      <th style="width: 50%;" rowspan="4">Rekomendasi: <br><i>' . $status_apl1 . '</i>
                      </th>
                      <th>
                      </th>
                    </tr>
                    <tr>
                      <th style="width: 20%;">Nama Asesi</th>
                      <th style="width: 30%; text-align: center;" >' . $dataAPL1['nama_siswa'] . '</th>
                    </tr>
                    <tr>
                      <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
                      <td style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_asesi . '" ></td>

                    </tr>
                    <tr style="text-align: center;">
                     <td>' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>

                    </tr>
                    <tr>
                      <th rowspan="4">Catatan :
                      </th>
                      <th colspan="2"><b>Ditijau oleh Asesor</b></th>
                    </tr>
                    <tr>
                      <th style="width: 20%;">Nama Asesor</th>
                      <th style="width: 30%; text-align: center;" >' . $nama_asesor . '</th>
                    </tr>
                    <tr>
                      <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
                     <th style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_asesor . '" ></th>
                    </tr>
                     <tr style="text-align: center;">
                     <td>' . date('d/m/Y', strtotime($dataAPL1['updated_at'])) . '</td>

                    </tr>
                    </table>
        ';

        $html2 .= '
        
            </body>
            </html>';


        $pdf->lastPage();

        // Output the HTML content to the PDF
        $pdf->writeHTML($html2, true, false, true, false, '');

        // Output the PDF as attachment to browser
        $this->response->setContentType('application/pdf');
        $pdf->Output('FR.APL.02. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
    }

    public function validasi()
    {
        $data = [
            'siteTitle' => 'Validasi FR.APL.02',
            'listAPL2Pending' => $this->apl2->getPendingData2(),
        ];
        // dd($data);
        return view('dashboard/validasi_apl2', $data);
    }

    public function store_validasi()
    {
        $rules = [
            'validasi_apl2' => [
                'label' => 'Validasi',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'validasi_apl2' => $this->request->getVar('validasi_apl2'),
            'validator' => $this->request->getVar('id_asesor'),
        ];

        $this->apl2->update($this->request->getVar('id'), $data);

        session()->setFlashdata('pesan', 'Form APL 2 berhasil divalidasi!');
        return redirect()->to('kelola_apl2/validasi');
    }

    public function send_email_validasi()
    {

        $listAPL2Validated = $this->apl2->getEmailValidasiToday();


        foreach ($listAPL2Validated as $row) {

            $subject = 'Validasi Asesmen Mandiri';

            $to = $row['email'];

            $nama_asesi = $row['nama_siswa'];

            $id_apl1 = $row['id_apl1'];

            $skema = $row['nama_skema'];

            if ($row['validasi_apl2'] == "validated") {
                // HTML message
                $message = view('email/email_validated_apl2', [
                    'dataAPL1' => $this->apl2->emailDetailSertifikasi($row['id_apl1']),
                ]);
            } else {
                $message = view('email/email_unvalidated_apl2', [
                    'alasan_penolakan' => '',
                    'email_kontak' => 'lspp1smkn2kuningan@gmail.com',
                    'telepon_kontak' => '0812345678'
                ]);
            }

            $email = \Config\Services::email();
            $email->setTo($to);
            $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

            $email->setSubject($subject);
            $email->setMessage($message);

            // Set mail type to HTML
            $email->setMailType('html');

            if ($email->send()) {

                $data = [
                    'email_validasi' => '1',
                ];

                if ($this->apl2->update($row['id_apl2'], $data)) {

                    if ($row['validasi_apl2'] == "validated") {

                        $subject = 'Informasi Jadwal Sertfikasi';

                        // HTML message
                        $message = view('email/email_info_sertifikasi', [
                            'dataAPL1' => $this->apl2->emailDetailSertifikasi($row['id_apl1']),
                        ]);


                        $email = \Config\Services::email();
                        $email->setTo($to);
                        $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

                        $email->setSubject($subject);
                        $email->setMessage($message);

                        // Set mail type to HTML
                        $email->setMailType('html');

                        if (!$email->send()) {
                            $data = $email->printDebugger(['headers']);
                            print_r($data);
                        }
                    }
                }
            } else {
                $data = $email->printDebugger(['headers']);
                print_r($data);
            }
        }
        session()->setFlashdata('pesan', 'Email Validasi Form APL 2 berhasil terkirim');
        return redirect()->to('kelola_apl2');
    }

    public function send_email_validasi_by_date()
    {
        $rules = [
            'dateValidated' => [
                'label' => 'Tanggal Validasi',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $listAPL2Validated = $this->apl2->getEmailValidasiByDate($this->request->getVar('dateValidated'));

        if ($listAPL2Validated == null) {
            session()->setFlashdata('warning', 'Data Validasi Pada tanggal ' . $this->request->getVar('dateValidated') . ' Kosong.');
            return redirect()->back()->withInput();
        }

        $subject = 'Validasi Asesmen Mandiri';

        foreach ($listAPL2Validated as $row) {

            $to = $row['email'];

            $nama_asesi = $row['nama_siswa'];

            $id_apl1 = $row['id_apl1'];

            $skema = $row['nama_skema'];

            if ($row['validasi_apl2'] == "validated") {
                // HTML message
                $message = view('email/email_validated_apl2', [
                    'dataAPL1' => $this->apl2->emailDetailSertifikasi($row['id_apl1']),
                ]);
            } else {
                $message = view('email/email_unvalidated_apl2', [
                    'alasan_penolakan' => '',
                    'email_kontak' => 'lspp1smkn2kuningan@gmail.com',
                    'telepon_kontak' => '0812345678'
                ]);
            }

            $email = \Config\Services::email();
            $email->setTo($to);
            $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

            $email->setSubject($subject);
            $email->setMessage($message);

            // Set mail type to HTML
            $email->setMailType('html');

            if ($email->send()) {

                $data = [
                    'email_validasi' => '1',
                ];

                $this->apl2->update($row['id_apl2'], $data);
            } else {
                $data = $email->printDebugger(['headers']);
                print_r($data);
            }
        }
        session()->setFlashdata('pesan', 'Email Validasi Form APL 2 berhasil terkirim');
        return redirect()->to('kelola_apl2');
    }

    public function getDateValidated()
    {
        $dataAPL2 = $this->apl2->getEmailValidasiByDate($this->request->getVar('dateValidated'));
        $no = null;
        $output = '';

        foreach ($dataAPL2 as $row) {
            $no++;
            $badgeColor = '';

            switch ($row['validasi_apl2']) {
                case 'validated':
                    $badgeColor = 'success';
                    break;
                case 'pending':
                    $badgeColor = 'warning';
                    break;
                default:
                    $badgeColor = 'danger';
            }

            // Memperbarui output HTML
            $output .= "
            <tr>
                <td>" . $no . "</td>
                <td>" . $row['id_apl1'] . "</td>
                <td>" . $row['id_apl2'] . "</td>
                <td>" . $row['nama_siswa'] . "</td>
                <td>" . $row['nama_skema'] . "</td>
                <td>
                    <div class='badge badge-" . $badgeColor . "'>" . $row['validasi_apl2'] . "</div>
                </td>
            </tr>";
        }

        echo $output;
    }

    public function scan_ttd_asesor($ttd)
    {
        return view('scan/scan-ttd-asesor', [
            'data' => $this->apl2->getbyttdAsesor($ttd),
            'siteTitle' => 'Scan Tanda Tangan'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->apl2->delete($id);
        session()->setFlashdata('pesan', 'FR.APL.02 berhasil dihapus!');
        return redirect()->to('/kelola_apl2');
    }
}

// $to = $this->request->getVar('email');
// $subject = 'Validasi Asesmen Mandiri';

// if ($this->request->getVar('validasi_apl2') == "validated") {
//     // HTML message
//     $message = view('email/email_validated_apl2', [
//         'dataAPL1' => $this->apl2->emailDetailSertifikasi($this->request->getVar('id_apl1')),
//     ]);
// } else {
//     $message = view('email/email_unvalidated_apl2', [

//         'alasan_penolakan' => '',
//         'email_kontak' => 'lspp1smkn2kuningan@gmail.com',
//         'telepon_kontak' => '0812345678'
//     ]);
// }

// $email = \Config\Services::email();
// $email->setTo($to);
// $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

// $email->setSubject($subject);
// $email->setMessage($message);

// // Set mail type to HTML
// $email->setMailType('html');

// // $email->attach($pdfFileAPL1);
// // $email->attach($pdfFileAPL2);
// // $email->attach($pdfFilePersetujuan);

// if (!$email->send()) {
//     $data = $email->printDebugger(['headers']);
//     print_r($data);
// }


    // {

    //     // APL1 PDF

    //     $dataAPL1 = $this->apl1->getAPL1byid($this->request->getVar('id_apl1'));

    //     $listUnit = $this->unit->getUnit($dataAPL1['skema_id']);

    //     $jenis_kelamin = ($dataAPL1['jenis_kelamin'] == "Laki-Laki") ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>' : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';

    //     $jenis_sertifikasi = '';

    //     if ($dataAPL1['jenis_skema'] == "KKNI") {
    //         $jenis_sertifikasi = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>';
    //     } elseif ($dataAPL1['jenis_skema'] == "Okupasi") {
    //         $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>';
    //     } else {
    //         $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster';
    //     }

    //     $tujuan = '';
    //     if ($dataAPL1['tujuan'] == "Sertifikasi") {
    //         $tujuan = '
    //                  <tr>
    //                   <td colspan="2" rowspan="4">Tujuan Asesmen</td>
    //                   <td>:</td>
    //                   <td>Sertifikasi</td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Lainya</span></td>
    //                 </tr>
    //                 ';
    //     } elseif ($dataAPL1['tujuan'] == "PKT") {
    //         $tujuan = '
    //                  <tr>
    //                   <td colspan="2" rowspan="4">Tujuan Asesmen</td>
    //                   <td>:</td>
    //                   <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td>Pengakuan Kompetensi Lampau (PKT)</td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Lainya</span></td>
    //                 </tr>
    //                 ';
    //     } elseif ($dataAPL1['tujuan'] == "RPL") {
    //         $tujuan = '
    //                  <tr>
    //                   <td colspan="2" rowspan="4">Tujuan Asesmen</td>
    //                   <td>:</td>
    //                   <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td>Rekognisi Pembelajaran Lampau (RPL)</td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Lainya</span></td>
    //                 </tr>
    //                 ';
    //     } else {
    //         $tujuan = '
    //                  <tr>
    //                   <td colspan="2" rowspan="4">Tujuan Asesmen</td>
    //                   <td>:</td>
    //                   <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
    //                 </tr>
    //                 <tr>
    //                 <td></td>
    //                 <td>Lainya</td>
    //                 </tr>
    //                 ';
    //     }

    //     $bukti_dasar = '';
    //     if (isset($dataAPL1['pas_foto'])) {
    //         $bukti_dasar = '
    //                 <tr>
    //                     <td>2.</td>
    //                     <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
    //                     <td style="text-align: center;">Ada</td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>
    //                 ';
    //     } else {
    //         $bukti_dasar = '
    //                 <tr>
    //                     <td>2.</td>
    //                     <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
    //                     <td style="text-align: center;"></td>
    //                     <td></td>
    //                     <td>Tidak Ada</td>
    //                 </tr>
    //                 ';
    //     }

    //     $status_apl1 = '';
    //     if ($dataAPL1['validasi_apl1'] == "validated") {
    //         $status_apl1 = '
    //                Diterima / <span style="text-decoration: line-through;">Tidak Diterima</span>
    //                 ';
    //     } elseif ($dataAPL1['validasi_apl1'] == "unvalidated") {
    //         $status_apl1 = '
    //                <span style="text-decoration: line-through;">Diterima</span> / Tidak Diterima
    //                 ';
    //     } else {
    //         $status_apl1 = '
    //                Diterima / Tidak Diterima
    //                 ';
    //     }

    //     $nama_admin = (isset($dataAPL1['validator_apl1'])) ? $dataAPL1['validator_apl1'] : '';

    //     $tanda_tangan_admin = (isset($dataAPL1['ttd_validator_apl1'])) ?  $dataAPL1['ttd_validator_apl1'] : '';

    //     // Create a new PDF document
    //     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

    //     // Set document information
    //     $pdf->SetCreator(PDF_CREATOR);
    //     $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

    //     // Set title based on user's full name
    //     $pdf->SetTitle('FR.APL.01. ' . $dataAPL1['nama_siswa']);

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
    //                 <!DOCTYPE html>
    //                 <html lang="en">

    //                 <head>
    //                     <meta charset="UTF-8">
    //                     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //                     <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
    //                 </head>
    //                 <body>
    //                 ';

    //     $html .= '


    //                 <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
    //                 <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
    //                 <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>
    //                 <ol type="a">
    //                 <li>
    //                 <table>  
    //                 <tr>
    //                 <th>Data Pribadi</th>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Nama Lengkap</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_siswa'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >No. KTP/NIK/Paspor</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nik'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Tempat / Tanggal Lahir</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['tempat_lahir'] . ', ' .  $dataAPL1['tanggal_lahir'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Jenis Kelamin</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; ">' .  $jenis_kelamin . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Kebangsaan</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['kebangsaan'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Alamat Rumah</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">Kel. ' .  $dataAPL1['nama_kelurahan'] . ', Kec. ' .  $dataAPL1['nama_kecamatan'] . ', ' .  $dataAPL1['nama_kabupaten'] . ', Prov. ' .  $dataAPL1['nama_provinsi'] . ', Kode Pos :' .  $dataAPL1['kode_pos'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" ></th>
    //                 <td style="width: 5%;"></td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">HP: ' .  $dataAPL1['no_hp'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >No Telepon/Email</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">Rumah: ' .  $dataAPL1['telpon_rumah'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" ></th>
    //                 <td style="width: 5%;"></td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Pendidikan Terakhir</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['pendidikan_terakhir'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Nama Sekolah/Universitas</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_sekolah'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Jurusan</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jurusan'] . '</td>
    //                 </tr>
    //                 </table>
    //                 </li>
    //                 <li>
    //                 <table>  
    //                 <tr>
    //                 <th>Data Pekerjaan Sekarang</th>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Nama Institusi/Perusahaan</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_lembaga'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Jabatan</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jabatan'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >Alamat Perusahaan</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['alamat_perusahaan'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" >No Telepon/Email</th>
    //                 <td style="width: 5%;">:</td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">Telp: ' .  $dataAPL1['no_telp_perusahaan'] . '</td>
    //                 </tr>
    //                 <tr><td></td></tr>
    //                 <tr>
    //                 <th style="width: 30%;" ></th>
    //                 <td style="width: 5%;"></td>
    //                 <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email_perusahaan'] . '</td>
    //                 </tr>
    //                 </table>
    //                 </li>
    //                 </ol>
    //                 ';

    //     $html .= '
    //                 </body>
    //                 </html>';

    //     // Output the HTML content to the PDF
    //     $pdf->writeHTML($html, true, false, true, false, '');

    //     $pdf->AddPage();

    //     $html2 = '
    //                 <!DOCTYPE html>
    //                 <html lang="en">

    //                 <head>
    //                     <meta charset="UTF-8">
    //                     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //                     <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
    //                 <style>
    //                 table, th, td {
    //                     border: 1px solid black;
    //                     border-collapse: collapse;
    //                padding: 3px;
    //                 }
    //                 </style>
    //                 <body>
    //                 ';

    //     $html2 .= '

    //                 <h4>Bagian 2 : Data Sertifikasi</h4>
    //                 <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>

    //                 <table>
    //                 <tr>
    //                   <td style="width: 30%;" rowspan="2">
    //                   Skema Sertifikasi<br>
    //                ' . $jenis_sertifikasi . '   
    //                   </td>
    //                   <td style="width: 10%;">Judul</td>
    //                   <td style="width: 5%;">:</td>
    //                   <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
    //                 </tr>
    //                 <tr>
    //                   <td>Nomor</td>
    //                   <td>:</td>
    //                   <td></td>
    //                 </tr>
    //                ' . $tujuan . '
    //               </table>

    //               <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
    //               <table>
    //               <tr>
    //                 <th style="width: 5%;">No.</th>
    //                 <th style="width: 25%;">Kode Unit</th>
    //                 <th style="width: 45%;">Judul Unit</th>
    //                 <th style="width: 25%;">Standar Kompetensi Kerja</th>
    //               </tr>
    //               ';

    //     $no = 0;
    //     foreach ($listUnit as $key => $value) {
    //         $no++;

    //         $html2 .= '

    //                     <tr>
    //                       <td>' . $no . '.</td>
    //                       <td>' . $value['kode_unit'] . '</td>
    //                       <td>' . $value['nama_unit'] . '</td>
    //                       <td style="text-align: center;">SKKNI</td>
    //                     </tr>

    //                    ';
    //     }

    //     $html2 .= '
    //                 </table>

    //                 </body>
    //                 </html>';

    //     // Output the HTML content to the PDF
    //     $pdf->writeHTML($html2, true, false, true, false, '');

    //     $pdf->AddPage();


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


    //     $result = $writer->write($ttd_asesi, $logo);

    //     $qr_asesi = $result->getDataUri();

    //     $ttd_admin = QrCode::create(base_url('/scan-tanda-tangan-admin/' . $tanda_tangan_admin))
    //         ->setEncoding(new Encoding('UTF-8'))
    //         ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    //         ->setSize(200)
    //         ->setMargin(10)
    //         ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
    //         ->setForegroundColor(new Color(0, 0, 0))
    //         ->setBackgroundColor(new Color(255, 255, 255));



    //     $result = $writer->write($ttd_admin, $logo);

    //     $qr_admin = $result->getDataUri();

    //     $html3 = '
    //                 <!DOCTYPE html>
    //                 <html lang="en">

    //                 <head>
    //                     <meta charset="UTF-8">
    //                     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //                  <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
    //                 <style>
    //                 table, th, td {
    //                     border: 1px solid black;
    //                     border-collapse: collapse;
    //               padding: 2.5px;
    //                 }
    //                 </style>
    //                 <body>
    //                 ';

    //     $html3 .= '
    //                 <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
    //                 <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>

    //                 <table>
    //                 <tr style="text-align: center;">
    //                     <th style="width: 5%;" rowspan="2">No.</th>
    //                     <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
    //                     <th style="width: 30%;" colspan="2">Ada</th>
    //                     <th style="width: 15%;" rowspan="2">Tidak Ada</th>
    //                 </tr>
    //                 <tr style="text-align: center;">
    //                     <th style="width: 15%;">Memenuhi Syarat</th>
    //                     <th style="width: 15%;">Tidak Memenuhi Syarat</th>
    //                 </tr>
    //                 <tr>
    //                     <td>1.</td>
    //                     <td>Fotocopy Kartu Keluarga</td>
    //                     <td style="text-align: center;">Ada</td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>
    //                ' . $bukti_dasar . '
    //                 <tr>
    //                     <td>3.</td>
    //                     <td></td>
    //                     <td></td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>
    //                 </table>

    //                 <h4>3.2 Bukti Administratif</h4>

    //                 <table>
    //                 <tr style="text-align: center;">
    //                     <th style="width: 5%;"  rowspan="2">No.</th>
    //                     <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
    //                     <th style="width: 30%;" colspan="2">Ada</th>
    //                     <th style="width: 15%;" rowspan="2">Tidak Ada</th>
    //                 </tr>
    //                 <tr style="text-align: center;">
    //                     <th>Memenuhi Syarat</th>
    //                     <th>Tidak Memenuhi Syarat</th>
    //                 </tr>
    //                 <tr >
    //                     <td>1.</td>
    //                     <td>Fotocopy Raport</td>
    //                     <td style="text-align: center;">Ada</td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>
    //                 <tr>
    //                     <td>2.</td>
    //                     <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
    //                     <td style="text-align: center;">Ada</td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>
    //                 <tr>
    //                     <td>3.</td>
    //                     <td>Fotocopy Kartu Pelajar</td>
    //                     <td style="text-align: center;">Ada</td>
    //                     <td></td>
    //                     <td></td>
    //                 </tr>

    //                 </table>

    //                 <h4></h4>

    //                 <table>
    //                 <tr>
    //                   <th style="width: 50%;" rowspan="4">Rekomendasi (DIisi Oleh LSP): <br>Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon<i>' . $status_apl1 . '</i> *) Sebagai Peserta Sertifikasi <br>*coret yang tidak perlu
    //                   </th>
    //                   <th style="width: 50%;" colspan="2">Pemohon/Kandidat</th>
    //                 </tr>
    //                 <tr>
    //                   <th style="width: 20%;">Nama</th>
    //                   <th style="width: 30%; text-align: center;" >' . $dataAPL1['nama_siswa'] . '</th>
    //                 </tr>
    //                 <tr>
    //                   <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
    //                   <td style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_asesi . '" ></td>

    //                 </tr>
    //                 <tr style="text-align: center;">
    //                  <td>' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>

    //                 </tr>
    //                 <tr>
    //                   <th rowspan="4">
    //                   Catatan :
    //                   </th>
    //                   <th colspan="2">Admin LSP</th>
    //                 </tr>
    //                 <tr>
    //                   <th style="width: 20%;">Nama</th>
    //                   <th style="width: 30%; text-align: center;" >' . $nama_admin . '</th>
    //                 </tr>
    //                 <tr>
    //                   <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
    //                  <th style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_admin . '" ></th>
    //                 </tr>
    //                  <tr style="text-align: center;">
    //                  <td>' . date('d/m/Y', strtotime($dataAPL1['updated_at'])) . '</td>

    //                 </tr>
    //                 </table>

    //                ';


    //     $html3 .= '
    //                 </body>
    //                 </html>';


    //     $pdf->lastPage();

    //     $pdf->writeHTML($html3, true, false, true, false, '');

    //     // Output the PDF as attachment to browser
    //     $pdfFileAPL1 =  FCPATH . 'pdf/FR.APL.01.' . $dataAPL1['nama_siswa'] . '.pdf'; // Change this path as needed
    //     $pdf->Output($pdfFileAPL1, 'F');

    //     // End APL1 PDF

    //     // APL2 PDF

    //     $dataAPL1 = $this->apl1->getAPL1byid($this->request->getVar('id_apl1'));

    //     $listUnit = $this->unit->getUnit($dataAPL1['skema_id']);
    //     $listElemen = $this->elemen->AllElemen();
    //     $listAsesi = $this->apl2->getbySkema($dataAPL1['skema_id']);

    //     $status_apl1 = '';
    //     if ($dataAPL1['validasi_apl2'] == "validated") {
    //         $status_apl1 = '
    //               Asesmen dapat dilanjutkan / <span style="text-decoration: line-through;">tidak dapat dilanjutkan</span>
    //                 ';
    //     } elseif ($dataAPL1['validasi_apl1'] == "unvalidated") {
    //         $status_apl1 = '
    //                <span style="text-decoration: line-through;">Asesmen dapat dilanjutkan</span> / tidak dapat dilanjutkan
    //                 ';
    //     } else {
    //         $status_apl1 = '
    //              Asesmen dapat dilanjutkan/tidak dapat dilanjutkan
    //                 ';
    //     }

    //     $nama_asesor = (isset($dataAPL1['validator_apl2'])) ? $dataAPL1['validator_apl2'] : '';

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

    //     $writer = new PngWriter();

    //     $result = $writer->write($ttd_asesi, $logo);

    //     $qr_asesi = $result->getDataUri();

    //     $ttd_asesor = QrCode::create(base_url('/scan-tanda-tangan-asesor/' . $dataAPL1['ttd_validator_apl2']))
    //         ->setEncoding(new Encoding('UTF-8'))
    //         ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
    //         ->setSize(200)
    //         ->setMargin(10)
    //         ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
    //         ->setForegroundColor(new Color(0, 0, 0))
    //         ->setBackgroundColor(new Color(255, 255, 255));



    //     $result = $writer->write($ttd_asesor, $logo);
    //     $qr_asesor = $result->getDataUri();

    //     // Create a new PDF document
    //     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

    //     // Set document information
    //     $pdf->SetCreator(PDF_CREATOR);
    //     $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

    //     // Set title based on user's full name
    //     $pdf->SetTitle('FR.APL.02. ' . $dataAPL1['nama_siswa']);

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
    //         <!DOCTYPE html>
    //         <html lang="en">

    //         <head>
    //             <meta charset="UTF-8">
    //             <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //         </head>
    //           <style>
    //         table,th, td {
    //             border: 1px solid black;
    //             border-collapse: collapse;
    //             padding:2px;
    //           }

    //         </style>
    //         <body>
    //         ';

    //     $html .= '
    //     <h4>FR.APL.02. ASESMEN MANDIRI</h4>

    //     <table>
    //     <tr>
    //         <td rowspan="2" style="width: 25%;">Skema Sertifikasi
    //         (<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>)</td>  
    //         <td style="width: 10%;">Judul </td>
    //         <td style="width: 5%;">:</td>
    //         <td style="width: 60%;">' . $dataAPL1['nama_skema'] . '</td>
    //     </tr>
    //     <tr>
    //         <td>Nomor</td>
    //         <td>:</td>
    //     </tr>

    //     </table>

    //     <h4></h4>

    //     <table>
    //     <tr>
    //         <td>PANDUAN ASESMEN MANDIRI</td>  
    //     </tr>
    //      <tr>
    //         <td>Instruksi:</td>
    //     </tr>
    //     <tr>
    //         <td>
    //         <ul>
    //         <li>Baca setiap pertanyaan di kolom sebelah kiri.</li>
    //         <li>Beri tanda centang pada kotak jika anda yakin dapat melakukan tugas yang dijelaskan.</li>
    //         <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunukan bahwa anda melakukan tugas-tugas ini.</li>
    //         </ul>
    //         </td>
    //     </tr>


    //     </table>

    //         ';
    //     foreach ($listUnit as $unit) {
    //         $html .= '
    //     <h4></h4>
    //     <table>
    //     <tr>
    //         <td rowspan="2" style="width: 25%;">Unit Kompetensi</td>  
    //         <td style="width: 10%;">Kode Unit</td>
    //         <td style="width: 5%;">:</td>
    //         <td style="width: 60%;">' . $unit['kode_unit'] . '</td>
    //     </tr>
    //     <tr>
    //         <td>Judul Unit</td>
    //         <td>:</td>
    //         <td>' . $unit['nama_unit'] .
    //             '</td>
    //     </tr>
    //     <tr>
    //         <td style="width: 60%;">Dapatkah Saya ...?</td>
    //         <td style="width:5%;">K</td>
    //         <td style="width: 5%;">BK</td>
    //         <td style="width: 30%;">Bukti Yang Relevan</td>
    //     </tr>
    //      ';
    //         $noElemen = 0;
    //         foreach ($listElemen as $elemen) {
    //             if ($elemen['id_unit'] == $unit['id_unit']) {
    //                 $noElemen++;

    //                 $html .= '
    //         <tr>
    //             <td><b>' . $noElemen . '. Elemen: ' . $elemen['nama_elemen'] . '</b></td>
    //             <td colspan="2"></td>
    //             <td></td>
    //         </tr>
    //      ';
    //                 $no = 0;
    //                 foreach ($listAsesi as $asesi) {
    //                     if ($asesi['id_apl1'] == $dataAPL1['id_apl1']) {
    //                         if ($asesi['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
    //                             $no++;

    //                             $html .=
    //                                 '
    //         <tr>
    //             <td>' . $noElemen . '.' . $no . '. ' . $asesi['pertanyaan'] . '</td>
    //             <td colspan="2" style="text-align: center;">' . $asesi['tk'] . '</td>
    //             <td>' . $asesi['bukti_pendukung'] . '</td>
    //         </tr>
    //      ';
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         $html .=
    //             '
    //     </table>

    //      ';
    //     }
    //     $html .= '

    //         </body>
    //         </html>';


    //     // Output the HTML content to the PDF
    //     $pdf->writeHTML(
    //         $html,
    //         true,
    //         false,
    //         true,
    //         false,
    //         ''
    //     );

    //     $pdf->AddPage();

    //     $html2 = '
    //         <!DOCTYPE html>
    //         <html lang="en">

    //         <head>
    //             <meta charset="UTF-8">
    //             <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //         </head>
    //           <style>
    //         table,th, td {
    //             border: 1px solid black;
    //             border-collapse: collapse;
    //             padding:2px;
    //           }

    //         </style>
    //         <body>
    //         ';

    //     $html2 .= '
    //       <h4></h4>

    //                 <table>
    //                 <tr>
    //                   <th style="width: 50%;" rowspan="4">Rekomendasi: <br><i>' . $status_apl1 . '</i>
    //                   </th>
    //                   <th>
    //                   </th>
    //                 </tr>
    //                 <tr>
    //                   <th style="width: 20%;">Nama Asesi</th>
    //                   <th style="width: 30%; text-align: center;" >' . $dataAPL1['nama_siswa'] . '</th>
    //                 </tr>
    //                 <tr>
    //                   <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
    //                   <td style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_asesi . '" ></td>

    //                 </tr>
    //                 <tr style="text-align: center;">
    //                  <td>' . date('d/m/Y', strtotime($dataAPL1['created_at'])) . '</td>

    //                 </tr>
    //                 <tr>
    //                   <th rowspan="4">Catatan :
    //                   </th>
    //                   <th colspan="2"><b>Ditijau oleh Asesor</b></th>
    //                 </tr>
    //                 <tr>
    //                   <th style="width: 20%;">Nama Asesor</th>
    //                   <th style="width: 30%; text-align: center;" >' . $nama_asesor . '</th>
    //                 </tr>
    //                 <tr>
    //                   <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
    //                  <th style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_asesor . '" ></th>
    //                 </tr>
    //                  <tr style="text-align: center;">
    //                  <td>' . date('d/m/Y', strtotime($dataAPL1['updated_at'])) . '</td>

    //                 </tr>
    //                 </table>
    //     ';

    //     $html2 .= '

    //         </body>
    //         </html>';


    //     $pdf->lastPage();

    //     // Output the HTML content to the PDF
    //     $pdf->writeHTML($html2, true, false, true, false, '');

    //     // Output the PDF as attachment to browser
    //     $pdfFileAPL2 =  FCPATH . 'pdf/FR.APL.02.' . $dataAPL1['nama_siswa'] . '.pdf'; // Change this path as needed
    //     $pdf->Output($pdfFileAPL2, 'F');

    //     // END APL 2 PDF



    //     $rules = [

    //         'validasi_apl2' => [
    //             'label' => 'Validasi',
    //             'rules' => 'required',
    //             'errors' => [
    //                 'required' => 'Kolom {field} harus diisi.',
    //             ],
    //         ],

    //     ];

    //     if (!$this->validate($rules)) {
    //         return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    //     }
    //     $data = [
    //         'validasi_apl2' => $this->request->getVar('validasi_apl2'),
    //         'validator' => $this->request->getVar('id_asesor'),
    //     ];

    //     $validated = $this->apl2->update($this->request->getVar('id'), $data);

    //     if ($validated) {
    //         $to = $this->request->getVar('email');
    //         $subject = 'Validasi Asesmen Mandiri';

    //         if ($this->request->getVar('validasi_apl2') == "validated") {
    //             // HTML message
    //             $message = view('email/email_validated_apl2', [
    //                 'dataAPL1' => $this->apl2->emailDetailSertifikasi($this->request->getVar('id_apl1')),
    //             ]);
    //         } else {
    //             $message = view('email/email_unvalidated_apl2', [

    //                 'alasan_penolakan' => '',
    //                 'email_kontak' => 'lspp1smkn2kuningan@gmail.com',
    //                 'telepon_kontak' => '0812345678'
    //             ]);
    //         }

    //         $email = \Config\Services::email();
    //         $email->setTo($to);
    //         $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');

    //         $email->setSubject($subject);
    //         $email->setMessage($message);

    //         // Set mail type to HTML
    //         $email->setMailType('html');

    //         $email->attach($pdfFileAPL1);
    //         $email->attach($pdfFileAPL2);
    //         $email->attach($pdfFilePersetujuan);

    //         if ($email->send()) {
    //             echo 'Email successfully sent';
    //         } else {
    //             $data = $email->printDebugger(['headers']);
    //             print_r($data);
    //         }
    //     } else {
    //         echo 'Validasi Gagal';
    //     }



    //     session()->setFlashdata('pesan', 'Form APL 2 berhasil divalidasi!');
    //     return redirect()->to('apl2/validasi');
    // }
