<?php

namespace App\Controllers\Api;

use App\Controllers\DataTableController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class HeaderKonfigurasi extends DataTableController
{
    use ResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('HeaderKonfigurasiModel');

        // Mapping kolom untuk sorting dan searching di DataTable
        $this->columnMap = [
            0 => null, // No
            1 => 'nama_kop',
            2 => 'instansi_name', // Kolom hasil join
            3 => null, // Logo
            4 => 'title',
            5 => null  // Aksi
        ];
    }

    /**
     * Menyimpan atau memperbarui data konfigurasi header.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Halaman tidak ditemukan');
        }

        $id = $this->request->getPost('id');
        $isUpdate = !empty($id);

        $instansiIdRule = 'permit_empty|is_natural_no_zero|is_not_unique[instansi.id]';
        if ($this->request->getPost('instansi_id')) {
            if ($isUpdate) {
                // Saat update, abaikan ID saat ini
                $instansiIdRule .= '|is_unique[header_konfigurasi.instansi_id,id,' . $id . ']';
            } else {
                // Saat tambah baru, harus unik
                $instansiIdRule .= '|is_unique[header_konfigurasi.instansi_id]';
            }
        }

        $rules = [
            'nama_kop'      => 'required|max_length[255]',
            'instansi_id'   => $instansiIdRule,
            'logo_width'    => 'required|integer|greater_than[0]',
            'title'         => 'permit_empty|max_length[255]',
            'header_string' => 'permit_empty',
        ];

        // Validasi logo: wajib saat tambah baru, opsional saat update
        if (!$isUpdate) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,1024]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/gif]';
        } else {
            // Hanya validasi jika ada file baru diupload
            if ($this->request->getFile('logo')->isValid()) {
                $rules['logo'] = 'max_size[logo,1024]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/gif]';
            }
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            $data = $this->validator->getValidated();

            // Proses upload logo jika ada
            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                // Hapus logo lama saat update
                if ($isUpdate) {
                    $oldData = $this->model->find($id);
                    if ($oldData && !empty($oldData->logo)) {
                        $oldLogoPath = FCPATH . 'uploads/logos/' . $oldData->logo;
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                }
                $logoName = $logoFile->getRandomName();
                $logoFile->move(FCPATH . 'uploads/logos', $logoName);
                $data['logo'] = $logoName;
            }

            // Pastikan instansi_id adalah null jika string kosong
            if (isset($data['instansi_id']) && $data['instansi_id'] === '') {
                $data['instansi_id'] = null;
            }

            if ($isUpdate) {
                $this->model->update($id, $data);
                $message = 'Konfigurasi header berhasil diperbarui.';
            } else {
                $this->model->insert($data);
                $message = 'Konfigurasi header berhasil ditambahkan.';
            }

            return $this->respondCreated(['status' => true, 'message' => $message]);
        } catch (\Exception $e) {
            log_message('error', '[Api/HeaderKonfigurasi::save] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server.');
        }
    }

    /**
     * Menghapus data konfigurasi header.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $header = $this->model->find($id);
        if (!$header) {
            return $this->failNotFound('Data konfigurasi tidak ditemukan.');
        }

        try {
            // Hapus file logo dari server
            if (!empty($header->logo)) {
                $logoPath = FCPATH . 'uploads/logos/' . $header->logo;
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            $this->model->delete($id);
            return $this->respondDeleted(['status' => true, 'message' => 'Konfigurasi berhasil dihapus.']);
        } catch (\Exception $e) {
            log_message('error', '[Api/HeaderKonfigurasi::delete] ' . $e->getMessage());
            return $this->failServerError('Gagal menghapus data.');
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
