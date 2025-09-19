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
        $this->model = $this->headerKonfigurasiModel;

        $this->columnMap = [
            0 => null, // No
            1 => 'nama_kop',
            2 => 'assessor_name',
            3 => null, // Logo
            4 => 'title',
            5 => null  // Aksi
        ];
    }

    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Halaman tidak ditemukan');
        }

        $id = $this->request->getPost('id');
        $isUpdate = !empty($id);

        $rules = [
            'nama_kop'      => 'required|max_length[255]',
            'assessor_id'   => 'permit_empty|is_natural_no_zero|is_not_unique[asesor.id_asesor]',
            'logo_width'    => 'required|integer|greater_than[0]',
            'title'         => 'required|max_length[255]',
            'header_string' => 'required',
        ];

        // Validasi logo: wajib diisi saat membuat baru.
        // Saat update, validasi hanya jika ada file baru yang diunggah.
        if (!$isUpdate) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,1024]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/gif]';
        } else {
            $rules['logo'] = 'permit_empty|max_size[logo,1024]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/gif]';
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            $data = $this->validator->getValidated();

            // Hapus 'logo' dari data jika tidak ada file yang diupload saat update
            if ($isUpdate && !$this->request->getFile('logo')->isValid()) {
                unset($data['logo']);
            }

            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                // Hapus logo lama jika ada (saat update)
                if ($isUpdate) {
                    $oldData = $this->model->find($id);
                    if ($oldData && !empty($oldData->logo)) {
                        $oldLogoPath = ROOTPATH . 'public/uploads/logos/' . $oldData->logo;
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                }
                $logoName = $logoFile->getRandomName();
                $logoFile->move(ROOTPATH . 'public/uploads/logos', $logoName);
                $data['logo'] = $logoName;
            }

            // Pastikan assessor_id adalah null jika string kosong
            if (isset($data['assessor_id']) && $data['assessor_id'] === '') {
                $data['assessor_id'] = null;
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
            log_message('error', '[HeaderKonfigurasi::save] ' . $e->getMessage());
            return $this->failServerError('Terjadi kesalahan pada server.');
        }
    }

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
                $logoPath = ROOTPATH . 'public/uploads/logos/' . $header->logo;
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            $this->model->delete($id);
            return $this->respondDeleted(['status' => true, 'message' => 'Konfigurasi berhasil dihapus.']);
        } catch (\Exception $e) {
            log_message('error', '[HeaderKonfigurasi::delete] ' . $e->getMessage());
            return $this->failServerError('Gagal menghapus data karena terikat dengan data lain atau kesalahan server.');
        }
    }

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
