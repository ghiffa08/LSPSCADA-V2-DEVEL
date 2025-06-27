<?php

namespace App\Controllers\Api;

use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;
use App\Services\ObservasiService;

/**
 * Observasi API Controller - OPTIMIZED
 * 
 * Enhanced controller dengan service layer pattern,
 * caching, validation, dan error handling yang lebih baik
 */
class Observasi extends DataTableController
{
    private $id_asesor;
    private ObservasiService $observasiService;
    protected $db;

    public function __construct()
    {
        parent::__construct();

        helper('auth');

        $this->model = $this->observasiModel;
        $this->observasiService = new ObservasiService();
        $this->db = Database::connect();

        // Get id_asesor from asesor table, not directly from user
        $user_id = user()->id;
        $asesorModel = new \App\Models\AsesorModel();
        $asesor = $asesorModel->where('id_user', $user_id)->first();

        if (!$asesor) {
            throw new \RuntimeException('User is not registered as asesor');
        }

        $this->id_asesor = $asesor['id_asesor'];

        // Updated column mapping for new schema
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'asesi_user.nama_lengkap',
            2 => 'asesor_user.nama_lengkap',
            3 => 'skema.nama_skema',
            4 => 'observasi.tanggal_observasi',
            5 => null // No ordering for action column
        ];
    }

    /**
     * Get skema details and available asesi - OPTIMIZED
     */
    public function getSkemaDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_skema = $this->request->getGet('id_skema');

        if (!$id_skema || !filter_var($id_skema, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Skema diperlukan dan harus berupa angka', 400);
        }

        try {
            // Validate skema exists dan asesor memiliki akses
            $skema = $this->skemaModel->find($id_skema);
            if (!$skema) {
                return $this->fail('Skema tidak ditemukan', 404);
            }

            // Get asesi list menggunakan service dengan caching
            $asesi = $this->observasiService->getAsesiBySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'asesi' => $asesi,
                'cache_info' => [
                    'cached' => true,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema details: ' . $e->getMessage());
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
    /**
     * Load observation data via AJAX - NO CACHE
     * Always return fresh data from database
     */
    public function loadObservasi()
    {
        $startTime = microtime(true);

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        // Add no-cache headers to prevent browser caching
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        $id_skema = $this->request->getGet('id_skema');
        $id_asesi = $this->request->getGet('id_asesi');
        $id_asesmen = $this->request->getGet('id_asesmen');

        // Enhanced validation
        if (!$id_skema || !filter_var($id_skema, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Skema diperlukan dan harus berupa angka', 400);
        }

        if (!$id_asesi || empty(trim($id_asesi))) {
            return $this->fail('ID Asesi diperlukan', 400);
        }

        try {
            // CLEAR ANY EXISTING CACHE for this data
            $cache = \Config\Services::cache();
            $cache->deleteMatching("observasi_{$id_asesmen}_{$id_asesi}_*");
            $cache->deleteMatching("kuk_structure_{$id_skema}_*");

            // Get fresh observasi structure from service
            $result = $this->observasiService->getKukStructureForSchema($id_skema, $id_asesi);

            if (!$result['success']) {
                return $this->fail($result['message']);
            }

            $structureData = $result['data'];

            return $this->respond([
                'success' => true,
                'observasi' => $structureData['structure'],
                'existing_data' => $structureData['existingData'],
                'totalKUK' => $structureData['totalKUK'],
                'performance' => [
                    'cached' => false, // Always fresh data
                    'load_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading observasi data: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Universal handler for all observation saves - ENHANCED
     * Enhanced dengan service layer, validation, dan error handling
     */
    public function save()
    {
        // Security check
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        // Get request data
        $rawInput = $this->request->getBody();
        $jsonData = json_decode($rawInput, true);
        $postData = $this->request->getPost();

        // Determine request type
        $requestType = $postData['save_type'] ?? ($jsonData['save_type'] ?? 'full');

        try {
            switch ($requestType) {
                case 'settings':
                    return $this->saveSettings($postData, $jsonData);

                case 'kuk':
                    return $this->saveSingleKUK($postData, $jsonData);

                case 'batch':
                    return $this->saveBatchKUK($postData, $jsonData);

                case 'full':
                default:
                    return $this->saveFullObservasi($postData, $jsonData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in observasi save: ' . $e->getMessage());
            return $this->fail('Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Save observasi settings (master data only)
     */
    private function saveSettings($postData, $jsonData): ResponseInterface
    {
        $data = array_merge($postData, $jsonData ?? []);
        $data['id_asesor'] = $this->id_asesor;

        // Auto-get pengajuan if not provided but asesi is available
        if (empty($data['id_pengajuan']) && !empty($data['id_asesi'])) {
            $pengajuan = $this->db->table('pengajuan_asesmen')
                ->where('id_asesi', $data['id_asesi'])
                ->orderBy('tanggal_pengajuan', 'DESC')
                ->get()
                ->getRow();

            if ($pengajuan) {
                $data['id_pengajuan'] = $pengajuan->id_pengajuan;
            }
        }

        // Validate required fields
        $required = ['id_asesi', 'tanggal_observasi'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        // Validate id_pengajuan separately with better error message
        if (empty($data['id_pengajuan'])) {
            return $this->fail("ID Pengajuan diperlukan. Pastikan asesi memiliki pengajuan asesmen yang valid.");
        }

        // Log input data for debugging
        log_message('info', 'ObservasiController saveSettings input: ' . json_encode($data));

        // Use service to save
        $result = $this->observasiService->saveObservation($data);

        // Log the result for debugging
        log_message('info', 'ObservasiController saveSettings result: ' . json_encode($result));

        if ($result['success']) {
            return $this->respond([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
                'token' => csrf_hash()
            ]);
        }

        // Log the error details for debugging
        log_message('error', 'ObservasiController saveSettings failed: ' . $result['message']);
        return $this->fail($result['message']);
    }

    /**
     * Save single KUK
     */
    private function saveSingleKUK($postData, $jsonData): ResponseInterface
    {
        $data = array_merge($postData, $jsonData ?? []);

        // Validate required fields
        $required = ['id_observasi', 'id_kuk', 'kompeten'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        // Validate data types
        if (!filter_var($data['id_observasi'], FILTER_VALIDATE_INT)) {
            return $this->fail('ID Observasi tidak valid');
        }

        if (!filter_var($data['id_kuk'], FILTER_VALIDATE_INT)) {
            return $this->fail('ID KUK tidak valid');
        }

        if (!in_array($data['kompeten'], ['Y', 'N'])) {
            return $this->fail('Nilai kompeten harus Y atau N');
        }

        // Use service to save
        $result = $this->observasiService->saveSingleKUK(
            (int)$data['id_observasi'],
            (int)$data['id_kuk'],
            [
                'kompeten' => $data['kompeten'],
                'keterangan' => $data['keterangan'] ?? ''
            ]
        );

        if ($result['success']) {
            // CLEAR CACHE after successful save
            $cache = \Config\Services::cache();
            $cache->deleteMatching("observasi_*");
            $cache->deleteMatching("kuk_structure_*");

            return $this->respond([
                'success' => true,
                'message' => $result['message'],
                'token' => csrf_hash()
            ]);
        }

        return $this->fail($result['message']);
    }

    /**
     * Save batch KUK
     */
    private function saveBatchKUK($postData, $jsonData): ResponseInterface
    {
        $data = $jsonData ?? $postData;

        // Validate required fields
        if (empty($data['id_observasi']) || empty($data['items'])) {
            return $this->fail('ID Observasi dan items diperlukan');
        }

        if (!filter_var($data['id_observasi'], FILTER_VALIDATE_INT)) {
            return $this->fail('ID Observasi tidak valid');
        }

        if (!is_array($data['items'])) {
            return $this->fail('Items harus berupa array');
        }

        // Use service to save batch
        $result = $this->observasiService->batchSaveKUK(
            (int)$data['id_observasi'],
            $data['items']
        );

        if ($result['success']) {
            // CLEAR CACHE after successful batch save
            $cache = \Config\Services::cache();
            $cache->deleteMatching("observasi_*");
            $cache->deleteMatching("kuk_structure_*");

            return $this->respond([
                'success' => true,
                'message' => $result['message'],
                'processed' => $result['processed'] ?? 0,
                'token' => csrf_hash()
            ]);
        }

        return $this->fail($result['message']);
    }

    /**
     * Save full observasi dengan details
     */
    private function saveFullObservasi($postData, $jsonData): ResponseInterface
    {
        $data = array_merge($postData, $jsonData ?? []);
        $data['id_asesor'] = $this->id_asesor;

        // Auto-get pengajuan if not provided but asesi is available
        if (empty($data['id_pengajuan']) && !empty($data['id_asesi'])) {
            $pengajuan = $this->db->table('pengajuan_asesmen')
                ->where('id_asesi', $data['id_asesi'])
                ->orderBy('tanggal_pengajuan', 'DESC')
                ->get()
                ->getRow();

            if ($pengajuan) {
                $data['id_pengajuan'] = $pengajuan->id_pengajuan;
            }
        }

        // Validate required fields
        $required = ['id_asesi', 'tanggal_observasi'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        // Validate id_pengajuan separately with better error message
        if (empty($data['id_pengajuan'])) {
            return $this->fail("ID Pengajuan diperlukan. Pastikan asesi memiliki pengajuan asesmen yang valid.");
        }

        // Prepare details if available
        if (isset($data['kuk']) && is_array($data['kuk'])) {
            $details = [];
            foreach ($data['kuk'] as $id_kuk => $kompeten) {
                if ($kompeten !== '') {
                    $details[] = [
                        'id_kuk' => $id_kuk,
                        'kompeten' => $kompeten,
                        'keterangan' => $data['keterangan'][$id_kuk] ?? '',
                        'tanggal_observasi' => $data['tanggal_observasi'] ?? date('Y-m-d')
                    ];
                }
            }
            $data['details'] = $details;
        }

        // Use service to save
        $result = $this->observasiService->saveObservation($data);

        if ($result['success']) {
            return $this->respond([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
                'token' => csrf_hash()
            ]);
        }

        return $this->fail($result['message']);
    }

    /**
     * Get observasi statistics untuk dashboard
     */
    public function getStatistics()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized'
            ])->setStatusCode(401);
        }

        try {
            $stats = $this->observasiService->getAsesorObservasiStats($this->id_asesor);

            return $this->respond([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return $this->fail('Gagal mengambil statistik: ' . $e->getMessage());
        }
    }

    /**
     * Delete observasi
     */
    public function deleteObservasi($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Observasi tidak valid');
        }

        try {
            // Verify ownership
            $observasi = $this->db->table('observasi')
                ->where('id_observasi', $id)
                ->where('id_asesor', $this->id_asesor)
                ->get()
                ->getRowArray();

            if (!$observasi) {
                return $this->fail('Observasi tidak ditemukan atau tidak memiliki akses');
            }

            $result = $this->observasiService->deleteObservasi((int)$id);

            if ($result['success']) {
                return $this->respond([
                    'success' => true,
                    'message' => $result['message']
                ]);
            }

            return $this->fail($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting observasi: ' . $e->getMessage());
            return $this->fail('Gagal menghapus observasi: ' . $e->getMessage());
        }
    }

    /**
     * Get progress report
     */
    public function getProgressReport()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');

            $result = $this->observasiService->getProgressReport(
                $this->id_asesor,
                $dateFrom,
                $dateTo
            );

            if ($result['success']) {
                return $this->respond([
                    'success' => true,
                    'data' => $result['data']
                ]);
            }

            return $this->fail($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'Error getting progress report: ' . $e->getMessage());
            return $this->fail('Gagal mengambil laporan progress: ' . $e->getMessage());
        }
    }
}
