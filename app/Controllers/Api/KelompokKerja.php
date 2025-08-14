<?php

namespace App\Controllers\Api;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\Api\DataTableController;

class KelompokKerja extends DataTableController
{
    public function __construct()
    {
        parent::__construct();

        $this->model = $this->kelompokKerjaModel;

        // Column mapping for KelompokKerja DataTable ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'kelompok_kerja.nama_kelompok',
            2 => 'skema.nama_skema',
            3 => 'jumlah_unit', // Count from subquery
            4 => null // No ordering for action column
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

        $data = $this->request->getPost();

        // Format data yang akan disimpan
        $formattedData = [
            'id_kelompok' => $data['id_kelompok'] ?? null,
            'nama_kelompok' => $data['nama_kelompok'],
            'id_skema' => $data['id_skema'],
        ];

        // Callback sebelum simpan (opsional)
        $beforeSave = function ($data) {
            // Validasi atau manipulasi data jika diperlukan
            return $data;
        };

        // Callback sesudah simpan (opsional)  
        $afterSave = function ($data, $id) {
            // Logging atau update tabel lain jika diperlukan
        };

        // Simpan data
        $result = $this->dataService->save(
            $this->kelompokKerjaModel,
            $formattedData,
            'id_kelompok',
            $beforeSave,
            $afterSave
        );

        // Kembalikan response JSON
        return $this->dataService->response($result, $result['code']);
    }
    /**
     * Delete kelompok kerja
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $kelompokKerjaModel = $this->kelompokKerjaModel;

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $deleted = $kelompokKerjaModel->delete($id);

            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response([
                    'status' => true,
                    'message' => 'Kelompok kerja berhasil dihapus'
                ]);
            } else {
                return $this->dataService->response([
                    'status' => false,
                    'message' => 'Gagal menghapus kelompok kerja'
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
     * Get kelompok kerja by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $kelompokKerjaModel = $this->kelompokKerjaModel;
        $kelompokKerja = $kelompokKerjaModel->find($id);

        if (!$kelompokKerja) {
            return $this->dataService->response([
                'status' => false,
                'message' => 'Kelompok kerja tidak ditemukan'
            ], 404);
        }

        return $this->dataService->response([
            'status' => true,
            'data' => $kelompokKerja
        ]);
    }
}
