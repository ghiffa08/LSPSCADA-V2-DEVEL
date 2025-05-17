<?php

namespace App\Controllers\Api;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Skema extends DataTableController
{

    public function __construct()
    {
        parent::__construct();

        $this->model = $this->skemaModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'skema.id_skema',
            1 => 'skema.kode_skema',
            2 => 'skema.nama_skema',
            2 => 'skema.jenis_skema',
            3 => 'skema.status',
            4 => null // No ordering for action column
        ];
    }

    /**
     * Save or update skema data
     */
    public function save(): ResponseInterface
    {
        // Hanya izinkan request AJAX
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = \App\Models\SkemaModel::class;
        $data = $this->request->getPost();

        // Format data yang akan disimpan
        $formattedData = [
            'id_skema'     => $data['id_skema'] ?? null,
            'kode_skema'   => $data['kode_skema'],
            'nama_skema'   => $data['nama_skema'],
            'jenis_skema'  => $data['jenis_skema'],
            'status'       => $data['status']
        ];

        // Callback sebelum simpan (opsional)
        $beforeSave = function ($data) {
            // Misalnya, validasi atau manipulasi data
            return $data;
        };

        // Callback sesudah simpan (opsional)
        $afterSave = function ($data, $id) {
            // Misalnya, logging atau update tabel lain
        };

        // Simpan data
        $result = $this->dataService->save(
            $modelName,
            $formattedData,
            'id_skema',
            $beforeSave,
            $afterSave
        );

        // Kembalikan response JSON
        return $this->dataService->response($result, $result['code']);
    }

    /**
     * Delete skema
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $skemaModel = $this->skemaModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $skemaModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Skema deleted successfully'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Failed to delete skema'
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
     * Get skema by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $skemaModel = new $this->skemaModel;
        $skema = $skemaModel->find($id);

        if (!$skema) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'skema not found'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $skema
        ]);
    }

    public function get($id): ResponseInterface
    {
        $skema = $this->skemaModel->find($id);

        if (!$skema) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Skema tidak ditemukan']);
        }

        return $this->response->setJSON($skema);
    }
}
