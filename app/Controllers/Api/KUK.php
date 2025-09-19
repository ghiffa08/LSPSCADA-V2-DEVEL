<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class KUK extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->kukModel;

        // Map untuk sorting, sesuai dengan JOIN di Model
        $this->columnMap = [
            0 => null, // No
            1 => 'skema.nama_skema',
            2 => 'unit.nama_unit',
            3 => 'elemen.nama_elemen',
            4 => 'kuk.kode_kuk',
            5 => 'kuk.pertanyaan',
            6 => null  // Aksi
        ];
    }
    
        /**
     * Get KUK by Elemen ID for dependent dropdown
     * Returns HTML options
     */
    public function getKuk(): ResponseInterface
    {
        if ($this->request->isAJAX()) {
            $id_elemen = $this->request->getPost('id_elemen');
            if (!$id_elemen) {
                return $this->response->setStatusCode(400)->setBody('ID Elemen is required.');
            }

            // Cast the ID to an integer to match the model's type hint
            $kukData = $this->kukModel->getByElemen((int)$id_elemen);

            $options = '<option value="">-- Pilih KUK --</option>';
            foreach ($kukData as $kuk) {
                $options .= '<option value="' . $kuk['id_kuk'] . '">' . esc($kuk['kode_kuk'] . ' - ' . $kuk['pertanyaan']) . '</option>';
            }

            return $this->response->setBody($options);
        }

        // Return 404 if not AJAX
        return $this->response->setStatusCode(404);
    }

    /**
     * Save or update KUK data using Model's validation.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->request->getPost();

        // Unset primary key if empty (for INSERT)
        if (empty($data['id_kuk'])) {
            unset($data['id_kuk']);
        }

        if ($this->model->save($data) === false) {
            return $this->failValidationErrors($this->model->errors());
        }

        $message = isset($data['id_kuk']) ? 'KUK berhasil diperbarui.' : 'KUK berhasil ditambahkan.';
        return $this->respondCreated(['status' => true, 'message' => $message]);
    }

    /**
     * Get KUK by ID for edit modal.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }
        
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Data KUK tidak ditemukan.');
        }

        return $this->respond(['status' => true, 'data' => $data]);
    }
    
   

    /**
     * Delete a KUK.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            if ($this->model->delete($id)) {
                return $this->respondDeleted(['status' => true, 'message' => 'KUK berhasil dihapus.']);
            }
            return $this->fail($this->model->errors() ? implode(', ', $this->model->errors()) : 'Gagal menghapus KUK.');
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menghapus KUK.');
        }
    }
}