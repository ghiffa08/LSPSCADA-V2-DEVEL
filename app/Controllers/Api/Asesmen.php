<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Asesmen extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->asesmenModel;

        // Map untuk sorting, sesuai dengan JOIN di Model
        $this->columnMap = [
            0 => null, // No
            1 => 'skema.nama_skema',
            2 => 'tuk.nama_tuk',
            3 => 'set_tanggal.tanggal',
            4 => 'asesmen.tujuan',
            5 => null  // Aksi
        ];
    }

    /**
     * Save or update Asesmen data.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->request->getPost();

        if (empty($data['id_asesmen'])) {
            unset($data['id_asesmen']);
        }

        if ($this->model->save($data) === false) {
            return $this->failValidationErrors($this->model->errors());
        }

        $message = isset($data['id_asesmen']) ? 'Data asesmen berhasil diperbarui.' : 'Data asesmen berhasil ditambahkan.';
        return $this->respondCreated(['status' => true, 'message' => $message]);
    }

    /**
     * Get Asesmen by ID for edit modal.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }
        
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Data asesmen tidak ditemukan.');
        }

        return $this->respond(['status' => true, 'data' => $data]);
    }

    /**
     * Delete an Asesmen.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            if ($this->model->delete($id)) {
                return $this->respondDeleted(['status' => true, 'message' => 'Asesmen berhasil dihapus.']);
            }
            return $this->fail($this->model->errors() ? implode(', ', $this->model->errors()) : 'Gagal menghapus asesmen.');
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menghapus asesmen.');
        }
    }
/**
     * Get ALL TUK data for a dropdown.
     */
    public function getTukJSON(): ResponseInterface
    {
        // Parameter id_skema telah dihapus
        try {
            // Memanggil metode getTuk() dari Model (yang juga akan kita ubah)
            $data = $this->model->getTuk();
            return $this->respond(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            log_message('error', '[getTukJSON] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan saat mengambil data TUK.');
        }
    }

    /**
     * Get ALL Jadwal data for a dropdown.
     */
    public function getJadwalJSON(): ResponseInterface
    {
        // Parameter id_skema telah dihapus
        try {
            // Memanggil metode getJadwal() dari Model (yang juga akan kita ubah)
            $data = $this->model->getJadwal();
            return $this->respond(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            log_message('error', '[getJadwalJSON] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan saat mengambil data Jadwal.');
        }
    }
}