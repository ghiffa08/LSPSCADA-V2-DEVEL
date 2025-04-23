<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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
    public function index()
    {

        $data = [
            'listAPL1' => $this->apl1Model->findAllAPL1(),
            'listEmailAPL1' => $this->apl1Model->getEmailValidasiToday(),
            'siteTitle' => "Form APL 1"
        ];
        // dd($data);
        return view('dashboard/kelola_apl1', $data);
    }

    public function pdf($id_apl1)
    {
        $dataAPL1 = $this->apl1Model->getAPL1($id_apl1);
        $listUnit = $this->unit->getUnit($dataAPL1['skema_id']);

        $data = [
            'apl1' => $dataAPL1,
            'listUnit' => $listUnit,
            'jenisKelaminFormatted' => formatJenisKelamin($dataAPL1['jenis_kelamin']),
            'jenisSertifikasiFormatted' => formatJenisSertifikasi($dataAPL1['jenis_skema']),
            'tujuanFormatted' => formatTujuan($dataAPL1['tujuan']),
        ];

        ob_start();
        echo view('pdf/pdf_apl1', $data);
        $html = ob_get_clean();

        $pdf = initTCPDF('FR.APL.01. ' . $dataAPL1['nama_siswa']);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('FR.APL.01. ' . $dataAPL1['nama_siswa'] . '.pdf', 'I');
        exit;
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
        $data = [
            'validasi_apl1' => $this->request->getVar('validasi_apl1'),
            'validasi_admin' => $this->request->getVar('id_admin'),
        ];

        $this->apl1Model->update($this->request->getVar('id'), $data);

        session()->setFlashdata('pesan', 'Form APL 1 berhasil divalidasi!');
        return redirect()->to('kelola_apl1/validasi');
    }

    public function send_email_validasi()
    {

        $listAPL1Validated = $this->apl1Model->getEmailValidasiToday();

        $subject = 'Validasi Data Pendaftaran Uji Kompetensi Keahlian';

        foreach ($listAPL1Validated as $row) {

            $data = [
                'email_validasi' => '1',
            ];

            if ($this->apl1Model->update($row['id_apl1'], $data)) {

                $to = $row['email'];

                $nama_asesi = $row['nama_siswa'];

                $id_apl1 = $row['id_apl1'];

                $skema = $row['nama_skema'];

                if ($row['validasi_apl1'] == "validated") {
                    $message = view('email/email_validated_apl1', [
                        'skema' => $skema,
                        'name' => $nama_asesi,
                        'id' => $id_apl1
                    ]);
                } else {
                    $message = view('email/email_unvalidated_apl1', [
                        'skema' => $skema,
                        'name' => $nama_asesi,
                        'id' => $id_apl1,
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

                if (!$email->send()) {
                    $data = $email->printDebugger(['headers']);
                    print_r($data);
                }
            }
        }
        session()->setFlashdata('pesan', 'Email Validasi Form APL 1 berhasil terkirim');
        return redirect()->to('kelola_apl1');
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

        $listAPL1Validated = $this->apl1Model->getEmailValidasiByDate($this->request->getVar('dateValidated'));

        if ($listAPL1Validated == null) {
            session()->setFlashdata('warning', 'Data Validasi Pada tanggal ' . $this->request->getVar('dateValidated') . ' Kosong.');
            return redirect()->back()->withInput();
        }

        $subject = 'Validasi Data Pendaftaran Uji Kompetensi Keahlian';

        foreach ($listAPL1Validated as $row) {

            $data = [
                'email_validasi' => '1',
            ];

            if ($this->apl1Model->update($row['id_apl1'], $data)) {

                $to = $row['email'];

                $nama_asesi = $row['nama_siswa'];

                $id_apl1 = $row['id_apl1'];

                $skema = $row['nama_skema'];

                if ($row['validasi_apl1'] == "validated") {
                    $message = view('email/email_validated_apl1', [
                        'skema' => $skema,
                        'name' => $nama_asesi,
                        'id' => $id_apl1
                    ]);
                } else {
                    $message = view('email/email_unvalidated_apl1', [
                        'skema' => $skema,
                        'name' => $nama_asesi,
                        'id' => $id_apl1,
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

                if (!$email->send()) {
                    $data = $email->printDebugger(['headers']);
                    print_r($data);
                }
            }
        }
        session()->setFlashdata('pesan', 'Email Validasi Form APL 1 berhasil terkirim');
        return redirect()->to('kelola_apl1');
    }

    public function scan_ttd_asesi($ttd)
    {
        return view('scan/scan-ttd-asesi', [
            'data' => $this->apl1Model->getbyttdAsesi($ttd),
            'siteTitle' => 'Scan Tanda Tangan'
        ]);
    }

    public function scan_ttd_admin($ttd)
    {
        return view('scan/scan-ttd-validator', [
            'data' => $this->apl1Model->getbyttdAdmin($ttd),
            'siteTitle' => 'Scan Tanda Tangan'
        ]);
    }

    public function kabupaten()
    {
        $id_provinsi = $this->request->getPost('id_provinsi');
        $kab = $this->dependent->AllKabupaten($id_provinsi);
        echo '<option>-- Pilih Kabupaten/Kota --</option>';
        foreach ($kab as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }

    public function kecamatan()
    {
        $id_kabupaten = $this->request->getPost('id_kabupaten');
        $kec = $this->dependent->AllKecamatan($id_kabupaten);
        echo '<option>-- Pilih Kecamatan --</option>';
        foreach ($kec as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }

    public function desa()
    {

        $id_kecamatan = $this->request->getPost('id_kecamatan');
        $desa = $this->dependent->AllDesa($id_kecamatan);
        echo '<option>-- Pilih Kelurahan/Desa --</option>';
        foreach ($desa as $key => $k) {
            echo "<option value=" . $k['id'] . ">" . $k['nama'] . "</option>";
        }
    }

    public function getDateValidated()
    {
        $dataAPL1 = $this->apl1Model->getEmailValidasiByDate($this->request->getVar('dateValidated'));
        $no = null;
        $output = '';

        foreach ($dataAPL1 as $row) {
            $no++;
            $badgeColor = '';

            switch ($row['validasi_apl1']) {
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
                <td>" . $row['nama_siswa'] . "</td>
                <td>" . $row['nama_skema'] . "</td>
                <td>
                    <div class='badge badge-" . $badgeColor . "'>" . $row['validasi_apl1'] . "</div>
                </td>
            </tr>";
        }

        echo $output;
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->apl1Model->delete($id);
        session()->setFlashdata('pesan', 'FR.APL.01 berhasil dihapus!');
        return redirect()->to('/kelola_apl1');
    }
}
