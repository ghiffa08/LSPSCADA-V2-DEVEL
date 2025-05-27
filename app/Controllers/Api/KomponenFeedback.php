<?php

namespace App\Controllers\Api;

use App\Models\KomponenFeedbackModel;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class KomponenFeedback extends DataTableController
{
    protected $komponenFeedbackModel;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->komponenFeedbackModel = new KomponenFeedbackModel();
        $this->model = $this->komponenFeedbackModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => null,
            2 => 'komponen_feedback.id_komponen',
            3 => 'komponen_feedback.pernyataan',
            4 => null // No ordering for action column
        ];
    }

    /**
     * Get all komponen feedback data
     */
    public function getAll(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }
        
        // Get all komponen ordered by urutan
        $komponen = $this->komponenFeedbackModel->orderBy('urutan', 'ASC')->findAll();
        
        return $this->dataService->response([
            'status' => true,
            'data' => $komponen
        ]);
    }

    /**
     * Save or update komponen feedback data
     */
    public function save(): ResponseInterface
    {
        // Only allow AJAX requests
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = KomponenFeedbackModel::class;
        $data = $this->request->getPost();

        // Format data to be saved
        $formattedData = [
            'id_komponen'  => $data['id_komponen'] ?? null,
            'pernyataan'   => $data['pernyataan'],
            'urutan'       => $data['urutan'] ?? 0,
        ];

        // Optional callback before save
        $beforeSave = function ($data) {
            return $data;
        };

        // Optional callback after save
        $afterSave = function ($data, $id) {
            // Any post-save operations
        };

        // Save data
        $result = $this->dataService->save(
            $modelName,
            $formattedData,
            'id_komponen',
            $beforeSave,
            $afterSave
        );

        // Return JSON response
        return $this->dataService->response($result, $result['code']);
    }

    /**
     * Delete komponen feedback
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Check if this komponen is used in any detail_feedback_asesi
            $isUsed = $db->table('detail_feedback_asesi')
                ->where('id_komponen', $id)
                ->countAllResults() > 0;

            if ($isUsed) {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Komponen ini sedang digunakan dalam data umpan balik asesi dan tidak dapat dihapus'
                ], 400);
            }

            $deleted = $this->komponenFeedbackModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Komponen berhasil dihapus'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Gagal menghapus komponen'
                ], 400);
            }
        } catch (\Exception $e) {
            $db->transRollback();

            return $this->dataService->response([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get komponen feedback by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $komponen = $this->komponenFeedbackModel->find($id);

        if (!$komponen) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'Komponen tidak ditemukan'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $komponen
        ]);
    }
    
    /**
     * Get maximum order value
     */
    public function getMaxOrder(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }
        
        $maxOrder = $this->komponenFeedbackModel->getMaxUrutan();
        
        return $this->dataService->response([
            'status' => true,
            'maxOrder' => $maxOrder,
            'nextOrder' => $maxOrder + 1
        ]);
    }

    /**
     * Update order of komponen feedback
     */
    public function updateOrder(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            // Get raw input and log for debugging
            $raw = $this->request->getBody();
            log_message('debug', 'Raw updateOrder payload: ' . $raw);

            // Parse JSON data
            $data = $this->request->getJSON(true);
            log_message('debug', 'Parsed updateOrder data: ' . json_encode($data));

            $items = $data['items'] ?? [];

            if (empty($items)) {
                log_message('error', 'No items found in updateOrder request');
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 400);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // Log items being updated
            log_message('info', 'Updating order for items: ' . json_encode($items));

            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['position'])) {
                    log_message('error', 'Invalid item format: ' . json_encode($item));
                    continue;
                }
                
                // Ensure values are of correct type
                $id = (int) $item['id'];
                $position = (int) ($item['position'] + 1); // Convert 0-based index to 1-based order
                
                log_message('debug', "Updating item ID $id to position $position");

                // Update each component with new order
                $result = $this->komponenFeedbackModel->update($id, [
                    'urutan' => $position
                ]);
                
                if ($result === false) {
                    log_message('error', "Failed to update item ID $id: " . print_r($this->komponenFeedbackModel->errors(), true));
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', 'Transaction failed in updateOrder');
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Gagal mengubah urutan'
                ], 500);
            }

            return $this->dataService->response([
                'status' => true,
                'message' => 'Urutan berhasil diubah'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in updateOrder: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            
            return $this->dataService->response([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
