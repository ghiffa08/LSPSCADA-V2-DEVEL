<?php

namespace App\Controllers\Api;

use Config\Services;
use App\Models\AsesmenModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use App\Models\SettanggalModel;

class SetTanggal extends DataTableController
{

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->settanggalModel;

        // Optional: Define custom column mapping for ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'set_tanggal.tanggal',
            2 => 'set_tanggal.keterangan',
            3 => 'set_tanggal.status',
            4 => null // No ordering for action column
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

        $modelName = SettanggalModel::class;
        $data = $this->request->getPost();

        // Set proper field names (based on model allowedFields)
        $formattedData = [
            'id_tanggal' => $data['id_tanggal'] ?? null,
            'tanggal' => $data['tanggal'],
            'keterangan' => $data['keterangan'],
            'status' => $data['status'] ?? 'Y',
        ];

        // Process before save callback (if needed)
        $beforeSave = function ($data) {
            // Ubah format tanggal dari 'Y-m-d\TH:i' ke 'Y-m-d H:i:s'
            if (!empty($data['tanggal'])) {
                $parsed = \DateTime::createFromFormat('Y-m-d\TH:i', $data['tanggal']);
                if ($parsed) {
                    $data['tanggal'] = $parsed->format('Y-m-d H:i:s');
                }
            }

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
            'id_tanggal',
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

        $settanggalModel = new $this->settanggalModel;
        $elemen = $settanggalModel->find($id);

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
