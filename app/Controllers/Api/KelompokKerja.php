<?php

namespace App\Controllers\Api;

use Config\Services;
use App\Models\TUKModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;

class KelompokKerja extends DataTableController
{

    public function __construct()
    {
        parent::__construct();

        $this->model = $this->kelompokKerjaModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'tuk.nama_tuk',
            2 => 'tuk.jenis_tuk',
            3 => null // No ordering for action column
        ];
    }

    /**
     * Save or update Kelompok Kerja data
     */
    public function save(): ResponseInterface
    {
        // Hanya izinkan request AJAX
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = TUKModel::class;
        $data = $this->request->getPost();

        // Format data yang akan disimpan
        $formattedData = [
            'id_tuk'     => $data['id_tuk'] ?? null,
            'nama_tuk'   => $data['nama_tuk'],
            'jenis_tuk'   => $data['jenis_tuk'],
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
            'id_tuk',
            $beforeSave,
            $afterSave
        );

        // Kembalikan response JSON
        return $this->dataService->response($result, $result['code']);
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
}
