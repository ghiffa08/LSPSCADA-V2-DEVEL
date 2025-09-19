<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;

class Unit extends DataTableController
{
    use ResponseTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = $this->unitModel;

        // Pemetaan kolom ini tetap diperlukan oleh DataTableController Anda
        // untuk menangani sorting/pengurutan data dari sisi klien.
        $this->columnMap = [
            0 => null, // No
            1 => 'skema.nama_skema',
            2 => 'unit.kode_unit',
            3 => 'unit.nama_unit',
            4 => 'unit.status',
            5 => null  // Aksi
        ];
    }

    /**
     * Save or update unit data.
     * This method now leverages the validation and save logic from the Model.
     */

    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->request->getPost();

        // =================================================================
        // PERBAIKAN DI SINI:
        // Jika id_unit kosong (saat tambah data baru), hapus dari array
        // agar database bisa menjalankan auto-increment.
        // =================================================================
        if (empty($data['id_unit'])) {
            unset($data['id_unit']);
        }

        // Gunakan metode save() bawaan Model.
        if ($this->model->save($data) === false) {
            // Jika validasi gagal, kirim error langsung dari Model.
            return $this->failValidationErrors($this->model->errors());
        }

        // Tentukan pesan sukses berdasarkan operasi (insert/update)
        $message = isset($data['id_unit']) && !empty($data['id_unit'])
            ? 'Unit Kompetensi berhasil diperbarui.'
            : 'Unit Kompetensi berhasil ditambahkan.';

        return $this->respondCreated(['status' => true, 'message' => $message]);
    }

    /**
     * Get unit by ID for edit modal.
     * This method remains lean as its job is simple.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Data unit tidak ditemukan.');
        }

        return $this->respond(['status' => true, 'data' => $data]);
    }

    /**
     * Delete a unit using the custom method from the Model.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        try {
            // Memanggil metode deleteUnit() khusus dari Model untuk keamanan transaksi
            // dan menghapus data terkait (elemen).
            if ($this->model->deleteUnit((int)$id)) {
                return $this->respondDeleted(['status' => true, 'message' => 'Unit berhasil dihapus.']);
            }
            
            // Pesan ini mungkin muncul jika data tidak ditemukan sebelum dihapus.
            return $this->fail('Gagal menghapus unit. Data mungkin tidak ditemukan.', 400);

        } catch (\Exception $e) {
            return $this->failServerError('Gagal menghapus unit. Mungkin data ini terhubung dengan data lain.');
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
     * Get unit data for the dependent dropdown, returns JSON.
     */
    public function getUnitJSON(): ResponseInterface
    {
        $id_skema = $this->request->getPost('id_skema');

        if (empty($id_skema)) {
            // Mengirim error jika id_skema tidak ada
            return $this->fail('ID Skema diperlukan.', 400);
        }

        try {
            // Gunakan UnitModel yang sudah ada di controller
            $units = $this->unitModel
                ->where('id_skema', $id_skema)
                ->where('status', 'Y') // Hanya ambil unit yang aktif
                ->orderBy('kode_unit', 'asc')
                ->findAll();

            // Mengirim respons JSON yang sukses
            return $this->respond([
                'status'  => 'success',
                'data'    => $units
            ]);

        } catch (\Exception $e) {
            // Menangani jika ada error database
            log_message('error', '[getUnitJSON] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan saat mengambil data unit.');
        }
    }
}