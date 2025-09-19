<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Services\RekamanAsesmenService;
use App\Models\AsesorModel;
use App\Models\SkemaModel;

use App\Models\RekamanAsesmenModel;
use App\Services\PDFService;
use App\Services\QRCodeService;
use ZipArchive;

class RekamanAsesmenController extends ResourceController
{
    protected RekamanAsesmenService $rekamanService;
    protected int $id_asesor;
    protected RekamanAsesmenModel $rekamanModel;
    protected PDFService $pdfService;
    protected QRCodeService $qrCodeService;

    public function __construct()
    {
        helper('auth');
        $this->rekamanService = new RekamanAsesmenService();
        $this->rekamanModel = new RekamanAsesmenModel();
        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();

        // Mengambil ID Asesor dari user yang sedang login
        $asesorModel = new AsesorModel();
        $asesor = $asesorModel->where('id_user', user()->id)->first();
        if (!$asesor) {
            // Sebaiknya ditangani dengan baik, misal redirect atau pesan error
            throw new \RuntimeException('Akses ditolak: Anda bukan seorang asesor.');
        }
        $this->id_asesor = (int)$asesor['id_asesor'];
    }

    /**
     * Menampilkan halaman manajemen Rekaman Asesmen untuk Admin.
     * (Diadaptasi dari PMO)
     */
    public function index()
    {
        $request = service('request');
        $asesorModel = new AsesorModel();
        $skemaModel = new SkemaModel(); // Gunakan model skema

        // Ambil data untuk dropdown filter
        $data['asesor_list'] = $asesorModel->select('asesor.id_asesor, users.nama_lengkap as nama_asesor')
            ->join('users', 'users.id = asesor.id_user')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();

        $data['skema_list'] = $skemaModel->select('id_skema, nama_skema')
            ->orderBy('nama_skema', 'ASC')
            ->findAll();

        // Simpan nilai filter dari URL
        $data['filters'] = [
            'id_asesor'      => $request->getGet('id_asesor'),
            'id_skema'       => $request->getGet('id_skema'),
            'tanggal_dari'   => $request->getGet('tanggal_dari'),
            'tanggal_sampai' => $request->getGet('tanggal_sampai'),
        ];

        $data['siteTitle'] = 'Manajemen Rekaman Asesmen';

        // Pastikan view ini ada: app/Views/admin/rekaman_asesmen_list.php
        return view('admin/rekaman_asesmen_list', $data);
    }

    public function ceklist()
    {
        $asesorModel = model('AsesorModel');
        $asesmenModel = model('AsesmenModel');

        // 1. Ambil data asesor yang sedang login, termasuk skema yang diampu
        $asesorData = $asesorModel->getWithSkema($this->id_asesor);

        // 2. Ambil ID Skema dari data asesor
        $id_skema_asesor = $asesorData['id_skema'] ?? null;

        $asesmenList = [];
        // 3. Hanya cari data asesmen JIKA asesor memiliki skema
        if ($id_skema_asesor) {
            $asesmenList = $asesmenModel
                ->select('asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, skema.nama_skema, skema.kode_skema')
                ->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner') // inner join untuk data valid
                ->where('asesmen.id_skema', $id_skema_asesor) // Filter berdasarkan skema asesor
                ->findAll();
        }

        // 4. Kirim data yang sudah difilter ke view
        $data = [
            'siteTitle' => 'Rekaman Asesmen Kompetensi',
            'asesor'    => $asesorData,
            'asesmen'   => $asesmenList,
        ];

        return view('asesor/rekaman_kompetensi', $data);
    }

    /**
     * API: Mengambil daftar APL1 (asesi) yang sudah divalidasi per asesmen.
     */
    public function getAsesiByAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized();
        }

        $id_asesmen = $this->request->getGet('id_asesmen');
        if (!$id_asesmen) {
            return $this->fail('ID Asesmen diperlukan.', 400);
        }

        $result = $this->rekamanService->getApl1ByAsesmen((int)$id_asesmen);
        return $this->respond($result);
    }

    /**
     * API: Memuat data rekaman (struktur skema & data checklist yang ada).
     */
    public function loadRekamanAsesmen()
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized();
        }

        $id_apl1 = $this->request->getGet('id_apl1');
        if (!$id_apl1) {
            return $this->fail('ID APL1 diperlukan.', 400);
        }

        $result = $this->rekamanService->getRekamanWithDetailsByApl1($id_apl1, $this->id_asesor);
        return $this->respond($result);
    }

    /**
     * Endpoint tunggal untuk menyimpan semua jenis data.
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized();
        }

        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->fail('Invalid JSON data.', 400);
        }

        $id_apl1 = $data['id_apl1'] ?? null;
        if (empty($id_apl1)) {
            return $this->fail('ID APL1 diperlukan.', 400);
        }

        try {
            // Dapatkan atau buat record master
            $rekaman = $this->rekamanService->getOrCreateRekaman($id_apl1, $this->id_asesor);
            if (!$rekaman) {
                return $this->fail('Gagal memproses record rekaman.', 500);
            }
            $id_rekaman = $rekaman['id'];

            $saveType = $data['save_type'] ?? 'full_save';
            $message = '';
            $resultData = ['id_rekaman' => $id_rekaman];

            switch ($saveType) {
                case 'auto_save_unit':
                    $this->rekamanService->saveUnitKompetensi($id_rekaman, $data['id_unit'], [
                        $data['method_key'] => $data['method_value']
                    ]);
                    $message = 'Perubahan disimpan.';
                    break;

                case 'batch_save_units':
                    $this->rekamanService->saveBatchKompetensi($id_rekaman, $data['kompetensi']);
                    $message = 'Semua perubahan berhasil disimpan.';
                    break;

                // --- TAMBAHKAN CASE INI ---
                case 'full_save':
                    $this->rekamanService->saveFinalData($id_rekaman, $data);
                    $message = 'Rekomendasi dan komentar berhasil disimpan.';
                    break;

                    // Anda bisa menambahkan case 'full_save' jika perlu
            }

            return $this->respond([
                'success'   => true,
                'message'   => $message,
                'data'      => $resultData,
                'csrf_hash' => csrf_hash() // Selalu kirim token baru
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * Menghasilkan file PDF untuk satu Rekaman Asesmen.
     */
    public function pdf(int $id = null)
    {
        try {
            if (!$id) {
                throw new \Exception('ID Rekaman tidak valid atau tidak diberikan.');
            }

            // Panggil helper untuk menyiapkan semua data PDF
            $viewData = $this->_preparePdfData($id);

            $this->generatePdf($viewData);
        } catch (\Exception $e) {
            log_message('error', '[RekamanAsesmenController::pdf] Error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Gagal membuat PDF: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Menghasilkan dan mengunduh file ZIP dari beberapa PDF Rekaman Asesmen.
     * (Diadaptasi dari PMO)
     */
    public function batchPdf()
    {
        $rekamanIds = $this->request->getPost('rekaman_ids');

        if (empty($rekamanIds) || !is_array($rekamanIds)) {
            session()->setFlashdata('error', 'Tidak ada ID rekaman yang dipilih atau format tidak valid.');
            return redirect()->back();
        }

        $rekamanIds = array_map('intval', $rekamanIds);
        $tempDir = WRITEPATH . 'uploads/temp_pdf_rekaman_' . uniqid();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $pdfFiles = [];

        foreach ($rekamanIds as $rekamanId) {
            try {
                // Panggil helper untuk menyiapkan data PDF
                $viewData = $this->_preparePdfData($rekamanId);

                // Buat nama file yang aman
                $safeNamaAsesi = preg_replace('/[^a-zA-Z0-9_-]/', '_', $viewData['rekaman']['nama_asesi']);
                $filename = "FR.AK.02_Rekaman_{$safeNamaAsesi}_{$rekamanId}.pdf";
                $filePath = $tempDir . '/' . $filename;

                // Generate dan simpan PDF ke direktori sementara
                $this->pdfService->generateAndSave(['pdf/rekaman_page1', 'pdf/rekaman_page2'], $viewData, $filePath);

                if (file_exists($filePath)) {
                    $pdfFiles[] = $filePath;
                }
            } catch (\Exception $e) {
                log_message('error', "Batch PDF Rekaman: Gagal membuat PDF untuk ID {$rekamanId}. Error: " . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            $this->cleanup($tempDir);
            session()->setFlashdata('error', 'Gagal membuat file PDF untuk di-zip. Silakan coba lagi.');
            return redirect()->back();
        }

        // Buat file ZIP
        $zipFileName = 'Laporan_Rekaman_Asesmen_Batch_' . date('Y-m-d_His') . '.zip';
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

        // Baca konten ZIP, hapus direktori sementara, lalu kirimkan file untuk diunduh
        $zipData = file_get_contents($zipPath);
        $this->cleanup($tempDir);

        return $this->response->download($zipFileName, $zipData);
    }

    /**
     * Helper privat untuk menyiapkan data PDF.
     * (Diadaptasi dari PMO)
     */
    private function _preparePdfData(int $id): array
    {
        $result = $this->rekamanModel->getRekamanForPDF($id);
        if (empty($result) || !$result['success']) {
            throw new \Exception($result['message'] ?? 'Data untuk pembuatan PDF tidak ditemukan.');
        }

        $viewData = $result['data'];

        // Generate QR Code jika ada tanda tangan
        if (!empty($viewData['rekaman']['tanda_tangan_asesi'])) {
            $viewData['qr_asesi'] = $this->qrCodeService->generate(
                base_url('/scan/validasi/asesi/' . $viewData['rekaman']['tanda_tangan_asesi'])
            );
        }

        return $viewData;
    }

    /**
     * Helper privat untuk memanggil PDF service.
     */
    private function generatePdf(array $data): void
    {
        $views = ['pdf/rekaman_page1', 'pdf/rekaman_page2'];
        $namaFile = "FR.AK.02 - REKAMAN ASESMEN - " . strtoupper($data['rekaman']['nama_asesi'] ?? 'Asesi');
        $this->pdfService->generateMultiPagePdf($views, $data, $namaFile);
    }

    /**
     * Helper untuk membersihkan direktori sementara.
     * (Diadaptasi dari PMO)
     */
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
