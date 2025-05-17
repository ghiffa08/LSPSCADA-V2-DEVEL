<?php

namespace App\Controllers\Api;

use Config\Services;
use App\Models\ElemenModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Elemen extends DataTableController
{

    public function __construct()
    {
        parent::__construct();

        $this->model = $this->elemenModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'skema.id_skema',
            2 => 'unit.id_unit',
            3 => 'unit.id_elemen',
            4 => 'elemen.kode_elemen',
            5 => 'elemen.nama_elemen',
            6 => null // No ordering for action column
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

        $modelName = ElemenModel::class;
        $data = $this->request->getPost();

        // Set proper field names (based on model allowedFields)
        $formattedData = [
            'id_elemen' => $data['id_elemen'] ?? null,
            'id_skema' => $data['id_skema'],
            'id_unit' => $data['id_unit'],
            'kode_elemen' => $data['kode'],
            'nama_elemen' => $data['nama']
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
            'id_elemen',
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

        $elemenModel = $this->elemenModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $elemenModel->delete($id);

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

        $elemenModel = new $this->elemenModel;
        $elemen = $elemenModel->find($id);

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

    /**
     * Get elemen by elemen id for dropdown selection.
     *
     * @return string HTML options for select dropdown
     */
    public function getElemen()
    {
        try {
            $id_unit = $this->request->getPost('id_unit');
            if (empty($id_unit)) {
                return '<option>-- Pilih Elemen --</option>';
            }

            $elemen = $this->elemenModel->getElementsByUnit($id_unit);

            $output = '<option>-- Pilih Elemen --</option>';
            foreach ($elemen as $value) {
                $output .= "<option value=\"{$value['id_elemen']}\">{$value['nama_elemen']}</option>";
            }

            return $output;
        } catch (\Exception $e) {
            log_message('error', 'Error getting elemen options: ' . $e->getMessage());
            return '<option>-- Error loading data --</option>';
        }
    }
}
