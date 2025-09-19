<?php

namespace App\Jobs;

use App\Models\PMOModel;
use App\Services\PDFService;
use App\Services\QRCodeService;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use ZipArchive;

class GeneratePmoZipJob extends BaseJob implements JobInterface
{
    public function process()
    {
        $pmoIds = $this->data['pmo_ids'];
        $userId = $this->data['user_id']; // For potential future use (notifications, user-specific folders)

        $pdfService = new PDFService();
        $pmoModel = new PMOModel();
        $qrCodeService = new QRCodeService();

        // Create a unique temporary directory for this job's PDFs
        $tempDir = WRITEPATH . 'uploads/temp_pdf_pmo_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $pdfFiles = [];

        foreach ($pmoIds as $pmoId) {
            try {
                $result = $pmoModel->getPMOWithDetails((int)$pmoId);
                if (!$result['success']) {
                    log_message('error', "Job: Gagal mendapatkan detail untuk PMO ID {$pmoId}. Pesan: " . $result['message']);
                    continue; // Skip to the next ID
                }

                $pmoData = $result['data']['pmo'];

                // Generate QR codes
                $qr_asesi = !empty($pmoData['ttd_asesi']) ? $qrCodeService->generate(base_url('/scan-tanda-tangan-asesi/' . $pmoData['ttd_asesi']), 'logolsp.png') : '';
                $qr_asesor = !empty($pmoData['ttd_asesor']) ? $qrCodeService->generate(base_url('/scan-tanda-tangan-asesor/' . $pmoData['ttd_asesor']), 'logolsp.png') : '';

                $viewData = [
                    'pmo' => $pmoData,
                    'struktur' => $result['data']['struktur'],
                    'jawaban_list' => $result['data']['jawaban_list'],
                    'jenisSertifikasiFormatted' => $this->formatJenisSertifikasi($pmoData['jenis_skema'] ?? null),
                    'qr_asesi' => $qr_asesi,
                    'qr_asesor' => $qr_asesor,
                ];

                // Sanitize filename
                $safeNamaAsesi = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pmoData['nama_asesi']);
                $filename = "FR.IA.03_PMO_{$safeNamaAsesi}_{$pmoId}.pdf";
                $filePath = $tempDir . '/' . $filename;

                // Generate and save the PDF to the temp directory
                $pdfService->generateAndSave(['pdf/pmo_page1'], $viewData, $filePath);

                if (file_exists($filePath)) {
                    $pdfFiles[] = $filePath;
                }
            } catch (\Exception $e) {
                log_message('error', "Job: Gagal membuat PDF untuk PMO ID {$pmoId}. Error: " . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            log_message('error', 'Job: Tidak ada PDF yang berhasil dibuat. Membatalkan pembuatan ZIP.');
            $this->cleanup($tempDir);
            return;
        }

        // Create the ZIP file in a public-accessible (or controlled-access) directory
        $zipFileName = 'Laporan_PMO_Batch_' . date('Y-m-d_His') . '.zip';
        $downloadsDir = WRITEPATH . 'downloads';
        if (!is_dir($downloadsDir)) {
            mkdir($downloadsDir, 0777, true);
        }
        $zipPath = $downloadsDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($pdfFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            log_message('info', "Job: Berhasil membuat file ZIP di: {$zipPath}");
        } else {
            log_message('error', "Job: Gagal membuat file ZIP.");
        }

        // Clean up the temporary PDF files and directory
        $this->cleanup($tempDir);
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
