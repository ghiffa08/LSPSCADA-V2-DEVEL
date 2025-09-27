<?php

namespace App\Controllers;

use App\Models\PMOModel;
use App\Models\SkemaModel;
use App\Models\APL1Model;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\API\ResponseTrait;
use App\Models\PengajuanAsesmenModel;
use ZipArchive;
use CodeIgniter\RESTful\ResourceController;

class CeklistPMOController extends ResourceController
{
    use ResponseTrait;

    protected PMOModel $pmoModel;
    protected SkemaModel $skemaModel;
    protected APL1Model $apl1Model;
    protected PengajuanAsesmenModel $pengajuanModel;

    // Services
    protected QRCodeService $qrCodeService;
    protected PDFService $pdfService;

    public function __construct()
    {
        helper('auth');
        $this->pmoModel = new PMOModel();
        $this->skemaModel = new SkemaModel();
        $this->apl1Model = new APL1Model();

        $this->qrCodeService = new QRCodeService();
        $this->pdfService = new PDFService();
        $this->pengajuanModel = new PengajuanAsesmenModel();
    }

    /**
     * Menampilkan halaman manajemen PMO dengan filter dan tabel DataTables.
     */
    public function index()
    {
        $request = service('request');
        $asesorModel = new \App\Models\AsesorModel();
        $skemaModel = new \App\Models\SkemaModel();

        // Ambil data untuk dropdown filter
        $data['asesor_list'] = $asesorModel->select('asesor.id_asesor, users.nama_lengkap')
            ->join('users', 'users.id = asesor.id_user')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();

        $data['skema_list'] = $skemaModel->select('id_skema, nama_skema')
            ->orderBy('nama_skema', 'ASC')
            ->findAll();

        // Simpan nilai filter yang ada di URL untuk ditampilkan kembali di form
        $data['filters'] = [
            'id_asesor'      => $request->getGet('id_asesor'),
            'id_skema'       => $request->getGet('id_skema'),
            'tanggal_dari'   => $request->getGet('tanggal_dari'),
            'tanggal_sampai' => $request->getGet('tanggal_sampai'),
        ];

        $data['siteTitle'] = 'Manajemen Ceklis PMO';

        return view('asesor/pmo', $data);
    }


    /**
     * Display the PMO checklist form for a specific asesi's pengajuan.
     */
    public function show($id_pengajuan = null) // Changed parameter
    {
        try {
            if (!$id_pengajuan) {
                throw new \Exception('ID Pengajuan Asesmen tidak disediakan.');
            }

            // Fetch Pengajuan data with all related info
            $pengajuanData = $this->pengajuanModel
                ->select([
                    'pengajuan_asesmen.*',
                    'asesi.nik',
                    'user_asesi.nama_lengkap as nama_asesi',
                    'skema.id_skema',
                    'skema.nama_skema',
                    'skema.kode_skema',
                    'user_asesor.nama_lengkap as nama_asesor'
                ])
                ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                ->join('users as user_asesi', 'user_asesi.id = asesi.id_user')
                ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                ->join('skema', 'skema.id_skema = asesmen.id_skema')
                ->join('asesor', 'asesor.id_asesor = pengajuan_asesmen.id_asesor', 'left')
                ->join('users as user_asesor', 'user_asesor.id = asesor.id_user', 'left')
                ->find($id_pengajuan);

            if (!$pengajuanData) {
                throw new \Exception('Data Pengajuan Asesmen tidak ditemukan.');
            }

            $data = [
                'siteTitle' => 'Pengisian Ceklis PMO',
                'pengajuan_data' => $pengajuanData, // Changed variable name
                'id_skema' => $pengajuanData['id_skema'],
                'id_asesor' => $pengajuanData['id_asesor'],
            ];

            return view('asesi/pmo_ceklist', $data);
        } catch (\Exception $e) {
            log_message('error', '[CeklistPmoController] show error: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    public function pdf($id_pmo = null)
    {
        try {
            if (!$id_pmo || !filter_var($id_pmo, FILTER_VALIDATE_INT)) {
                throw new \InvalidArgumentException('ID PMO tidak valid.');
            }

            // Panggil helper untuk menyiapkan data PDF
            $viewData = $this->_preparePdfData((int)$id_pmo);

            $views = [
                'pdf/pmo_page1'
            ];
            $filename = 'FR.IA.03. CEKLIS PMO - ' . strtoupper($viewData['pmo']['nama_asesi'] ?? 'Asesi');

            // Panggil service untuk membuat PDF
            $this->pdfService->generateMultiPagePdf($views, $viewData, $filename);
        } catch (\Exception $e) {
            log_message('error', 'PMOController PDF Error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal generate PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate and download a ZIP file of multiple PMO PDFs directly.
     */
    public function batchPdf()
    {
        $pmoIds = $this->request->getPost('pmo_ids');

        if (empty($pmoIds) || !is_array($pmoIds)) {
            session()->setFlashdata('error', 'Tidak ada ID PMO yang dipilih atau format tidak valid.');
            return redirect()->back();
        }

        // Sanitize input to ensure all IDs are integers
        $pmoIds = array_map('intval', $pmoIds);

        // Create a unique temporary directory for this job's PDFs
        $tempDir = WRITEPATH . 'uploads/temp_pdf_pmo_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $pdfFiles = [];

        foreach ($pmoIds as $pmoId) {
            try {
                // Panggil helper untuk menyiapkan data PDF
                $viewData = $this->_preparePdfData($pmoId);

                // Sanitize filename
                $safeNamaAsesi = preg_replace('/[^a-zA-Z0-9_-]/', '_', $viewData['pmo']['nama_asesi']);
                $filename = "FR.IA.03_PMO_{$safeNamaAsesi}_{$pmoId}.pdf";
                $filePath = $tempDir . '/' . $filename;

                // Generate and save the PDF to the temp directory
                $this->pdfService->generateAndSave(['pdf/pmo_page1'], $viewData, $filePath);

                if (file_exists($filePath)) {
                    $pdfFiles[] = $filePath;
                }
            } catch (\Exception $e) {
                log_message('error', "Batch PDF: Gagal membuat PDF untuk PMO ID {$pmoId}. Error: " . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            log_message('error', 'Batch PDF: Tidak ada PDF yang berhasil dibuat. Membatalkan pembuatan ZIP.');
            $this->cleanup($tempDir);
            session()->setFlashdata('error', 'Gagal membuat file PDF untuk di-zip. Silakan coba lagi.');
            return redirect()->back();
        }

        // Create the ZIP file
        $zipFileName = 'Laporan_PMO_Batch_' . date('Y-m-d_His') . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            $this->cleanup($tempDir);
            session()->setFlashdata('error', 'Gagal membuat file ZIP.');
            return redirect()->back();
        }

        foreach ($pdfFiles as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // Baca konten ZIP ke memori, hapus direktori sementara, lalu kirim file
        $zipData = file_get_contents($zipPath);
        $this->cleanup($tempDir);

        return $this->response->download($zipFileName, $zipData);
    }

    /**
     * Helper method to prepare data for PDF generation.
     *
     * @param integer $id_pmo
     * @return array
     * @throws \Exception
     */
    private function _preparePdfData(int $id_pmo): array
    {
        $result = $this->pmoModel->getPMOWithDetails($id_pmo);

        if (!$result['success']) {
            throw new \RuntimeException($result['message']);
        }

        $pmoData = $result['data']['pmo'];

        // Generate QR codes
        $qr_asesi = !empty($pmoData['ttd_asesi']) ? $this->qrCodeService->generate(
            base_url('/scan-tanda-tangan-asesi/' . $pmoData['ttd_asesi']),
            'logolsp.png'
        ) : '';

        $qr_asesor = !empty($pmoData['ttd_asesor']) ? $this->qrCodeService->generate(
            base_url('/scan-tanda-tangan-asesor/' .  $pmoData['ttd_asesor']),
            'logolsp.png'
        ) : '';

        // Prepare final data for the view
        return [
            'pmo' => $pmoData,
            'struktur' => $result['data']['struktur'],
            'jawaban_list' => $result['data']['jawaban_list'],
            'jenisSertifikasiFormatted' => $this->formatJenisSertifikasi($pmoData['jenis_skema'] ?? null),
            'qr_asesi' => $qr_asesi,
            'qr_asesor' => $qr_asesor
        ];
    }

    private function formatJenisSertifikasi(?string $jenis_skema): string
    {
        if (empty($jenis_skema)) return '-';

        switch ($jenis_skema) {
            case 'KKNI':
                return 'KKNI / <span style="text-decoration: line-through;">Okupasi</span> / <span style="text-decoration: line-through;">Klaster</span>';
            case 'Okupasi':
                return '<span style="text-decoration: line-through;">KKNI</span> / Okupasi / <span style="text-decoration: line-through;">Klaster</span>';
            case 'Klaster':
                return '<span style="text-decoration: line-through;">KKNI</span> / <span style="text-decoration: line-through;">Okupasi</span> / Klaster';
            default:
                return '-';
        }
    }

    private function cleanup(string $dirPath): void
    {
        if (!is_dir($dirPath)) return;

        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dirPath/$file")) ? $this->cleanup("$dirPath/$file") : unlink("$dirPath/$file");
        }
        rmdir($dirPath);
    }
}
