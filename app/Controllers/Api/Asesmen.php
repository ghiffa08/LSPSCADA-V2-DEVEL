<?php

namespace App\Controllers\Api;

use Config\Services;
use App\Models\AsesmenModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Asesmen extends DataTableController
{

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->asesmenModel;

        // Optional: Define custom column mapping for ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'skema.nama_skema',
            2 => 'tuk.nama_tuk',
            3 => 'set_tanggal.tanggal_asesmen',
            4 => 'asesmen.tujuan',
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

        $modelName = AsesmenModel::class;
        $data = $this->request->getPost();

        // Set proper field names (based on model allowedFields)
        $formattedData = [
            'id_asesmen' => $data['id_asesmen'] ?? null,
            'id_skema' => $data['id_skema'],
            'id_tuk' => $data['id_tuk'],
            'id_tanggal' => $data['id_tanggal'],
            'tujuan' => $data['tujuan'],
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
            'id_asesmen',
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
