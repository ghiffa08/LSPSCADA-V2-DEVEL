<?php

namespace App\Controllers\Api;

use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;
use App\Services\ObservasiService;

/**
 * Observasi API Controller - OPTIMIZED for APL1
 * 
 * Enhanced controller dengan service layer pattern,
 * caching, validation, dan error handling yang lebih baik
 * Updated for simplified APL1 table structure
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

        // Updated column mapping for APL1 schema
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'apl1.nama_siswa', // Changed from asesi_user.nama_lengkap
            2 => 'asesor_user.nama_lengkap',
            3 => 'skema.nama_skema',
            4 => 'observasi.tanggal_observasi',
            5 => 'tuk.nama_tuk',
            6 => null // No ordering for action column
        ];
    }

    /**
     * Get skema details and available asesi - OPTIMIZED for APL1
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

            // Get validated APL1 list untuk skema ini
            $apl1List = $this->observasiService->getValidatedApl1BySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'apl1_list' => $apl1List, // Changed from 'asesi'
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
     * Load observation data via AJAX - Updated for APL1
     * Always return fresh data from database
     */
    public function loadObservasi()
    {
        $startTime = microtime(true);

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak valid'
            ])->setStatusCode(400);
        }

        // Add no-cache headers to prevent browser caching
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        $id_skema = $this->request->getGet('id_skema');
        $id_apl1 = $this->request->getGet('id_apl1'); // Changed from id_asesi
        $id_asesmen = $this->request->getGet('id_asesmen');

        // Enhanced validation
        if (!$id_skema || !filter_var($id_skema, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Skema diperlukan dan harus berupa angka', 400);
        }

        if (!$id_apl1 || empty(trim($id_apl1))) {
            return $this->fail('ID APL1 diperlukan', 400);
        }

        try {
            // Check if observasi already exists for this APL1
            $existingObservasi = $this->db->table('observasi o')
                ->select('o.id_observasi, o.tanggal_observasi')
                ->join('apl1', 'apl1.id_apl1 = o.id_apl1', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen', 'inner')
                ->where('o.id_apl1', $id_apl1)
                ->where('o.id_asesor', $this->id_asesor)
                ->where('asm.id_skema', $id_skema)
                ->orderBy('o.created_at', 'DESC')
                ->get()
                ->getRowArray();

            $id_observasi = $existingObservasi['id_observasi'] ?? null;

            // Get KUK structure dengan service yang sudah diperbaiki untuk APL1
            $result = $this->observasiService->getKukStructureForSchema(
                $id_skema,
                $id_apl1, // Changed from id_asesi
                $id_observasi
            );

            if (!$result['success']) {
                return $this->fail($result['message']);
            }

            // Performance tracking
            $duration = microtime(true) - $startTime;
            if ($duration > 2.0) {
                log_message('warning', "Slow loadObservasi query: {$duration}s");
            }

            return $this->respond([
                'success' => true,
                'observasi' => $result['observasi'],
                'existing_data' => $result['existing_data'],
                'totalKUK' => $result['totalKUK'],
                'id_observasi' => $id_observasi,
                'debug' => [
                    'id_apl1' => $id_apl1,
                    'id_skema' => $id_skema,
                    'id_asesmen' => $id_asesmen,
                    'existing_observasi' => $id_observasi ? 'found' : 'not_found'
                ],
                'performance' => [
                    'duration_seconds' => round($duration, 3),
                    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading observasi: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->fail('Gagal memuat data observasi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Universal handler for all observation saves - ENHANCED for APL1
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
     * Save observasi settings (master data only) - Updated for APL1
     */
    private function saveSettings($postData, $jsonData): ResponseInterface
    {
        $data = array_merge($postData, $jsonData ?? []);
        $data['id_asesor'] = $this->id_asesor;

        // Validate required fields for APL1
        $required = ['id_apl1', 'tanggal_observasi']; // Changed from id_asesi
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        // Validate APL1 exists and is validated
        $apl1 = $this->db->table('apl1')
            ->where('id_apl1', $data['id_apl1'])
            ->where('validasi_apl1', 'validated')
            ->get()
            ->getRowArray();

        if (!$apl1) {
            return $this->fail("APL1 tidak ditemukan atau belum tervalidasi");
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
            $cache->delete("observasi_*");
            $cache->delete("kuk_structure_*");

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
            $cache->delete("observasi_*");
            $cache->delete("kuk_structure_*");

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
     * Save full observasi dengan details - Updated for APL1
     */
    private function saveFullObservasi($postData, $jsonData): ResponseInterface
    {
        $data = array_merge($postData, $jsonData ?? []);
        $data['id_asesor'] = $this->id_asesor;

        // Validate required fields for APL1
        $required = ['id_apl1', 'tanggal_observasi']; // Changed from id_asesi
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->fail("Field {$field} diperlukan");
            }
        }

        // Validate APL1 exists and is validated
        $apl1 = $this->db->table('apl1')
            ->where('id_apl1', $data['id_apl1'])
            ->where('validasi_apl1', 'validated')
            ->get()
            ->getRowArray();

        if (!$apl1) {
            return $this->fail("APL1 tidak ditemukan atau belum tervalidasi");
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

    /**
     * Get APL1 details by ID - New method for APL1
     */
    public function getApl1Details()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_apl1 = $this->request->getGet('id_apl1');

        if (!$id_apl1 || empty(trim($id_apl1))) {
            return $this->fail('ID APL1 diperlukan', 400);
        }

        try {
            $apl1Data = $this->observasiModel->getApl1Data($id_apl1);

            if (!$apl1Data) {
                return $this->fail('Data APL1 tidak ditemukan', 404);
            }

            if ($apl1Data['validasi_apl1'] !== 'validated') {
                return $this->fail('APL1 belum tervalidasi', 400);
            }

            return $this->respond([
                'success' => true,
                'data' => $apl1Data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting APL1 details: ' . $e->getMessage());
            return $this->fail('Gagal memuat detail APL1: ' . $e->getMessage());
        }
    }

    /**
     * Check existing observation for APL1 - New method
     */
    public function checkExistingObservation()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_apl1 = $this->request->getGet('id_apl1');

        if (!$id_apl1 || empty(trim($id_apl1))) {
            return $this->fail('ID APL1 diperlukan', 400);
        }

        try {
            $existingObservation = $this->observasiModel->checkExistingObservation(
                $id_apl1,
                $this->id_asesor
            );

            return $this->respond([
                'success' => true,
                'existing' => $existingObservation !== null,
                'data' => $existingObservation
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error checking existing observation: ' . $e->getMessage());
            return $this->fail('Gagal mengecek observasi yang ada: ' . $e->getMessage());
        }
    }

    /**
     * Debug method to check available methods
     */
    public function debugMethods()
    {
        $methods = get_class_methods($this);
        return $this->respond([
            'controller' => get_class($this),
            'methods' => $methods,
            'request_uri' => $this->request->getUri()->getPath()
        ]);
    }

    /**
     * Get validated APL1 list for specific schema - New method
     */
    public function getValidatedApl1List()
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
            $apl1List = $this->observasiModel->getValidatedApl1BySkema($id_skema);

            return $this->respond([
                'success' => true,
                'data' => $apl1List,
                'count' => count($apl1List),
                'debug' => [
                    'method' => __METHOD__,
                    'id_skema' => $id_skema,
                    'controller' => get_class($this)
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting validated APL1 list: ' . $e->getMessage());
            return $this->fail('Gagal memuat daftar APL1: ' . $e->getMessage());
        }
    }

    /**
     * Get validated APL1 by skema - Alias method for backward compatibility
     */
    public function getValidatedApl1BySkema()
    {
        return $this->getValidatedApl1List();
    }
}
