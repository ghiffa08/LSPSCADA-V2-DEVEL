<?php

namespace App\Controllers\Api;

use Exception;
use Config\Services;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Unit extends DataTableController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = $this->unitModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'skema.id_skema',
            1 => 'unit.id_unit',
            1 => 'unit.kode_unit',
            2 => 'unit.nama_unit',
            2 => 'unit.keterangan',
            3 => 'unit.status',
            4 => null // No ordering for action column
        ];
    }

    /**
     * Save or update unit data
     */
    public function save(): ResponseInterface
    {
        // If not AJAX request, throw 404
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = UnitModel::class;
        $data = $this->request->getPost();

        // Set proper field names (based on model allowedFields)
        $formattedData = [
            'id_unit' => $data['id_unit'] ?? null,
            'id_skema' => $data['id_skema'],
            'kode_unit' => $data['kode'],
            'nama_unit' => $data['nama'],
            'keterangan' => $data['keterangan'],
            'status' => $data['status']
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
            'id_unit',
            $beforeSave,
            $afterSave
        );

        // Return response with appropriate status code
        return $this->dataService->response($result, $result['code']);
    }

    /**
     * Get unit by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $unitModel = new $this->unitModel;
        $unit = $unitModel->find($id);

        if (!$unit) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $unit
        ]);
    }

    /**
     * Delete unit
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $unitModel = $this->unitModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $unitModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Unit deleted successfully'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Failed to delete unit'
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
     * Get unit data for AJAX request (returns HTML options)
     */
    public function getUnit()
    {
        try {
            $id_skema = $this->request->getPost('id_skema');
            if (empty($id_skema)) {
                return '<option value="">-- Pilih Unit --</option>';
            }

            $units = $this->unitModel->getUnitsByScheme($id_skema);

            $output = '<option value="">-- Pilih Unit --</option>';
            foreach ($units as $unit) {
                $output .= "<option value=\"{$unit['id_unit']}\">";
                if (isset($unit['kode_unit']) && !empty($unit['kode_unit'])) {
                    $output .= "{$unit['kode_unit']} - ";
                }
                $output .= "{$unit['nama_unit']}</option>";
            }

            return $output;
        } catch (\Exception $e) {
            log_message('error', 'Error getting unit options: ' . $e->getMessage());
            return '<option value="">-- Error loading data --</option>';
        }
    }

    /**
     * Get unit data for AJAX request (returns JSON)
     */
    public function getUnitJSON()
    {
        try {
            $id_skema = $this->request->getPost('id_skema');
            if (empty($id_skema)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID skema tidak valid',
                    'data' => []
                ]);
            }

            $units = $this->unitModel->getUnitsByScheme($id_skema);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $units
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting unit options: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data unit',
                'data' => []
            ]);
        }
    }
}
