<?php

namespace App\Services;

use App\Models\PertanyaanTertulisModel;
use CodeIgniter\Database\BaseConnection;
use Exception;

class PertanyaanTertulisService
{
    protected PertanyaanTertulisModel $ujianModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ujianModel = new PertanyaanTertulisModel();
        $this->db = \Config\Database::connect();
    }

    public function saveUjian(array $data): array
    {
        $this->db->transStart();
        try {
            $masterData = [
                'id_apl1'   => $data['id_apl1'],
                'id_skema'  => $data['id_skema'],
                'id_asesor' => $data['id_asesor'],
                'tanggal_ujian' => $data['tanggal_ujian'] ?? date('Y-m-d'),
                'catatan'   => $data['catatan'] ?? null,
            ];

            $ujian = $this->ujianModel->where('id_apl1', $masterData['id_apl1'])
                ->where('id_skema', $masterData['id_skema'])
                ->first();

            if ($ujian) {
                $id_ujian = $ujian['id_ujian'];
                $this->ujianModel->update($id_ujian, $masterData);
            } else {
                $id_ujian = $this->ujianModel->insert($masterData, true);
            }

            if (!$id_ujian) {
                throw new Exception('Gagal membuat atau memperbarui sesi ujian.');
            }

            $jawabanModel = new \App\Models\PertanyaanTertulisJawabanModel();
            $jawabanModel->upsertJawaban($id_ujian, $data['jawaban'] ?? []);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Transaksi database gagal.');
            }

            return ['success' => true, 'message' => 'Ujian berhasil disimpan.', 'id_ujian' => $id_ujian];
        } catch (Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
