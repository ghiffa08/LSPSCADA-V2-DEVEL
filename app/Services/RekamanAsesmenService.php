<?php

namespace App\Services;

use App\Models\RekamanAsesmenModel;
use App\Models\RekamanAsesmenKompetensiModel;
use CodeIgniter\Database\BaseConnection;

class RekamanAsesmenService
{
    protected RekamanAsesmenModel $rekamanModel;
    protected RekamanAsesmenKompetensiModel $kompetensiModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->rekamanModel = new RekamanAsesmenModel();
        $this->kompetensiModel = new RekamanAsesmenKompetensiModel();
        $this->db = \Config\Database::connect();
    }

    public function saveRekaman(array $data): array
    {
        $this->db->transStart();

        try {
            // Validation
            if (empty($data['id_apl1']) || empty($data['rekomendasi'])) {
                throw new \Exception('Data tidak lengkap');
            }

            // Save main record
            $rekamanData = [
                'id_apl1' => $data['id_apl1'],
                'tanggal_asesmen' => $data['tanggal_asesmen'],
                'rekomendasi' => $data['rekomendasi'],
                'catatan' => $data['catatan'] ?? '',
                'tindak_lanjut' => $data['tindak_lanjut'] ?? '',
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $existing = $this->rekamanModel->where('id_apl1', $data['id_apl1'])->first();

            if ($existing) {
                $this->rekamanModel->update($existing['id'], $rekamanData);
                $rekamanId = $existing['id'];
            } else {
                $rekamanData['created_at'] = date('Y-m-d H:i:s');
                $rekamanId = $this->rekamanModel->insert($rekamanData);
            }

            // Save kompetensi data
            if (!empty($data['kompetensi'])) {
                $this->saveKompetensiData($rekamanId, $data['kompetensi']);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Rekaman asesmen berhasil disimpan',
                'data' => ['id_rekaman' => $rekamanId]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'RekamanAsesmenService Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ];
        }
    }

    private function saveKompetensiData(int $rekamanId, array $kompetensiData): void
    {
        // Delete existing
        $this->kompetensiModel->where('id_rekaman', $rekamanId)->delete();

        // Insert new data
        foreach ($kompetensiData as $idUnit => $methods) {
            $record = [
                'id_rekaman' => $rekamanId,
                'id_unit' => $idUnit,
                'metode_observasi' => isset($methods['observasi']) ? 1 : 0,
                'metode_portofolio' => isset($methods['portofolio']) ? 1 : 0,
                'metode_pihak_ketiga' => isset($methods['pihak_ketiga']) ? 1 : 0,
                'metode_lisan' => isset($methods['lisan']) ? 1 : 0,
                'metode_tertulis' => isset($methods['tertulis']) ? 1 : 0,
                'metode_proyek' => isset($methods['proyek']) ? 1 : 0,
                'metode_lainnya' => isset($methods['lainnya']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->kompetensiModel->insert($record);
        }
    }
}
