<?php

namespace App\Controllers\Api;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use CodeIgniter\API\ResponseTrait; // Gunakan ini untuk respons API yang standar

class Skema extends DataTableController
{
    use ResponseTrait; // Tambahkan trait ini

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->skemaModel;

        // Perbaikan: Kunci array tidak boleh duplikat
        $this->columnMap = [
            0 => null, // No
            1 => 'kode_skema',
            2 => 'nama_skema',
            3 => 'jenis_skema',
            4 => 'status',
            5 => null // Aksi
        ];
    }

    /**
     * Save or update skema data
     * Disederhanakan dengan validasi CodeIgniter
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Halaman tidak ditemukan');
        }

        // Aturan validasi
        $rules = [
            'kode_skema' => 'required|max_length[20]',
            'nama_skema' => 'required|max_length[255]',
            'jenis_skema' => 'required|in_list[KKNI,Okupasi,Klaster]',
            'status' => 'required|in_list[Y,N]'
        ];

        // Jalankan validasi
        if (!$this->validate($rules)) {
            // Jika validasi gagal, kirim error
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Ambil data yang sudah divalidasi
        $data = $this->validator->getValidated();
        $id = $this->request->getPost('id_skema');

        try {
            if (!empty($id)) {
                // Proses Update
                $this->model->update($id, $data);
                $message = 'Skema berhasil diperbarui.';
            } else {
                // Proses Insert
                $this->model->insert($data);
                $message = 'Skema berhasil ditambahkan.';
            }

            return $this->respondCreated([
                'status' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan pada server: ' . $e->getMessage());
        }
    }

    /**
     * Delete skema
     * Disederhanakan untuk respons yang lebih konsisten
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $skema = $this->model->find($id);
        if (!$skema) {
            return $this->failNotFound('Data skema tidak ditemukan.');
        }

        try {
            if ($this->model->delete($id)) {
                return $this->respondDeleted([
                    'status' => true,
                    'message' => 'Skema berhasil dihapus.'
                ]);
            }

            return $this->fail('Gagal menghapus skema.', 400);
        } catch (\Exception $e) {
            // Tangani error jika ada constraint foreign key
            return $this->failServerError('Gagal menghapus skema. Data ini mungkin digunakan di tabel lain.');
        }
    }


    /**
     * Get skema by ID (for edit modal)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->model->find($id);

        if (!$data) {
            return $this->failNotFound('Data skema tidak ditemukan');
        }

        return $this->respond([
            'status' => true,
            'data' => $data
        ]);
    }
}
