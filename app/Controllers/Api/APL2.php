<?php

namespace App\Controllers\Api;

use Config\Database;
use App\Models\APL2Model;
use App\Models\PengajuanAsesmenModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use Exception;

class APL2 extends DataTableController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        helper('auth');
        $this->model = new APL2Model();
        $this->db = Database::connect();

        $this->columnMap = [
            1 => 'nama_asesi',
            2 => 'nama_skema',
            3 => 'created_at',
            4 => 'validasi_apl2',
            5 => 'nama_asesor',
        ];
    }

    public function getDataTable(): ResponseInterface
    {
        $request = service('request');
        $postData = $request->getPost();

        $limit = (int) ($postData['length'] ?? 10);
        $start = (int) ($postData['start'] ?? 0);
        $search = $postData['search']['value'] ?? '';

        $orderColumnIndex = $postData['order'][0]['column'] ?? null;
        $orderDir = $postData['order'][0]['dir'] ?? 'asc';
        $orderColumn = $this->columnMap[$orderColumnIndex] ?? null;

        $result = $this->model->getDataTable($limit, $start, $search, $orderColumn, $orderDir);

        $output = [
            "draw"            => (int) ($postData['draw'] ?? 0),
            "recordsTotal"    => $result['total'],
            "recordsFiltered" => $result['filtered'],
            "data"            => $result['data'],
            "csrf_token"      => csrf_hash()
        ];

        return $this->respond($output);
    }

    public function getById($id_pengajuan = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        if (!$id_pengajuan) return $this->fail('ID Pengajuan diperlukan.', 400);

        try {
            $pengajuanModel = new PengajuanAsesmenModel();
            $pengajuan = $pengajuanModel
                ->select('
                    pengajuan_asesmen.id_pengajuan,
                    user_asesi.nama_lengkap as nama_asesi,
                    asesi.nik,
                    skema.nama_skema,
                    skema.kode_skema,
                    user_asesor.nama_lengkap as nama_asesor,
                    apl2.validasi_apl2,
                    apl2.catatan,
                    apl2.created_at
                ')
                ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                ->join('users as user_asesi', 'user_asesi.id = asesi.id_user')
                ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                ->join('skema', 'skema.id_skema = asesmen.id_skema')
                ->join('users as user_asesor', 'user_asesor.id = pengajuan_asesmen.id_asesor', 'left')
                ->join('apl2', 'apl2.id_pengajuan = pengajuan_asesmen.id_pengajuan', 'left')
                ->where('pengajuan_asesmen.id_pengajuan', $id_pengajuan)
                ->first();

            if (!$pengajuan) return $this->failNotFound('Data pengajuan tidak ditemukan.');

            return $this->respond(['status' => true, 'data' => ['pengajuan' => $pengajuan]]);
        } catch (Exception $e) {
            return $this->fail('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function validateApl2($id_pengajuan = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) return $this->failUnauthorized();
        if (!$id_pengajuan) return $this->fail('ID Pengajuan tidak valid', 400);

        $postData = $this->request->getPost();
        $status = $postData['status_pengajuan'] ?? null;
        $catatan = $postData['catatan_penolakan'] ?? null;

        if (!in_array($status, ['validated', 'unvalid'])) return $this->fail('Status validasi tidak valid.', 400);
        if ($status === 'unvalid' && empty($catatan)) return $this->fail('Catatan penolakan wajib diisi.', 400);

        try {
            $apl2 = $this->model->where('id_pengajuan', $id_pengajuan)->first();
            if (!$apl2) return $this->failNotFound('Data APL2 tidak ditemukan.');

            $updateData = ['validasi_apl2' => $status];
            if ($status === 'unvalid') {
                $updateData['catatan'] = $catatan; // Asumsi ada kolom catatan di apl2
            }

            $this->model->update($apl2['id_apl2'], $updateData);

            return $this->respondCreated(['success' => true, 'message' => 'Validasi APL2 berhasil diperbarui.', 'token' => csrf_hash()]);
        } catch (Exception $e) {
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}