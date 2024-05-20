<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use TCPDF;

class TesController extends BaseController
{
  public function index()
  {
    $to = 'haikalcode08@gmail.com';
    $subject = 'Pendaftaran Uji Kompetensi Keahlian';

    // HTML message
    $message = view('email/email_validasi_apl1', [
      'name' => 'haikal'
    ]);

    $email = \Config\Services::email();
    $email->setTo($to);
    $email->setFrom('haikal1080p@gmail.com', 'LSP SMK NEGERI 2 KUNINGAN');

    $email->setSubject($subject);
    $email->setMessage($message);

    // Set mail type to HTML
    $email->setMailType('html');

    if ($email->send()) {
      echo 'Email successfully sent';
    } else {
      $data = $email->printDebugger(['headers']);
      print_r($data);
    }
  }


  // public function index()
  // {
  //     // Create a new PDF document
  //     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

  //     // Set document information
  //     $pdf->SetCreator(PDF_CREATOR);
  //     $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

  //     // Set title based on user's full name
  //     $pdf->SetTitle('LEMBAGA SERTIFIKASI PROFESI - P1 SMK NEGERI 2 KUNINGAN');

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

  //     // // set cell padding
  //     // $pdf->setCellPaddings(1, 1, 1, 1);

  //     // // set cell margins
  //     // $pdf->setCellMargins(1, 1, 1, 1);

  //     // Add title
  //     // $pdf->SetFont('helvetica', 'B', 10);
  //     // $pdf->Cell(0, 10, 'FR.APL.01. PERMOHONAN SERTIFIKASI KOMPETENSI', 0, 1, 'L');

  //     // // Add subtitle
  //     // $pdf->SetFont('helvetica', 'B', 10);
  //     // $pdf->Cell(0, 10, 'Bagian 1 : Rincian Data Pemohon Sertifikasi', 0, 1, 'L');

  //     // // Add description
  //     // $pdf->SetFont('helvetica', '', 10);
  //     // $pdf->MultiCell(0, 10, 'Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.', 0, 'L');

  //     $dataAPL1 = $this->apl1->getAllAPL1(user()->id);
  //     $listUnit = $this->unit->getUnit($dataAPL1['id_skema']);

  //     $jenis_kelamin = ($dataAPL1['jenis_kelamin'] == "Laki-Laki") ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>' : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';

  //     $html = '
  //     <!DOCTYPE html>
  //     <html lang="en">

  //     <head>
  //         <meta charset="UTF-8">
  //         <meta name="viewport" content="width=device-width, initial-scale=1.0">
  //     </head>

  //     <body>
  //     ';

  //     $html .= '


  //     <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
  //     <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
  //     <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>
  //     <ol type="a">
  //     <li>
  //     <table>  
  //     <tr>
  //     <th>Data Pribadi</th>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Nama Lengkap</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_siswa'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >No. KTP/NIK/Paspor</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nik'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Tempat / Tanggal Lahir</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['tempat_lahir'] . ', ' .  $dataAPL1['tanggal_lahir'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Jenis Kelamin</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; ">' .  $jenis_kelamin . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Kebangsaan</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['kebangsaan'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Alamat Rumah</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">Kel. ' .  $dataAPL1['nama_kelurahan'] . ', Kec. ' .  $dataAPL1['nama_kecamatan'] . ', ' .  $dataAPL1['nama_kabupaten'] . ', Prov. ' .  $dataAPL1['nama_provinsi'] . ', Kode Pos :' .  $dataAPL1['kode_pos'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" ></th>
  //     <td style="width: 5%;"></td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">HP: ' .  $dataAPL1['no_hp'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >No Telepon/Email</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">Rumah: ' .  $dataAPL1['telpon_rumah'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" ></th>
  //     <td style="width: 5%;"></td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Pendidikan Terakhir</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['pendidikan_terakhir'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Nama Sekolah/Universitas</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_sekolah'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Jurusan</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jurusan'] . '</td>
  //     </tr>
  //     </table>
  //     </li>
  //     <li>
  //     <table>  
  //     <tr>
  //     <th>Data Pekerjaan Sekarang</th>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Nama Institusi/Perusahaan</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_lembaga'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Jabatan</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jabatan'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >Alamat Perusahaan</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['alamat_perusahaan'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" >No Telepon/Email</th>
  //     <td style="width: 5%;">:</td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">Telp: ' .  $dataAPL1['no_telp_perusahaan'] . '</td>
  //     </tr>
  //     <tr><td></td></tr>
  //     <tr>
  //     <th style="width: 30%;" ></th>
  //     <td style="width: 5%;"></td>
  //     <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email_perusahaan'] . '</td>
  //     </tr>
  //     </table>
  //     </li>
  //     </ol>
  //     ';

  //     $html .= '
  //     </body>
  //     </html>';

  //     // Output the HTML content to the PDF
  //     $pdf->writeHTML($html, true, false, true, false, '');

  //     $pdf->AddPage();

  //     $html2 = '
  //     <!DOCTYPE html>
  //     <html lang="en">

  //     <head>
  //         <meta charset="UTF-8">
  //         <meta name="viewport" content="width=device-width, initial-scale=1.0">
  //     </head>
  //     <style>
  //     table, th, td {
  //         border: 1px solid black;
  //         border-collapse: collapse;
  //       }
  //     </style>
  //     <body>
  //     ';

  //     $html2 .= '

  //     <h4>Bagian 2 : Data Sertifikasi</h4>
  //     <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>

  //     <table>
  //     <tr>
  //       <td style="width: 30%;" rowspan="2">
  //       Skema Sertifikasi
  //       (KKNI/Okupasi/Klaster)
  //       </td>
  //       <td style="width: 10%;">Judul</td>
  //       <td style="width: 5%;">:</td>
  //       <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
  //     </tr>
  //     <tr>
  //       <td>Nomor</td>
  //       <td>:</td>
  //       <td></td>
  //     </tr>
  //     <tr>
  //       <td colspan="2" rowspan="4">Tujuan Asesmen</td>
  //       <td>:</td>
  //       <td>Sertifikasi</td>
  //     </tr>
  //     <tr>
  //     <td></td>
  //     <td>Pengakuan Kompetensi Lampau (PKT)</td>
  //     </tr>
  //     <tr>
  //     <td></td>
  //     <td>Rekognisi Pembelajaran Lampau (RPL)</td>
  //     </tr>
  //     <tr>
  //     <td></td>
  //     <td>Lainya</td>
  //     </tr>
  //   </table>

  //   <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
  //   <table>
  //   <tr>
  //     <th style="width: 5%;">No.</th>
  //     <th style="width: 25%;">Kode Unit</th>
  //     <th style="width: 45%;">Judul Unit</th>
  //     <th style="width: 25%;">Standar Kompetensi Kerja</th>
  //   </tr>
  //   ';

  //     $no = 0;
  //     foreach ($listUnit as $key => $value) {
  //         $no++;

  //         $html2 .= '

  //         <tr>
  //           <td>' . $no . '.</td>
  //           <td>' . $value['kode_unit'] . '</td>
  //           <td>' . $value['nama_unit'] . '</td>
  //           <td></td>
  //         </tr>

  //        ';
  //     }

  //     $html2 .= '
  //     </table>

  //     </body>
  //     </html>';

  //     // Output the HTML content to the PDF
  //     $pdf->writeHTML($html2, true, false, true, false, '');

  //     $pdf->AddPage();

  //     $html3 = '
  //     <!DOCTYPE html>
  //     <html lang="en">

  //     <head>
  //         <meta charset="UTF-8">
  //         <meta name="viewport" content="width=device-width, initial-scale=1.0">
  //     </head>
  //     <style>
  //     table, th, td {
  //         border: 1px solid black;
  //         border-collapse: collapse;
  //       }
  //     </style>
  //     <body>
  //     ';

  //     $html3 .= '
  //     <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
  //     <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>

  //     <table>
  //     <tr style="text-align: center;">
  //         <th style="width: 5%;" rowspan="2">No.</th>
  //         <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
  //         <th style="width: 30%;" colspan="2">Ada</th>
  //         <th style="width: 15%;" rowspan="2">Tidak Ada</th>
  //     </tr>
  //     <tr style="text-align: center;">
  //         <th style="width: 15%;">Memenuhi Syarat</th>
  //         <th style="width: 15%;">Tidak Memenuhi Syarat</th>
  //     </tr>
  //     <tr>
  //         <td>1.</td>
  //         <td>Fotocopy Kartu Keluarga</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>
  //     <tr>
  //         <td>2.</td>
  //         <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>
  //     <tr>
  //         <td>3.</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>
  //     </table>

  //     <h4>3.2 Bukti Administratif</h4>

  //     <table>
  //     <tr style="text-align: center;">
  //         <th style="width: 5%;"  rowspan="2">No.</th>
  //         <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
  //         <th style="width: 30%;" colspan="2">Ada</th>
  //         <th style="width: 15%;" rowspan="2">Tidak Ada</th>
  //     </tr>
  //     <tr style="text-align: center;">
  //         <th>Memenuhi Syarat</th>
  //         <th>Tidak Memenuhi Syarat</th>
  //     </tr>
  //     <tr>
  //         <td>1.</td>
  //         <td>Fotocopy Raport</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>
  //     <tr>
  //         <td>2.</td>
  //         <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>
  //     <tr>
  //         <td>3.</td>
  //         <td>Fotocopy Kartu Pelajar</td>
  //         <td></td>
  //         <td></td>
  //         <td></td>
  //     </tr>

  //     </table>

  //     <h4></h4>

  //     <table>
  //     <tr>
  //       <th style="width: 50%;" rowspan="3">
  //       Rekomendasi (DIisi Oleh LSP): <br>
  //       Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon: <br> 
  //       <i>Diterima / Tidak Diterima</i> *) Sebagai Peserta Sertifikasi <br>
  //       *coret yang tidak perlu
  //       </th>
  //       <th style="width: 50%;" colspan="2">Pemohon/Kandidat</th>
  //     </tr>
  //     <tr>
  //       <th style="width: 20%;">Nama</th>
  //       <th style="width: 30%;"></th>
  //     </tr>
  //     <tr>
  //       <th style="width: 20%;">Tanda Tangan / Tanggal</th>
  //       <th style="width: 30%;"></th>
  //     </tr>
  //     <tr>
  //       <th rowspan="3">
  //       Catatan :
  //       </th>
  //       <th colspan="2">Admin LSP</th>
  //     </tr>
  //     <tr>
  //       <th style="width: 20%;">Nama</th>
  //       <th style="width: 30%;"></th>
  //     </tr>
  //     <tr>
  //       <th style="width: 20%;">Tanda Tangan / Tanggal</th>
  //       <th style="width: 30%;"></th>
  //     </tr>
  //     </table>

  //    ';


  //     $html3 .= '
  //     </body>
  //     </html>';

  //     $pdf->lastPage();

  //     // Output the HTML content to the PDF
  //     $pdf->writeHTML($html3, true, false, true, false, '');

  //     // Output the PDF as attachment to browser
  //     $this->response->setContentType('application/pdf');
  //     $pdf->Output('FR.APL.01.pdf', 'I');
  // }
}
