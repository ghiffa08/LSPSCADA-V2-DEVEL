<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Elemen extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = $this->elemenModel;

        // Map untuk sorting, merujuk pada kolom dari hasil JOIN di Model
        $this->columnMap = [
            0 => null, // No
            1 => 'skema.nama_skema',
            2 => 'unit.nama_unit',
            3 => 'elemen.kode_elemen',
            4 => 'elemen.nama_elemen',
            5 => null  // Aksi
        ];
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

    /**
     * Menyimpan atau memperbarui data elemen, memanfaatkan validasi dari Model.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->request->getPost();

        // Hapus id_elemen jika kosong (untuk proses INSERT)
        if (empty($data['id_elemen'])) {
            unset($data['id_elemen']);
        }

        // Gunakan metode save() dari Model yang sudah menangani validasi
        if ($this->model->save($data) === false) {
            return $this->failValidationErrors($this->model->errors());
        }

        $message = isset($data['id_elemen']) ? 'Elemen berhasil diperbarui.' : 'Elemen berhasil ditambahkan.';
        return $this->respondCreated(['status' => true, 'message' => $message]);
    }

    /**
     * Mengambil data elemen berdasarkan ID untuk form edit.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        // Model sudah di-join, jadi kita ambil data dengan info skema dan unit
        $data = $this->model
            ->select('elemen.*, unit.id_skema') // Pastikan id_skema terpilih
            ->join('unit', 'unit.id_unit = elemen.id_unit')
            ->find($id);

        if (!$data) {
            return $this->failNotFound('Data elemen tidak ditemukan.');
        }

        return $this->respond(['status' => true, 'data' => $data]);
    }
    
     /**
     * Get elemen data for a dependent dropdown, returns JSON.
     */
    public function getElemenJSON(): ResponseInterface
    {
        $id_unit = $this->request->getPost('id_unit');

        if (empty($id_unit)) {
            return $this->fail('ID Unit diperlukan.', 400);
        }

        try {
            $elemenModel = new \App\Models\ElemenModel(); // Instansiasi model
            
            $elemen = $elemenModel
                ->where('id_unit', $id_unit)
                ->orderBy('kode_elemen', 'asc')
                ->findAll();

            return $this->respond([
                'status'  => 'success',
                'data'    => $elemen
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getElemenJSON] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan saat mengambil data elemen.');
        }
    }

    /**
     * Menghapus data elemen.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            if ($this->model->delete($id)) {
                return $this->respondDeleted(['status' => true, 'message' => 'Elemen berhasil dihapus.']);
            }
            return $this->fail($this->model->errors() ? implode(', ', $this->model->errors()) : 'Gagal menghapus elemen.');
        } catch (\Exception $e) {
            return $this->failServerError('Gagal menghapus elemen. Mungkin data ini terhubung dengan data lain.');
        }
    }
}