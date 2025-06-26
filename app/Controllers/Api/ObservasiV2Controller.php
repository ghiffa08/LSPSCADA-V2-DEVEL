<?php

namespace App\Controllers\Api;

use App\Services\ObservasiService;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class ObservasiController extends ResourceController
{
    use ResponseTrait;

    protected ObservasiService $observasiService;

    public function __construct()
    {
        $this->observasiService = new ObservasiService();
        helper(['auth']);
    }

    /**
     * Get observation list with pagination and filtering
     * GET /api/observasi
     */
    public function index()
    {
        try {
            $filters = [
                'search' => $this->request->getGet('search'),
                'tanggal_dari' => $this->request->getGet('tanggal_dari'),
                'tanggal_sampai' => $this->request->getGet('tanggal_sampai'),
                'id_asesor' => $this->request->getGet('id_asesor')
            ];

            $page = (int) $this->request->getGet('page') ?: 1;
            $perPage = (int) $this->request->getGet('per_page') ?: 10;

            $result = $this->observasiService->getObservationList($filters, $page, $perPage);

            if ($result['success']) {
                return $this->respond($result['data'], 200);
            }

            return $this->failServerError($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::index() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Get specific observation with details
     * GET /api/observasi/{id}
     */
    public function show($id = null)
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationError('ID observasi tidak valid');
            }

            $result = $this->observasiService->getObservationWithDetails((int) $id);

            if ($result['success']) {
                return $this->respond($result['data'], 200);
            }

            return $this->failNotFound($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::show() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Create or update observation with batch details
     * POST /api/observasi
     */
    public function create()
    {
        try {
            // Validate request method
            if (!$this->request->isAJAX()) {
                return $this->failUnauthorized('Akses langsung tidak diizinkan');
            }

            // Get JSON input
            $input = $this->request->getJSON(true);

            if (!$input) {
                return $this->failValidationError('Data input tidak valid atau kosong');
            }

            // Process through service layer
            $result = $this->observasiService->saveObservation($input);

            if ($result['success']) {
                return $this->respondCreated($result, 'Observasi berhasil disimpan');
            }

            return $this->failValidationError($result['message']);
        } catch (\InvalidArgumentException $e) {
            return $this->failValidationError($e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::create() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Update observation
     * PUT /api/observasi/{id}
     */
    public function update($id = null)
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationError('ID observasi tidak valid');
            }

            if (!$this->request->isAJAX()) {
                return $this->failUnauthorized('Akses langsung tidak diizinkan');
            }

            $input = $this->request->getJSON(true);

            if (!$input) {
                return $this->failValidationError('Data input tidak valid atau kosong');
            }

            // Add ID to input for update operation
            $input['id_observasi'] = (int) $id;

            $result = $this->observasiService->saveObservation($input);

            if ($result['success']) {
                return $this->respond($result, 200, 'Observasi berhasil diperbarui');
            }

            return $this->failValidationError($result['message']);
        } catch (\InvalidArgumentException $e) {
            return $this->failValidationError($e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::update() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Get KUK structure for observation form
     * GET /api/observasi/kuk-structure/{id_skema}
     */
    public function getKukStructure($id_skema = null)
    {
        try {
            if (!$id_skema || !is_numeric($id_skema)) {
                return $this->failValidationError('ID skema tidak valid');
            }

            $result = $this->observasiService->getKukStructureForSchema((int) $id_skema);

            if ($result['success']) {
                return $this->respond($result['data'], 200);
            }

            return $this->failNotFound($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::getKukStructure() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Batch save multiple observations (for bulk operations)
     * POST /api/observasi/batch
     */
    public function batchSave()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->failUnauthorized('Akses langsung tidak diizinkan');
            }

            $input = $this->request->getJSON(true);

            if (!$input || !isset($input['observations']) || !is_array($input['observations'])) {
                return $this->failValidationError('Data observasi batch tidak valid');
            }

            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($input['observations'] as $index => $observationData) {
                try {
                    $result = $this->observasiService->saveObservation($observationData);
                    $results[$index] = $result;

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $results[$index] = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            return $this->respond([
                'success' => $successCount > 0,
                'message' => "Berhasil menyimpan {$successCount} observasi, gagal {$failedCount}",
                'summary' => [
                    'total' => count($input['observations']),
                    'success' => $successCount,
                    'failed' => $failedCount
                ],
                'details' => $results
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::batchSave() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }

    /**
     * Get observation summary/statistics
     * GET /api/observasi/{id}/summary
     */
    public function getSummary($id = null)
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->failValidationError('ID observasi tidak valid');
            }

            $result = $this->observasiService->getObservationWithDetails((int) $id);

            if ($result['success']) {
                return $this->respond($result['data']['summary'], 200);
            }

            return $this->failNotFound($result['message']);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::getSummary() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }
    /**
     * Validate observation data before save (for frontend validation)
     * POST /api/observasi/validate
     */
    public function validateObservationData()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->failUnauthorized('Akses langsung tidak diizinkan');
            }

            $input = $this->request->getJSON(true);

            if (!$input) {
                return $this->failValidationError('Data input tidak valid atau kosong');
            }

            // Use request validation without saving
            $request = new \App\Requests\ObservasiRequest();
            $validatedData = $request->validate($input);

            return $this->respond([
                'valid' => true,
                'message' => 'Data valid',
                'sanitized_data' => $validatedData
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return $this->respond([
                'valid' => false,
                'message' => $e->getMessage()
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'ObservasiController::validateData() - ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server');
        }
    }
}
