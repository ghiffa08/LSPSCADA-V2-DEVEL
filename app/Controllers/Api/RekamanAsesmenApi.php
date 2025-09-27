<?php

namespace App\Services;

use App\Models\RekamanAsesmenModel;
use CodeIgniter\Database\BaseConnection;

class RekamanAsesmenApi
{
    protected RekamanAsesmenModel $rekamanModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->rekamanModel = new RekamanAsesmenModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Mengambil daftar APL1 (asesi) yang sudah divalidasi per asesmen.
     */
    public function getApl1ByAsesmen(int $id_asesmen): array
    {
        try {
            $apl1List = $this->db->table('apl1')
                ->select('apl1.id_apl1, apl1.nama_siswa as nama_asesi, apl1.nik, apl1.email, apl1.validasi_apl1 as status_pengajuan')
                ->where('apl1.id_asesmen', $id_asesmen)
                ->where('apl1.validasi_apl1', 'validated')
                ->orderBy('apl1.nama_siswa', 'ASC')
                ->get()->getResultArray();

            return ['success' => true, 'data' => $apl1List];
        } catch (\Exception $e) {
            log_message('error', '[Service::getApl1ByAsesmen] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memuat daftar asesi.'];
        }
    }

    /**
     * Memuat semua data yang dibutuhkan untuk menampilkan form rekaman asesmen.
     */
    public function getRekamanWithDetailsByApl1(string $id_apl1, int $id_asesor): array
    {
        try {
            $rekaman = $this->getOrCreateRekaman($id_apl1, $id_asesor);
            if (!$rekaman) {
                throw new \Exception('Gagal mendapatkan atau membuat record rekaman.');
            }

            $apl1Data = $this->rekamanModel->getApl1Data($id_apl1);
            if (!$apl1Data) {
                throw new \Exception('Data APL-01 tidak ditemukan.');
            }

            $units = $this->rekamanModel->getUnitsBySkema($apl1Data['id_skema']);
            $existingData = $this->rekamanModel->getExistingById($rekaman['id']);

            return [
                'success' => true,
                'data' => [
                    'rekaman'             => $rekaman,
                    'units'               => $units,
                    'existing_data'       => $existingData,
                    'existing_rekaman_id' => $rekaman['id'],
                    'rekaman_data'        => $rekaman
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', '[Service::getRekamanWithDetailsByApl1] ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Menangani auto-save untuk satu checkbox metode.
     */
    public function autoSaveUnit(array $data): array
    {
        try {
            $rekaman = $this->getOrCreateRekaman($data['id_apl1'], $data['id_asesor']);
            $methodData = [$data['method_key'] => $data['method_value']];
            $this->saveUnitKompetensi($rekaman['id'], $data['id_unit'], $methodData);

            return ['success' => true, 'message' => 'Perubahan disimpan.', 'data' => ['id_rekaman' => $rekaman['id']]];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Menangani penyimpanan massal (batch) dari bulk check.
     */
    public function saveBatchUnits(array $data): array
    {
        try {
            $rekaman = $this->getOrCreateRekaman($data['id_apl1'], $data['id_asesor']);
            $this->rekamanModel->saveBulkKompetensiDetails($rekaman['id'], $data['kompetensi']);
            return ['success' => true, 'message' => 'Semua perubahan berhasil disimpan.', 'data' => ['id_rekaman' => $rekaman['id']]];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Menangani penyimpanan final (rekomendasi & catatan).
     * PERBAIKAN FINAL: Logika get-or-create dipindahkan ke sini.
     */
    public function saveRekaman(array $data): array
    {
        try {
            // Langkah 1: Selalu dapatkan atau buat record master yang benar
            $rekaman = $this->getOrCreateRekaman($data['id_apl1'], $data['id_asesor']);
            if (!$rekaman) {
                throw new \Exception('Gagal memproses record utama rekaman.');
            }
            $id_rekaman = $rekaman['id'];

            // Langkah 2: Siapkan data yang akan diupdate
            $updateData = [
                'rekomendasi'   => $data['rekomendasi'] ?? 'belum_kompeten',
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'komentar'      => $data['komentar'] ?? null,
            ];

            // Langkah 3: Gunakan Query Builder langsung untuk memastikan UPDATE terjadi
            $this->db->table('rekaman_asesmen')->where('id', $id_rekaman)->update($updateData);

            return ['success' => true, 'message' => 'Catatan berhasil disimpan.', 'data' => ['id_rekaman' => $id_rekaman]];
        } catch (\Exception $e) {
            log_message('error', '[Service::saveRekaman] ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Helper: Mengecek rekaman yang ada atau membuat yang baru.
     */
    private function getOrCreateRekaman(string $id_apl1, int $id_asesor): ?array
    {
        $existing = $this->rekamanModel->where(['id_apl1' => $id_apl1, 'id_asesor' => $id_asesor])->first();
        if ($existing) {
            return $existing;
        }
        $newId = $this->rekamanModel->insert([
            'id_apl1' => $id_apl1,
            'id_asesor' => $id_asesor,
            'tanggal_rekaman' => date('Y-m-d'),
            'rekomendasi' => 'belum_kompeten'
        ]);
        return $newId ? $this->rekamanModel->find($newId) : null;
    }

    /**
     * Helper: Menyimpan detail kompetensi untuk satu unit (upsert).
     */
    private function saveUnitKompetensi(int $id_rekaman, int $id_unit, array $methodData): bool
    {
        $table = 'rekaman_asesmen_kompetensi';
        $existing = $this->db->table($table)->where(['id_rekaman' => $id_rekaman, 'id_unit' => $id_unit])->get()->getRowArray();
        if ($existing) {
            return $this->db->table($table)->where('id', $existing['id'])->update($methodData);
        } else {
            $methodData['id_rekaman'] = $id_rekaman;
            $methodData['id_unit'] = $id_unit;
            return $this->db->table($table)->insert($methodData);
        }
    }
}
