<?php

namespace App\Controllers\Api;

use App\Controllers\DataTableController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Instansi extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('InstansiModel');

        $this->columnMap = [
            0 => null, // No
            1 => 'nama_instansi',
            2 => null  // Aksi
        ];
    }

    /**
     * Menyimpan atau memperbarui data instansi.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Halaman tidak ditemukan');
        }

        $rules = [
            'nama_instansi' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->validator->getValidated();
        $id = $this->request->getPost('id');

        try {
            if (!empty($id)) {
                $this->model->update($id, $data);
                $message = 'Instansi berhasil diperbarui.';
            } else {
                $this->model->insert($data);
                $message = 'Instansi berhasil ditambahkan.';
            }

            return $this->respondCreated(['status' => true, 'message' => $message]);
        } catch (\Exception $e) {
            log_message('error', '[Api/Instansi::save] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server.');
        }
    }

    /**
     * Menghapus data instansi.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        if (!$this->model->find($id)) {
            return $this->failNotFound('Data instansi tidak ditemukan.');
        }

        try {
            $this->model->delete($id);
            return $this->respondDeleted(['status' => true, 'message' => 'Instansi berhasil dihapus.']);
        } catch (\Exception $e) {
            // Menangani error jika instansi terikat dengan data lain (foreign key)
            log_message('error', '[Api/Instansi::delete] ' . $e->getMessage());
            return $this->failServerError('Gagal menghapus. Instansi ini mungkin sedang digunakan di konfigurasi kop surat.');
        }
    }

    /**
     * Mengambil data berdasarkan ID untuk form edit.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Data tidak ditemukan');
        }
        return $this->respond(['status' => true, 'data' => $data]);
    }
}
