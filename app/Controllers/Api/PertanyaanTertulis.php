<?php

namespace App\Controllers\Api;

use App\Models\PertanyaanTertulisModel;
use App\Services\PertanyaanTertulisService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use Exception;

class PertanyaanTertulis extends DataTableController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PertanyaanTertulisModel();
        $this->columnMap = [
            1 => 'users.nama_lengkap', // Diubah dari apl1.nama_siswa
            2 => 'skema.nama_skema',
            3 => 'pertanyaan_tertulis.tanggal_ujian',
        ];
    }

    public function loadUjian(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failForbidden();

        $id_skema = $this->request->getGet('id_skema');
        $id_pengajuan = $this->request->getGet('id_pengajuan'); // Diubah dari id_apl1

        if (!$id_skema || !$id_pengajuan) return $this->fail('ID Skema dan ID Pengajuan diperlukan.', 400);

        try {
            $strukturUjian = $this->model->getStrukturUjianSkema((int)$id_skema);
            // Kueri disesuaikan ke id_pengajuan
            $ujian = $this->model->where('id_pengajuan', $id_pengajuan)->where('id_skema', $id_skema)->first();
            $existingJawaban = $ujian ? $this->model->getExistingJawaban($ujian['id_ujian']) : [];

            return $this->respond([
                'success' => true,
                'struktur' => $strukturUjian,
                'ujian_data' => $ujian,
                'existing_jawaban' => $existingJawaban
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failForbidden();

        $data = $this->request->getPost();
        $service = new PertanyaanTertulisService();
        $result = $service->saveUjian($data);

        if ($result['success']) {
            return $this->respondCreated($result);
        }
        return $this->fail($result['message'], 400);
    }

    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failForbidden();
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Sesi ujian berhasil dihapus.']);
        }
        return $this->fail('Gagal menghapus sesi ujian.', 400);
    }
}
