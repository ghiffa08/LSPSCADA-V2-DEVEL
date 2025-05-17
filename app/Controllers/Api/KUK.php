<?php

namespace App\Controllers\Api;

use Config\Services;
use App\Models\KUKModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class KUK extends DataTableController
{

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->kukModel;

        // Optional: Define custom column mapping for ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'kuk.id_kuk',
            2 => 'kuk.kode_kuk',
            3 => 'kuk.nama_kuk',
            4 => 'kuk.pertanyaan',
            5 => null // No ordering for action column
        ];
    }

    /**
     * Save or update elemen data
     */
    public function save(): ResponseInterface
    {
        // If not AJAX request, throw 404
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = KUKModel::class;
        $data = $this->request->getPost();

        // Set proper field names (based on model allowedFields)
        $formattedData = [
            'id_kuk' => $data['id_kuk'] ?? null,
            'id_skema' => $data['id_skema'],
            'id_unit' => $data['id_unit'],
            'id_elemen' => $data['id_elemen'],
            'kode_kuk' => $data['kode_kuk'],
            'nama_kuk' => $data['nama_kuk'],
            'pertanyaan' => $data['pertanyaan'] ?? null
        ];

        // Process before save callback (if needed)
        $beforeSave = function ($data) {
            // You can modify data here if needed
            return $data;
        };

        // Process after save callback (if needed)
        $afterSave = function ($data, $id) {
            // You can perform additional actions here
            // For example: log activity, update related records, etc.
        };

        // Save the data
        $result = $this->dataService->save(
            $modelName,
            $formattedData,
            'id_kuk',
            $beforeSave,
            $afterSave
        );

        // Return response with appropriate status code
        return $this->dataService->response($result, $result['code']);
    }

    /**
     * Delete elemen
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $KukModel = $this->kukModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $KukModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Elemen deleted successfully'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Failed to delete Elemen'
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
     * Get elemen by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $KukModel = new $this->kukModel;
        $elemen = $KukModel->find($id);

        if (!$elemen) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'Elemen not found'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $elemen
        ]);
    }
}
