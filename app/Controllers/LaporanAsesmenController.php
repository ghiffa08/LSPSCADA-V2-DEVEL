<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LaporanAsesmenModel;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\HTTP\ResponseInterface;
use ZipArchive;
use CodeIgniter\API\ResponseTrait;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class LaporanAsesmenController extends BaseController
{
    use ResponseTrait;

    protected LaporanAsesmenModel $laporanModel;
    protected PDFService $pdfService;
    protected $qrCodeService;

    public function __construct()
    {
        helper('auth');

        $this->laporanModel = new LaporanAsesmenModel();
        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();
    }

    /**
     * Halaman utama laporan dengan filter
     */
    public function index()
    {
        $filters = [
            'id_asesor' => $this->request->getGet('id_asesor'),
            'id_skema' => $this->request->getGet('id_skema'),
            'tanggal_dari' => $this->request->getGet('tanggal_dari'),
            'tanggal_sampai' => $this->request->getGet('tanggal_sampai')
        ];

        $data = [
            'siteTitle' => 'Daftar Laporan Asesmen',
            'laporan_list' => $this->laporanModel->getLaporanList($filters),
            'asesor_list' => $this->laporanModel->getAsesorList(),
            'skema_list' => $this->laporanModel->getSkemaList(),
            'filters' => $filters
        ];

        return view('asesor/laporan_asesmen', $data);
    }


    /**
     * Generate laporan PDF (tanpa parameter - semua data)
     */
    public function generateLaporan($id_asesor)
    {
        try {
            // Get filters dari request
            $filters = [
                'id_asesor' => $id_asesor, // Use the ID from the URL
                'id_skema' => $this->request->getGet('id_skema'),
                'rekomendasi' => $this->request->getGet('rekomendasi'),
                'tanggal_dari' => $this->request->getGet('tanggal_dari'),
                'tanggal_sampai' => $this->request->getGet('tanggal_sampai')
            ];

            // Remove empty filters
            $filters = array_filter($filters);

            // Prepare data for the PDF view
            $viewData = $this->_preparePdfData($filters);

            if (empty($viewData['laporan_data'])) {
                return $this->fail('Tidak ada data laporan yang ditemukan', 404);
            }

            // Generate PDF and stream it
            $filename = "Laporan_Asesmen_" . date('Y_m_d_His') . ".pdf";
            $this->pdfService->generateMultiPagePdf(
                ['pdf/laporan_lengkap'],
                $viewData,
                $filename
            );

            log_message('info', "Laporan asesmen lengkap generated with " . count($viewData['laporan_data']) . " records");
        } catch (\Exception $e) {
            log_message('error', 'Generate laporan error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->fail('Gagal generate laporan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate and download a ZIP file of multiple Laporan Asesmen PDFs.
     */
    public function batchPdf()
    {
        $laporanData = $this->request->getPost('laporan_data');

        if (empty($laporanData) || !is_array($laporanData)) {
            session()->setFlashdata('error', 'Tidak ada laporan yang dipilih atau format tidak valid.');
            return redirect()->back();
        }

        // Create a unique temporary directory for this job's PDFs
        $tempDir = WRITEPATH . 'uploads/temp_pdf_laporan_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $pdfFiles = [];

        foreach ($laporanData as $report) {
            try {
                // Build filters for this specific report
                $filters = [
                    'id_asesor' => $report['id_asesor'],
                    'id_skema' => $report['id_skema'],
                    'tanggal_dari' => $report['tanggal_rekaman'],
                    'tanggal_sampai' => $report['tanggal_rekaman'],
                ];
                $filters = array_filter($filters);

                // Prepare data for the PDF
                $viewData = $this->_preparePdfData($filters);

                if (empty($viewData['laporan_data'])) {
                    log_message('warning', "Batch PDF: No data found for filters " . json_encode($filters));
                    continue;
                }

                // Sanitize filename
                $safeNamaAsesor = preg_replace('/[^a-zA-Z0-9_-]/', '_', $viewData['general_info']['nama_asesor']);
                $safeNamaSkema = preg_replace('/[^a-zA-Z0-9_-]/', '_', $viewData['general_info']['nama_skema']);
                $tanggal = date('Ymd', strtotime($report['tanggal_rekaman']));
                $filename = "Laporan_{$safeNamaAsesor}_{$safeNamaSkema}_{$tanggal}.pdf";
                $filePath = $tempDir . '/' . $filename;

                // Generate and save the PDF to the temp directory
                $this->pdfService->generateAndSave(['pdf/laporan_lengkap'], $viewData, $filePath);

                if (file_exists($filePath)) {
                    $pdfFiles[] = $filePath;
                }
            } catch (\Exception $e) {
                log_message('error', "Batch PDF: Gagal membuat PDF untuk laporan. Error: " . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            log_message('error', 'Batch PDF: Tidak ada PDF yang berhasil dibuat. Membatalkan pembuatan ZIP.');
            $this->cleanup($tempDir);
            session()->setFlashdata('error', 'Gagal membuat file PDF untuk di-zip. Silakan coba lagi.');
            return redirect()->back();
        }

        // Create the ZIP file
        $zipFileName = 'Batch_Laporan_Asesmen_' . date('Y-m-d_His') . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->cleanup($tempDir);
            session()->setFlashdata('error', 'Gagal membuat file ZIP.');
            return redirect()->back();
        }

        foreach ($pdfFiles as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // Read ZIP content to memory, clean up temp dir, then send the file
        $zipData = file_get_contents($zipPath);
        $this->cleanup($tempDir);

        return $this->response->download($zipFileName, $zipData);
    }

    /**
     * Helper method to prepare data for PDF generation.
     *
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    private function _preparePdfData(array $filters): array
    {
        log_message('info', 'Preparing PDF data with filters: ' . json_encode($filters));

        // Get all laporan data based on filters
        $laporanData = $this->laporanModel->getAllLaporanData($filters['id_asesor'], $filters);

        if (empty($laporanData)) {
            return ['laporan_data' => []]; // Return empty if no data
        }

        // Get general info for header
        $generalInfo = $this->laporanModel->getGeneralInfo($filters);

        // Format jenis skema based on type
        switch ($generalInfo['jenis_skema']) {
            case 'KKNI':
                $generalInfo['jenis_skema'] = 'KKNI / <span style="text-decoration: line-through;">Okupasi</span>/<span style="text-decoration: line-through;">Klaster</span>';
                break;
            case 'Okupasi':
                $generalInfo['jenis_skema'] = '<span style="text-decoration: line-through;">KKNI</span>/Okupasi/<span style="text-decoration: line-through;">Klaster</span>';
                break;
            default: // For Klaster or any other type
                $generalInfo['jenis_skema'] = '<span style="text-decoration: line-through;">KKNI</span>/<span style="text-decoration: line-through;">Okupasi</span>/Klaster';
                break;
        }

        $writer = new PngWriter();
        $logo = Logo::create('logolsp.png')->setResizeToWidth(50);
        $ttd_asesor = QrCode::create(base_url('/scan-tanda-tangan-asesor/'))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(200)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $result = $writer->write($ttd_asesor, $logo);
        $generalInfo['tanda_tangan_asesor'] = $result->getDataUri();

        // Get statistik
        $statistik = $this->laporanModel->getLaporanStatistik($filters);

        // Prepare data for view
        return [
            'laporan_data' => $laporanData,
            'general_info' => $generalInfo,
            'statistik' => $statistik,
            'filters' => $filters,
            'qr_code_path' => null,
            'generated_date' => date('Y-m-d H:i:s'),
            'total_asesi' => count($laporanData)
        ];
    }

    /**
     * Helper method to clean up temporary directories.
     */
    private function cleanup(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }

        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dirPath/$file")) ? $this->cleanup("$dirPath/$file") : unlink("$dirPath/$file");
        }
        rmdir($dirPath);
    }
}
