<?php

namespace App\Controllers;

use Config\Services;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\QRCodeService;
use App\Services\FormatterService;
use App\Services\ValidationService;
use CodeIgniter\HTTP\ResponseInterface;
use Google\Service\CloudSearch\Id;
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

class APL1Controller extends BaseController
{
    protected $pdfService;
    protected $qrCodeService;
    protected $formatterService;
    protected $emailService;
    protected $validationService;

    private $cacheExpiration = 3600;
    private $cache;

    public function __construct()
    {
        helper(['auth', 'application']);

        $this->cache = Services::cache();
        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();
        $this->formatterService = new FormatterService();
        $this->emailService = new EmailService();
        $this->validationService = new ValidationService();

        $this->dynamicDependentModel = new \App\Models\DynamicDependent();
    }


    public function pdf($id_apl1)
    {
        $dataPengajuan = $this->pengajuanAsesmenModel->getPengajuanById($id_apl1);
    $listUnit = $this->unitModel->getUnit($dataPengajuan['id_skema']);


        $jenis_kelamin = ($dataPengajuan['jenis_kelamin'] == "Laki-Laki") ? 'Laki-Laki / <span style="text-decoration: line-through;">Perempuan</span>' : '<span style="text-decoration: line-through;">Laki-Laki</span> / Perempuan';

        $jenis_sertifikasi = '';

        if ($dataPengajuan['jenis_skema'] == "KKNI") {
            $jenis_sertifikasi = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>';
        } elseif ($dataPengajuan['jenis_skema'] == "Okupasi") {
            $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>';
        } else {
            $jenis_sertifikasi = '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster';
        }

        $tujuan = '';
        if ($dataPengajuan['tujuan'] == "Sertifikasi") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td>Sertifikasi</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } elseif ($dataPengajuan['tujuan'] == "PKT") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Pengakuan Kompetensi Lampau (PKT)</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } elseif ($dataPengajuan['tujuan'] == "RPL") {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Rekognisi Pembelajaran Lampau (RPL)</td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Lainya</span></td>
            </tr>
            ';
        } else {
            $tujuan = '
             <tr>
              <td colspan="2" rowspan="4">Tujuan Asesmen</td>
              <td>:</td>
              <td><span style="text-decoration: line-through;">Sertifikasi</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Pengakuan Kompetensi Lampau (PKT)</span></td>
            </tr>
            <tr>
            <td></td>
            <td><span style="text-decoration: line-through;">Rekognisi Pembelajaran Lampau (RPL)</span></td>
            </tr>
            <tr>
            <td></td>
            <td>Lainya</td>
            </tr>
            ';
        }

        $bukti_dasar = '';
        if (isset($dataAPL1['pas_foto'])) {
            $bukti_dasar = '
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
            ';
        } else {
            $bukti_dasar = '
            <tr>
                <td>2.</td>
                <td>Foto Berwarna Ukuran 3x4 2 Lembar</td>
                <td style="text-align: center;"></td>
                <td></td>
                <td>Tidak Ada</td>
            </tr>
            ';
        }

        // $status_apl1 = '';
        // if ($dataPengajuan['validasi_apl1'] == "validated") {
        //     $status_apl1 = '
        //    Diterima / <span style="text-decoration: line-through;">Tidak Diterima</span>
        //     ';
        // } elseif ($dataPengajuan['validasi_apl1'] == "unvalidated") {
        //     $status_apl1 = '
        //    <span style="text-decoration: line-through;">Diterima</span> / Tidak Diterima
        //     ';
        // } else {
        //     $status_apl1 = '
        //    Diterima / Tidak Diterima
        //     ';
        // }


        // $nama_admin = (isset($dataPengajuan['validator_apl1'])) ? $dataPengajuan['validator_apl1'] : '';

        // $tanda_tangan_admin = (isset($dataPengajuan['ttd_validator_apl1'])) ?  $dataPengajuan['ttd_validator_apl1'] : '';


        // Create a new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('LSP SMK NEGERI 2 Kuningan');

        // Set title based on user's full name
        $pdf->SetTitle('FR.APL.01. ');

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

        // // set cell padding
        // $pdf->setCellPaddings(1, 1, 1, 1);

        // // set cell margins
        // $pdf->setCellMargins(1, 1, 1, 1);

        // Add title
        // $pdf->SetFont('helvetica', 'B', 10);
        // $pdf->Cell(0, 10, 'FR.APL.01. PERMOHONAN SERTIFIKASI KOMPETENSI', 0, 1, 'L');

        // // Add subtitle
        // $pdf->SetFont('helvetica', 'B', 10);
        // $pdf->Cell(0, 10, 'Bagian 1 : Rincian Data Pemohon Sertifikasi', 0, 1, 'L');

        // // Add description
        // $pdf->SetFont('helvetica', '', 10);
        // $pdf->MultiCell(0, 10, 'Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.', 0, 'L');

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
            </head>
            <body>
            ';

        $html .= '
    
    
            <h4>FR.APL.01. PERMOHONAN DATA PEMOHON SERTIFIKASI KOMPETENSI</h4>
            <h4>Bagian 1 : Rincian Data Pemohon Sertifikasi</h4>
            <p>Pada bagian ini, cantumlah data pribadi, data pendidikan formal serta data pekerjaan anda pada saat ini.</p>
            <ol type="a">
            <li>
            <table>  
            <tr>
            <th>Data Pribadi</th>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Lengkap</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_siswa'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No. KTP/NIK/Paspor</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nik'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Tempat / Tanggal Lahir</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['tempat_lahir'] . ', ' . date('d/m/Y', strtotime($dataAPL1['tanggal_lahir'])) . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jenis Kelamin</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; ">' .  $jenis_kelamin . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Kebangsaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['kebangsaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Alamat Rumah</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Kel. ' .  $dataAPL1['nama_kelurahan'] . ', Kec. ' .  $dataAPL1['nama_kecamatan'] . ', ' .  $dataAPL1['nama_kabupaten'] . ', Prov. ' .  $dataAPL1['nama_provinsi'] . ', Kode Pos :' .  $dataAPL1['kode_pos'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">HP: ' .  $dataAPL1['no_hp'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No Telepon/Email</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Rumah: ' .  $dataAPL1['telpon_rumah'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Pendidikan Terakhir</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['pendidikan_terakhir'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Sekolah/Universitas</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_sekolah'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jurusan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jurusan'] . '</td>
            </tr>
            </table>
            </li>
            <li>
            <table>  
            <tr>
            <th>Data Pekerjaan Sekarang</th>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Pekerjaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['pekerjaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Nama Institusi/Perusahaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['nama_lembaga'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Jabatan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['jabatan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >Alamat Perusahaan</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">' .  $dataAPL1['alamat_perusahaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" >No Telepon/Email</th>
            <td style="width: 5%;">:</td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Telp: ' .  $dataAPL1['no_telp_perusahaan'] . '</td>
            </tr>
            <tr><td></td></tr>
            <tr>
            <th style="width: 30%;" ></th>
            <td style="width: 5%;"></td>
            <td style="width: 55%; border-bottom: 1px solid #000;">Email: ' .  $dataAPL1['email_perusahaan'] . '</td>
            </tr>
            </table>
            </li>
            </ol>
            ';

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
                <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
           padding: 3px;
            }
            </style>
            <body>
            ';

        $html2 .= '
    
            <h4>Bagian 2 : Data Sertifikasi</h4>
            <p>Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang pendidikan, pelatihan serta pengalaman kerja yang anda miliki.</p>
    
            <table>
            <tr>
              <td style="width: 30%;" rowspan="2">
              Skema Sertifikasi<br>
           ' . $jenis_sertifikasi . '   
              </td>
              <td style="width: 10%;">Judul</td>
              <td style="width: 5%;">:</td>
              <td style="width: 55%;">' . $dataAPL1['nama_skema'] . '</td>
            </tr>
            <tr>
              <td>Nomor</td>
              <td>:</td>
              <td></td>
            </tr>
           ' . $tujuan . '
          </table>
    
          <h4>Daftar Unit Kompetensi Sesuai Kemasan:</h4>
          <table>
          <tr>
            <th style="width: 5%;">No.</th>
            <th style="width: 25%;">Kode Unit</th>
            <th style="width: 45%;">Judul Unit</th>
            <th style="width: 25%;">Standar Kompetensi Kerja</th>
          </tr>
          ';

        $no = 0;
        foreach ($listUnit as $key => $value) {
            $no++;

            $html2 .= '
    
                <tr>
                  <td>' . $no . '.</td>
                  <td>' . $value['kode_unit'] . '</td>
                  <td>' . $value['nama_unit'] . '</td>
                  <td style="text-align: center;">SKKNI</td>
                </tr>
    
               ';
        }

        $html2 .= '
            </table>
    
            </body>
            </html>';

        // Output the HTML content to the PDF
        $pdf->writeHTML($html2, true, false, true, false, '');

        $pdf->AddPage();


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

        $ttd_admin = QrCode::create(base_url('/scan-tanda-tangan-admin/' . $tanda_tangan_admin))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(200)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        // $label = Label::create($dataAPL1['fullname'])
        //     ->setTextColor(new Color(000, 0, 0));

        $result = $writer->write($ttd_admin, $logo);

        $qr_admin = $result->getDataUri();



        $html3 = '
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
             <link rel="shortcut icon" href="' . base_url('asset_img/logolsp.png') . '" type="image/x-icon">
            <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
           padding: 2.5px;
            }
            </style>
            <body>
            ';

        $html3 .= '
            <h4>Bagian 3 : Bukti Kelengkapan Pemohon</h4>
            <h4>3.1 Bukti Persyaratan Dasar Pemohon</h4>
    
            <table>
            <tr style="text-align: center;">
                <th style="width: 5%;" rowspan="2">No.</th>
                <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
                <th style="width: 30%;" colspan="2">Ada</th>
                <th style="width: 15%;" rowspan="2">Tidak Ada</th>
            </tr>
            <tr style="text-align: center;">
                <th style="width: 15%;">Memenuhi Syarat</th>
                <th style="width: 15%;">Tidak Memenuhi Syarat</th>
            </tr>
            <tr>
                <td>1.</td>
                <td>Fotocopy Kartu Keluarga</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
           ' . $bukti_dasar . '
            <tr>
                <td>3.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </table>
    
            <h4>3.2 Bukti Administratif</h4>
    
            <table>
            <tr style="text-align: center;">
                <th style="width: 5%;"  rowspan="2">No.</th>
                <th style="width: 50%;" rowspan="2">Bukti Persyaratan Dasar</th>
                <th style="width: 30%;" colspan="2">Ada</th>
                <th style="width: 15%;" rowspan="2">Tidak Ada</th>
            </tr>
            <tr style="text-align: center;">
                <th>Memenuhi Syarat</th>
                <th>Tidak Memenuhi Syarat</th>
            </tr>
            <tr >
                <td>1.</td>
                <td>Fotocopy Raport</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Fotocopy Sertifikat/Surat Keterangan PKL</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Fotocopy Kartu Pelajar</td>
                <td style="text-align: center;">Ada</td>
                <td></td>
                <td></td>
            </tr>
    
            </table>
    
            <h4></h4>
    
            <table>
            <tr>
              <th style="width: 50%;" rowspan="4">Rekomendasi (DIisi Oleh LSP):<br>Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon<i>' . $status_apl1 . '</i> *) Sebagai Peserta Sertifikasi <br>*coret yang tidak perlu
              </th>
              <th style="width: 50%;" colspan="2">Pemohon/Kandidat</th>
            </tr>
            <tr>
              <th style="width: 20%;">Nama</th>
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
              <th rowspan="4">Catatan :<br>Direkomendasikan untuk melanjutkan Asesmen
              </th>
              <th colspan="2">Admin LSP</th>
            </tr>
            <tr>
              <th style="width: 20%;">Nama</th>
              <th style="width: 30%; text-align: center;" >' . $nama_admin . '</th>
            </tr>
            <tr>
              <th rowspan="2" style="width: 20%;">Tanda Tangan / Tanggal</th>
             <th style="width: 30%; text-align: center;"><img style="width: 150px;" src="' . $qr_admin . '" ></th>
            </tr>
             <tr style="text-align: center;">
             <td>' . date('d/m/Y', strtotime($dataAPL1['updated_at'])) . '</td>

            </tr>
            </table>
    
           ';


        $html3 .= '
            </body>
            </html>';


        $pdf->lastPage();

        $pdf->writeHTML($html3, true, false, true, false, '');

        // Output the PDF as attachment to browser
        $this->response->setContentType('application/pdf');
        $pdf->Output('FR.APL.01. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
    }

    public function validasi()
    {
        $data = [
            'siteTitle' => 'Validasi FR.APL.01',
            'listAPL1Pending' => $this->apl1Model->getPendingData(),
            'listAPL1Validated' => $this->apl1Model->getValidatedData(),
        ];

        return view('dashboard/validasi_apl1', $data);
    }

    /**
     * Store validation data AND send notification email automatically.
     */
    public function store_validasi()
    {
        $rules = [
            'validasi_apl1' => [
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

        $id_apl1 = $this->request->getVar('id');
        $validationStatus = $this->request->getVar('validasi_apl1');

        // Data untuk diupdate di database
        // HANYA baris 'tanggal_validasi' yang dihapus dari sini
        $data = [
            'validasi_apl1' => $validationStatus,
            'validasi_admin' => $this->request->getVar('id_admin'),
        ];

        // 1. Update data validasi di database
        if (!$this->apl1Model->update($id_apl1, $data)) {
            session()->setFlashdata('error', 'Gagal memperbarui status validasi di database.');
            return redirect()->to('kelola_apl1/validasi');
        }

        // 2. Kirim email notifikasi secara otomatis
        try {
            $asesiData = $this->apl1Model->getAsesiDetailForEmail($id_apl1);

            if ($asesiData) {
                $this->sendValidationEmail($asesiData, $id_apl1);
                $this->apl1Model->update($id_apl1, ['email_validasi' => 1]);
                session()->setFlashdata('pesan', 'Form APL 1 berhasil divalidasi dan email notifikasi telah dikirim!');
            } else {
                session()->setFlashdata('warning', 'Form APL 1 berhasil divalidasi, namun data asesi tidak ditemukan untuk pengiriman email.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Gagal kirim email validasi otomatis untuk APL1 ID ' . $id_apl1 . ': ' . $e->getMessage());
            session()->setFlashdata('warning', 'Form APL 1 berhasil divalidasi, namun email notifikasi gagal dikirim. Anda dapat mencoba mengirim ulang secara manual.');
        }

        return redirect()->to('kelola_apl1/validasi');
    }

    /**
     * Send validation email untuk asesi yang divalidasi hari ini
     */
    public function send_email_validasi(): ResponseInterface
    {
        // if (!in_groups('Admin')) {
        //     return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        // }

        try {
            $listAPL1Validated = $this->apl1Model->getEmailValidasiToday();

            if (empty($listAPL1Validated)) {
                session()->setFlashdata('warning', 'Tidak ada asesi yang perlu dikirim email validasi hari ini');
                return redirect()->to('kelola_apl1');
            }

            $emailsSent = 0;
            $errors = [];

            foreach ($listAPL1Validated as $row) {
                try {
                    $this->sendValidationEmail($row, $row['id_apl1']);

                    // Mark email as sent
                    $this->apl1Model->update($row['id_apl1'], ['email_validasi' => 1]);
                    $emailsSent++;
                } catch (\Exception $e) {
                    $errors[] = "Gagal kirim email ke {$row['email']}: " . $e->getMessage();
                    log_message('error', 'Email sending failed: ' . $e->getMessage());
                }
            }

            if ($emailsSent > 0) {
                $message = "Berhasil mengirim {$emailsSent} email validasi";
                if (!empty($errors)) {
                    $message .= ". " . count($errors) . " email gagal dikirim.";
                }
                session()->setFlashdata('pesan', $message);
            } else {
                session()->setFlashdata('error', 'Semua email gagal dikirim');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in send_email_validasi: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat mengirim email');
        }

        return redirect()->to('kelola_apl1');
    }

    /**
     * Send validation email by specific date
     */
    public function send_email_validasi_by_date(): ResponseInterface
    {
        // if (!in_groups('Admin')) {
        //     return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        // }

        $validationRules = [
            'dateValidated' => 'required|valid_date'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid');
        }

        try {
            $dateValidated = $this->request->getVar('dateValidated');
            $listAPL1Validated = $this->apl1Model->getEmailValidasiByDate($dateValidated);

            if (empty($listAPL1Validated)) {
                session()->setFlashdata('warning', "Tidak ada asesi yang perlu dikirim email validasi pada tanggal {$dateValidated}");
                return redirect()->to('kelola_apl1');
            }

            $emailsSent = 0;
            $errors = [];

            foreach ($listAPL1Validated as $row) {
                try {
                    $this->sendValidationEmail($row, $row['id_apl1']);

                    // Mark email as sent
                    $this->apl1Model->update($row['id_apl1'], ['email_validasi' => 1]);
                    $emailsSent++;
                } catch (\Exception $e) {
                    $errors[] = "Gagal kirim email ke {$row['email']}: " . $e->getMessage();
                    log_message('error', 'Email sending failed: ' . $e->getMessage());
                }
            }

            if ($emailsSent > 0) {
                $message = "Berhasil mengirim {$emailsSent} email validasi untuk tanggal {$dateValidated}";
                if (!empty($errors)) {
                    $message .= ". " . count($errors) . " email gagal dikirim.";
                }
                session()->setFlashdata('pesan', $message);
            } else {
                session()->setFlashdata('error', 'Semua email gagal dikirim');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in send_email_validasi_by_date: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat mengirim email');
        }

        return redirect()->to('kelola_apl1');
    }

    /**
     * Get validated data by date (AJAX)
     */
    public function getDateValidated(): string
    {
        if (!$this->request->isAJAX()) {
            return '';
        }

        $date = $this->request->getPost('dateValidated');

        if (empty($date)) {
            return '<tr><td colspan="5" class="text-center text-muted">Tanggal tidak valid</td></tr>';
        }

        try {
            $data = $this->apl1Model->getEmailValidasiByDate($date);

            if (empty($data)) {
                return '<tr><td colspan="5" class="text-center text-muted">Tidak ada data untuk tanggal ini</td></tr>';
            }

            $html = '';
            foreach ($data as $index => $row) {
                $statusBadge = $row['validasi_apl1'] === 'validated'
                    ? '<span class="badge badge-success">Valid</span>'
                    : '<span class="badge badge-danger">Tidak Valid</span>';

                $tanggalValidasi = isset($row['tanggal_validasi'])
                    ? date('d/m/Y H:i', strtotime($row['tanggal_validasi']))
                    : '-';

                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . esc($row['nama_siswa']) . '</td>';
                $html .= '<td>' . esc($row['email']) . '</td>';
                $html .= '<td>' . $statusBadge . '</td>';
                $html .= '<td>' . $tanggalValidasi . '</td>';
                $html .= '</tr>';
            }

            return $html;
        } catch (\Exception $e) {
            log_message('error', 'Error in getDateValidated: ' . $e->getMessage());
            return '<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>';
        }
    }

    /**
     * Send validation email helper
     */
    private function sendValidationEmail(array $asesi, string $id_apl1): void
    {
        $to = $asesi['email'];
        $nama_asesi = $asesi['nama_siswa'];
        $skema = $asesi['nama_skema'];
        $subject = 'Validasi Data Pendaftaran Uji Kompetensi Keahlian';

        $emailData = [
            'name' => $nama_asesi,
            'skema' => $skema,
            'id' => $id_apl1,
            'status_validasi' => $asesi['validasi_apl1'],
            'validator' => $asesi['validator_apl1'] ?? 'Admin',
            'tanggal_validasi' => $asesi['tanggal_validasi'] ?? date('Y-m-d H:i:s')
        ];

        // Choose email template based on validation status
        if ($asesi['validasi_apl1'] === "validated") {
            $template = 'email/email_validated_apl1';
            $emailData['next_step_url'] = base_url('/asesmen-mandiri/' . $id_apl1);
        } else {
            $template = 'email/email_unvalidated_apl1';
            $emailData['alasan_penolakan'] = 'Data tidak lengkap atau tidak sesuai persyaratan';
            $emailData['email_kontak'] = 'lspp1smkn2kuningan@gmail.com';
            $emailData['telepon_kontak'] = '0261-xxx-xxx';
        }

        $this->emailService->sendEmail($to, $subject, $template, $emailData);
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->apl1Model->delete($id);
        session()->setFlashdata('pesan', 'FR.APL.01 berhasil dihapus!');
        return redirect()->to('/kelola_apl1');
    }
}
