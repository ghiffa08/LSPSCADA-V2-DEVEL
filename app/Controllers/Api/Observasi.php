<?php

namespace App\Controllers\Api;

use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;

class Observasi extends DataTableController
{
    private $id_asesor;

    public function __construct()
    {
        parent::__construct();

        $this->model = $this->observasiModel;

        $this->id_asesor = user()->id;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'asesi_user.fullname',
            2 => 'asesor.fullname',
            3 => 'skema.nama_skema',
            4 => 'tuk.nama_tuk',
            5 => 'observasi.tanggal_observasi',
            6 => null // No ordering for action column
        ];
    }

    /**
     * Get skema details and available asesi
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

        if (!$id_skema) {
            return $this->fail('ID Skema diperlukan', 400);
        }

        try {
            // Get skema details
            $skema = $this->skemaModel->find($id_skema);

            if (!$skema) {
                return $this->fail('Skema tidak ditemukan', 404);
            }

            // Get asesi list for this skema
            $asesi = $this->observasiModel->getAsesiBySkema($id_skema);

            return $this->respond([
                'success' => true,
                'skema' => $skema,
                'asesi' => $asesi
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema details: ' . $e->getMessage());
            return $this->fail('Gagal memuat detail skema: ' . $e->getMessage());
        }
    }

    /**
   
     * Load observation data via AJAX
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function loadObservasi()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 401,
                'error' => 'Unauthorized: Direct access not allowed'
            ])->setStatusCode(401);
        }

        $id_skema = $this->request->getGet('id_skema');
        $id_asesi = $this->request->getGet('id_asesi');

        if (!$id_skema || !$id_asesi) {
            return $this->fail('ID Skema dan ID Asesi diperlukan', 400);
        }

        try {
            // Fetch data for both AJAX response and PDF generation
            $data = $this->getObservasiData($id_skema, $id_asesi);

            // Count total KUK for progress bar
            $totalKUK = 0;
            foreach ($data['detailObservasi'] as $row) {
                if (!empty($row['id_kuk'])) {
                    $totalKUK++;
                }
            }

            $data['totalKUK'] = $totalKUK;

            return $this->respond([
                'success' => true,
                'observasi' => $data['detailObservasi'],
                'existing_data' => $data['existing_data'],
                'totalKUK' => $totalKUK
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error loading observasi data: ' . $e->getMessage());
            return $this->fail('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Universal handler for all observation saves
     * This single endpoint handles settings, bulk save, single KUK and batch saves
     */
    public function save()
    {
        // Determine request type
        $isAjax = $this->request->isAJAX();

        // Get the raw input for JSON processing
        $rawInput = $this->request->getBody();
        $jsonData = json_decode($rawInput, true);

        // Determine request type from either POST or JSON
        $requestType = $this->request->getPost('save_type') ?? ($jsonData['save_type'] ?? 'full');

        // Common data collection - check both POST and JSON
        $id_asesi = $this->request->getPost('id_asesi') ?? ($jsonData['id_asesi'] ?? null);
        $id_skema = $this->request->getPost('id_skema') ?? ($jsonData['id_skema'] ?? null);
        $id_asesmen = $this->request->getPost('id_asesmen') ?? ($jsonData['id_asesmen'] ?? null);
        $tanggal_observasi = $this->request->getPost('tanggal_observasi') ?? ($jsonData['tanggal_observasi'] ?? date('Y-m-d'));
        $id_asesor = $this->request->getPost('id_asesor') ?? ($jsonData['id_asesor'] ?? $this->id_asesor);

        // Validate common required fields
        if (empty($id_asesi)) {
            return $this->handleErrorResponse('ID Asesi diperlukan', $isAjax);
        }

        try {
            // Get APL1 ID from pengajuan_asesmen
            $db = Database::connect();
            $pengajuan = $db->table('pengajuan_asesmen')
                ->select('id_apl1')
                ->where('id_asesi', $id_asesi)
                ->where('id_asesmen', $id_asesmen)
                ->where('deleted_at', null)
                ->get()
                ->getRow();

            $id_apl1 = $pengajuan->id_apl1 ?? '0';

            // Check for existing observation
            $observasi = null;
            if (!empty($id_asesi) && !empty($tanggal_observasi)) {
                $observasi = $db->table('observasi')
                    ->where('id_asesi', $id_asesi)
                    ->where('tanggal_observasi', $tanggal_observasi)
                    ->get()
                    ->getRow();
            }

            $id_observasi = $this->request->getPost('id_observasi') ?? ($observasi->id_observasi ?? null);

            // Prepare master data
            $masterData = [
                'id_observasi' => $id_observasi,
                'id_asesor' => $id_asesor,
                'id_asesi' => $id_asesi,
                'id_apl1' => $id_apl1,
                'tanggal_observasi' => $tanggal_observasi
            ];

            // Process based on request type
            $detailData = null;
            $singleKUK = false;

            switch ($requestType) {
                case 'settings':
                    // Only master data, no details
                    break;

                case 'kuk':
                    // Single KUK save
                    $id_skema = $this->request->getPost('id_skema');
                    $id_kuk = $this->request->getPost('id_kuk');
                    $kompeten = $this->request->getPost('kompeten');
                    $keterangan = $this->request->getPost('keterangan');

                    if (empty($id_skema) || empty($id_kuk) || empty($kompeten)) {
                        return $this->handleErrorResponse('Semua field KUK diperlukan', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $id_skema,
                        'id_kuk' => $id_kuk,
                        'kompeten' => $kompeten,
                        'keterangan' => $keterangan,
                        'tanggal_observasi' => $tanggal_observasi
                    ];
                    $singleKUK = true;
                    break;

                case 'batch':
                    // Batch KUK save from JSON
                    if (empty($jsonData['id_skema']) || empty($jsonData['items'])) {
                        return $this->handleErrorResponse('Data batch tidak lengkap', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $jsonData['id_skema'],
                        'items' => $jsonData['items']
                    ];
                    break;

                case 'full':
                default:
                    // Full form submission
                    $id_skema = $this->request->getPost('id_skema');
                    $kuk = $this->request->getPost('kuk') ?? [];
                    $keterangan = $this->request->getPost('keterangan') ?? [];

                    if (empty($id_skema)) {
                        return $this->handleErrorResponse('ID Skema diperlukan', $isAjax);
                    }

                    $detailData = [
                        'id_skema' => $id_skema,
                        'kuk' => $kuk,
                        'keterangan' => $keterangan
                    ];
                    break;
            }

            // Process the data
            $result = $this->observasiModel->saveObservasiData($masterData, $detailData, $singleKUK);

            // Handle success response
            return $this->handleSuccessResponse('Data observasi berhasil disimpan', $isAjax, $result);
        } catch (\Exception $e) {
            log_message('error', 'Error saving observasi: ' . $e->getMessage());
            return $this->handleErrorResponse('Gagal menyimpan data: ' . $e->getMessage(), $isAjax);
        }
    }

    /**
     * Common method to get all required observation data
     * Reduces code duplication between AJAX and PDF methods
     *
     * @param int $id_skema Schema ID
     * @param int $id_asesi Assessee ID
     * @return array All data needed for both AJAX response and PDF generation
     */
    private function getObservasiData(int $id_skema, string $id_asesi): array
    {
        // Get observation structure data
        $detailObservasi = $this->observasiModel->getStrukturObservasiSkema($id_skema);

        // Get existing observation data if available
        $existing_data = $this->observasiModel->getExistingObservasi($id_asesi);

        // Get observation metadata
        $observasi = $this->observasiModel->getObservasiData($id_asesi, $id_skema);

        // Get kelompok kerja data
        $kelompokWithUnit = $this->skemaModel->getWorkGroupsWithUnits($id_skema);

        return [
            'kelompokWithUnit' => $kelompokWithUnit,
            'observasi' => $observasi,
            'detailObservasi' => $detailObservasi,
            'existing_data' => $existing_data,
        ];
    }

    /**
     * Delete tuk
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $tukModel = $this->tukModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $tukModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'tuk deleted successfully'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Failed to delete tuk'
                ], 400);
            }
        } catch (\Exception $e) {
            $db->transRollback();

            return $this->dataService->response([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tuk by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $tukModel = new $this->tukModel;
        $tuk = $tukModel->find($id);

        if (!$tuk) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'tuk not found'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $tuk
        ]);
    }

    /**
     * Helper method to handle error responses
     */
    private function handleErrorResponse($message, $isAjax)
    {
        if ($isAjax) {
            return $this->fail($message, 400);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Helper method to handle success responses
     */
    private function handleSuccessResponse($message, $isAjax, $result = null)
    {
        if ($isAjax) {
            return $this->respond([
                'success' => true,
                'message' => $message,
                'result' => $result,
                'token' => csrf_hash() // Return new CSRF token
            ]);
        }

        return redirect()->to('asesmen')
            ->with('success', $message);
    }
}
