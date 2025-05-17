<?php

namespace App\Controllers;

use Config\Services;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\QRCodeService;
use App\Services\FormatterService;
use App\Services\ValidationService;
use CodeIgniter\HTTP\ResponseInterface;


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
        $this->cache = Services::cache();
        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();
        $this->formatterService = new FormatterService();
        $this->emailService = new EmailService();
        $this->validationService = new ValidationService();
    }

    /**
     * Display list of APL1 forms
     *
     * @return string
     */
    public function index(): string
    {
        $data = [
            'listAPL1' => $this->pengajuanAsesmenModel->getAPL1List(),
            'listEmailAPL1' => $this->apl1Model->getEmailValidasiToday(),
            'siteTitle' => "Form APL 1"
        ];

        return view('dashboard/kelola_apl1', $data);
    }

    /**
     * Generate APL1 PDF document
     *
     * @param int $id_apl1 The APL1 ID
     * @return void
     */
    public function pdf($id_apl1): void
    {
        // Get required data
        $dataAPL1 = $this->pengajuanAsesmenModel->getCompleteAPL1Data($id_apl1);

        dd($dataAPL1);

        $listUnit = $this->unitModel->getUnit($dataAPL1['asesmen']['id_skema']);

        // Prepare data for PDF
        $pdfData = $this->preparePdfData($dataAPL1, $listUnit);

        // Generate PDF
        $this->generatePdf($pdfData);
    }

    /**
     * Prepare data for PDF generation
     *
     * @param array $dataAPL1 APL1 data
     * @param array $listUnit List of units
     * @return array
     */
    private function preparePdfData(array $dataAPL1, array $listUnit): array
    {
        $nama_admin = $dataAPL1['validator_apl1'] ?? '';
        $tanda_tangan_admin = $dataAPL1['ttd_validator_apl1'] ?? '';

        // Generate QR codes
        $qr_asesi = $this->qrCodeService->generate(
            base_url('/scan-tanda-tangan-asesi/' . $dataAPL1['dokumen']['tanda_tangan_asesi']),
            'logolsp.png'
        );

        $qr_admin = null;
        if (!empty($tanda_tangan_admin)) {
            $qr_admin = $this->qrCodeService->generate(
                base_url('/scan-tanda-tangan-admin/' . $tanda_tangan_admin),
                'logolsp.png'
            );
        }

        return [
            'apl1' => $dataAPL1,
            'listUnit' => $listUnit,
            'jenisKelaminFormatted' => $this->formatterService->formatJenisKelamin($dataAPL1['asesi']['jenis_kelamin']),
            'jenisSertifikasiFormatted' => $this->formatterService->formatJenisSertifikasi($dataAPL1['asesmen']['jenis_skema']),
            'tujuanFormatted' => $this->formatterService->formatTujuanAsesmen($dataAPL1['asesmen']['tujuan']),
            'statusFormatted' => $this->formatterService->formatStatus($dataAPL1['pengajuan']['status']),
            'buktiDasarFormatted' => $this->formatterService->formatBuktiDasar($dataAPL1['dokumen']['pas_foto'] ?? null),
            'nama_admin' => $nama_admin,
            'qr_asesi' => $qr_asesi,
            'qr_admin' => $qr_admin,
        ];
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
            'pdf/apl1_page1',
            'pdf/apl1_page2',
            'pdf/apl1_page3'
        ];

        $filename = 'FR.APL.01. ' . $data['apl1']['asesi']['nama'];

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    /**
     * Display validation page
     * 
     * @return string
     */
    public function validasi(): string
    {
        $data = [
            'siteTitle' => 'Validasi FR.APL.01',
            'listAPL1Pending' => $this->apl1Model->getPendingData(),
            'listAPL1Validated' => $this->apl1Model->getValidatedData(),
        ];

        return view('dashboard/validasi_apl1', $data);
    }

    /**
     * Store validation result
     * 
     * @return ResponseInterface
     */
    public function store_validasi(): ResponseInterface
    {
        $validationRules = $this->validationService->getValidationRules('apl1_validation');

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id_apl1 = $this->request->getVar('id');

        $data = [
            'validasi_apl1' => $this->request->getVar('validasi_apl1'),
            'validasi_admin' => $this->request->getVar('id_admin'),
        ];

        if ($this->apl1Model->update($id_apl1, $data)) {
            $asesi = $this->apl1Model->getAPL1($id_apl1);

            if ($asesi) {
                $this->sendValidationEmail($asesi, $id_apl1);
            }
        }

        return $this->respondWithSuccess('validasi_apl1', 'Form APL 1 berhasil divalidasi dan email notifikasi telah dikirim!');
    }

    /**
     * Send validation email by date
     * 
     * @return ResponseInterface
     */
    public function send_email_validasi_by_date(): ResponseInterface
    {
        $validationRules = $this->validationService->getValidationRules('date_validation');

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dateValidated = $this->request->getVar('dateValidated');
        $listAPL1Validated = $this->apl1Model->getEmailValidasiByDate($dateValidated);

        if (empty($listAPL1Validated)) {
            return redirect()->back()->withInput()->with('warning', 'Data Validasi Pada tanggal ' . $dateValidated . ' Kosong.');
        }

        foreach ($listAPL1Validated as $row) {
            $data = ['email_validasi' => '1'];

            if ($this->apl1Model->update($row['id_apl1'], $data)) {
                $this->sendValidationEmail($row, $row['id_apl1']);
            }
        }

        return $this->respondWithSuccess('kelola_apl1', 'Email Validasi Form APL 1 berhasil terkirim');
    }

    /**
     * Send validation email
     * 
     * @param array $asesi Applicant data
     * @param int $id_apl1 APL1 ID
     * @return void
     */
    private function sendValidationEmail(array $asesi, int $id_apl1): void
    {
        $to = $asesi['email'];
        $nama_asesi = $asesi['nama_siswa'];
        $skema = $asesi['nama_skema'];
        $subject = 'Validasi Data Pendaftaran Uji Kompetensi Keahlian';

        $emailData = [
            'skema' => $skema,
            'name' => $nama_asesi,
            'id' => $id_apl1
        ];

        if ($asesi['validasi_apl1'] != "validated") {
            $emailData['alasan_penolakan'] = '';
            $emailData['email_kontak'] = 'lspp1smkn2kuningan@gmail.com';
            $emailData['telepon_kontak'] = '0812345678';
        }

        $template = ($asesi['validasi_apl1'] == "validated") ?
            'email/email_validated_apl1' :
            'email/email_unvalidated_apl1';

        $this->emailService->sendEmail($to, $subject, $template, $emailData);
    }

    /**
     * View QR code scan result for applicant signature
     *
     * @param string $ttd Signature ID
     * @return string
     */
    public function scan_ttd_asesi(string $ttd): string
    {
        return view('scan/scan-ttd-asesi', [
            'data' => $this->apl1Model->getbyttdAsesi($ttd),
            'siteTitle' => 'Scan Tanda Tangan'
        ]);
    }

    /**
     * View QR code scan result for admin signature
     *
     * @param string $ttd Signature ID
     * @return string
     */
    public function scan_ttd_admin(string $ttd): string
    {
        return view('scan/scan-ttd-validator', [
            'data' => $this->apl1Model->getbyttdAdmin($ttd),
            'siteTitle' => 'Scan Tanda Tangan'
        ]);
    }
    // public function kabupaten(): string
    // {
    //     $id_provinsi = $this->request->getPost('id_provinsi');
    //     $kab = $this->dynamicDependentModel->getAllKabupaten($id_provinsi);
    //     $oldKabupaten = old('kabupaten');

    //     return $this->renderSelectOptions($kab, '-- Pilih Kabupaten/Kota --', $oldKabupaten);
    // }

    // public function kecamatan(): string
    // {
    //     $id_kabupaten = $this->request->getPost('id_kabupaten');
    //     $kec = $this->dynamicDependentModel->getAllKecamatan($id_kabupaten);
    //     $oldKecamatan = old('kecamatan');

    //     return $this->renderSelectOptions($kec, '-- Pilih Kecamatan --', $oldKecamatan);
    // }

    // public function desa(): string
    // {
    //     $id_kecamatan = $this->request->getPost('id_kecamatan');
    //     $desa = $this->dynamicDependentModel->getAllDesa($id_kecamatan);
    //     $oldDesa = old('kelurahan');

    //     return $this->renderSelectOptions($desa, '-- Pilih Kelurahan/Desa --', $oldDesa);
    // }

    /**
     * Optimized kabupaten method with caching
     */
    public function kabupaten()
    {
        $id_provinsi = $this->request->getPost('id_provinsi');
        $selectedValue = $this->request->getPost('selected_value') ?? old('kabupaten');

        // Generate unique cache key for this request
        $cacheKey = "kabupaten_{$id_provinsi}_{$selectedValue}";

        // Try to get from cache first
        $cachedResult = $this->cache->get($cacheKey);
        if ($cachedResult) {
            return $this->response->setJSON($cachedResult);
        }

        // If not in cache, fetch from database
        $data = $this->dynamicDependentModel->getAllKabupaten($id_provinsi);
        $result = [
            'options' => renderSelectOptions($data, '-- Pilih Kabupaten/Kota --', $selectedValue)
        ];

        // Store in cache for future requests
        $this->cache->save($cacheKey, $result, $this->cacheExpiration);

        return $this->response->setJSON($result);
    }

    /**
     * Optimized kecamatan method with caching
     */
    public function kecamatan()
    {
        $id_kabupaten = $this->request->getPost('id_kabupaten');
        $selectedValue = $this->request->getPost('selected_value') ?? old('kecamatan');

        // Generate unique cache key for this request
        $cacheKey = "kecamatan_{$id_kabupaten}_{$selectedValue}";

        // Try to get from cache first
        $cachedResult = $this->cache->get($cacheKey);
        if ($cachedResult) {
            return $this->response->setJSON($cachedResult);
        }

        // If not in cache, fetch from database
        $data = $this->dynamicDependentModel->getAllKecamatan($id_kabupaten);
        $result = [
            'options' => renderSelectOptions($data, '-- Pilih Kecamatan --', $selectedValue)
        ];

        // Store in cache for future requests
        $this->cache->save($cacheKey, $result, $this->cacheExpiration);

        return $this->response->setJSON($result);
    }

    /**
     * Optimized desa method with caching
     */
    public function desa()
    {
        $id_kecamatan = $this->request->getPost('id_kecamatan');
        $selectedValue = $this->request->getPost('selected_value') ?? old('kelurahan');

        // Generate unique cache key for this request
        $cacheKey = "desa_{$id_kecamatan}_{$selectedValue}";

        // Try to get from cache first
        $cachedResult = $this->cache->get($cacheKey);
        if ($cachedResult) {
            return $this->response->setJSON($cachedResult);
        }

        // If not in cache, fetch from database
        $data = $this->dynamicDependentModel->getAllDesa($id_kecamatan);
        $result = [
            'options' => renderSelectOptions($data, '-- Pilih Kelurahan/Desa --', $selectedValue)
        ];

        // Store in cache for future requests
        $this->cache->save($cacheKey, $result, $this->cacheExpiration);

        return $this->response->setJSON($result);
    }

    /**
     * Optimized version of renderSelectOptions function
     * This function uses StringBuilder approach to improve performance with large datasets
     */
    function renderSelectOptions($data, $placeholder = null, $selectedValue = null)
    {
        $options = [];

        if ($placeholder !== null) {
            $options[] = "<option value=\"\">$placeholder</option>";
        }

        foreach ($data as $item) {
            $id = isset($item['id']) ? $item['id'] : $item[0];
            $name = isset($item['name']) ? $item['name'] : $item[1];
            $selected = ($selectedValue == $id) ? ' selected' : '';
            $options[] = "<option value=\"$id\"$selected>$name</option>";
        }

        return implode('', $options);
    }

    /**
     * Get validated data by date (AJAX)
     * 
     * @return string
     */
    public function getDateValidated(): string
    {
        $dataAPL1 = $this->apl1Model->getEmailValidasiByDate($this->request->getVar('dateValidated'));
        $output = '';
        $no = 0;

        foreach ($dataAPL1 as $row) {
            $no++;
            $badgeColor = $this->getBadgeColorForStatus($row['validasi_apl1']);

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

        return $output;
    }

    /**
     * Get badge color based on validation status
     * 
     * @param string $status Validation status
     * @return string
     */
    private function getBadgeColorForStatus(string $status): string
    {
        switch ($status) {
            case 'validated':
                return 'success';
            case 'pending':
                return 'warning';
            default:
                return 'danger';
        }
    }

    /**
     * Delete APL1 record
     * 
     * @return ResponseInterface
     */
    public function delete(): ResponseInterface
    {
        $id = $this->request->getVar('id');
        $this->pengajuanAsesmenModel->deleteCompleteAPL1($id);

        return $this->respondWithSuccess('/kelola_apl1', 'FR.APL.01 berhasil dihapus!');
    }

    /**
     * Helper method to create consistent success responses
     * 
     * @param string $redirectPath Where to redirect
     * @param string $message Flash message
     * @return ResponseInterface
     */
    private function respondWithSuccess(string $redirectPath, string $message): ResponseInterface
    {
        session()->setFlashdata('pesan', $message);
        return redirect()->to($redirectPath);
    }
}
