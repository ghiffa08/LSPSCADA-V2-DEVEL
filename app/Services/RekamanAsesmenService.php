<?php

namespace App\Services;

use App\Models\RekamanAsesmenModel;
use CodeIgniter\Database\BaseConnection;

class RekamanAsesmenService
{
    protected RekamanAsesmenModel $rekamanModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->rekamanModel = new RekamanAsesmenModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Mengambil atau membuat record rekaman master.
     */
    public function getOrCreateRekaman(string $id_apl1, int $id_asesor): ?array
    {
        $existing = $this->rekamanModel->where(['id_apl1' => $id_apl1, 'id_asesor' => $id_asesor])->first();
        if ($existing) {
            return $existing;
        }

        $newId = $this->rekamanModel->insert([
            'id_apl1' => $id_apl1, 'id_asesor' => $id_asesor,
            'tanggal_rekaman' => date('Y-m-d'), 'rekomendasi' => 'belum_kompeten'
        ]);

        return $newId ? $this->rekamanModel->find($newId) : null;
    }
    
    /**
     * Menyimpan detail kompetensi untuk satu unit (single check).
     */
    public function saveUnitKompetensi(int $id_rekaman, int $id_unit, array $methodData): bool
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
    
    
    // Di dalam RekamanAsesmenService.php
public function getRekamanWithDetailsByApl1(string $id_apl1, int $id_asesor): array
{
    try {
      
        $rekaman = $this->getOrCreateRekaman($id_apl1, $id_asesor);
        $apl1Data = $this->rekamanModel->getApl1Data($id_apl1); 
        
        if (!$apl1Data) { throw new \Exception('Data APL-01 tidak ditemukan.'); }

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
     * Mengambil daftar APL1 (asesi) yang sudah divalidasi per asesmen.
     * Metode ini dibutuhkan oleh dropdown di halaman form.
     */
    public function getApl1ByAsesmen(int $id_asesmen): array
    {
        try {
            // Query untuk mengambil daftar asesi yang sudah divalidasi
            $apl1List = $this->db->table('apl1')
                ->select('apl1.id_apl1, apl1.nama_siswa as nama_asesi, apl1.nik, apl1.email, apl1.validasi_apl1 as status_pengajuan')
                ->where('apl1.id_asesmen', $id_asesmen)
                ->where('apl1.validasi_apl1', 'validated') // Hanya yang sudah divalidasi
                ->orderBy('apl1.nama_siswa', 'ASC')
                ->get()->getResultArray();

            return ['success' => true, 'data' => $apl1List];

        } catch (\Exception $e) {
            log_message('error', '[Service::getApl1ByAsesmen] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memuat daftar asesi.'];
        }
    }

    /**
     * Menyimpan semua detail kompetensi (bulk check).
     */
    public function saveBatchKompetensi(int $id_rekaman, array $kompetensiData): bool
    {
        // Model sudah memiliki metode ini, kita hanya perlu memanggilnya.
        // Pastikan metode ini public di RekamanAsesmenModel Anda.
        return $this->rekamanModel->saveBulkKompetensiDetails($id_rekaman, $kompetensiData);
    }
    
  /**
     * Menangani penyimpanan data final (rekomendasi & catatan).
     * PERBAIKAN FINAL: Kembali menggunakan metode update() dari Model.
     */
    public function saveRekaman(array $data): array
    {
        try {
            // Dapatkan atau buat record master terlebih dahulu untuk memastikan ID valid
            $rekaman = $this->getOrCreateRekaman($data['id_apl1'], $data['id_asesor']);
            $id_rekaman = $rekaman['id'];

            // Siapkan data yang HANYA akan diupdate
            $updateData = [
                'rekomendasi'   => $data['rekomendasi'] ?? 'belum_kompeten',
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'komentar'      => $data['komentar'] ?? null,
            ];

            // Panggil metode update() dari Model. Ini cara yang paling benar.
            if ($this->rekamanModel->update($id_rekaman, $updateData)) {
                return ['success' => true, 'message' => 'Catatan berhasil diperbarui.'];
            } else {
                // Jika update gagal, tampilkan error dari model
                $errors = $this->rekamanModel->errors();
                throw new \Exception('Gagal menyimpan ke database: ' . ($errors ? implode(', ', $errors) : 'Tidak ada data yang berubah.'));
            }

        } catch (\Exception $e) {
            log_message('error', '[Service::saveRekaman] ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
     /**
     * Menyimpan data final seperti rekomendasi, tindak lanjut, dan komentar.
     *
     * @param integer $id_rekaman ID dari record rekaman_asesmen.
     * @param array   $data       Data yang dikirim dari frontend.
     * @return boolean
     */
    public function saveFinalData(int $id_rekaman, array $data): bool
    {
        // Siapkan data yang akan diupdate, saring hanya field yang relevan
        $updateData = [];

        // Periksa apakah setiap kunci ada dalam data yang dikirim sebelum menambahkannya
        if (array_key_exists('rekomendasi', $data)) {
            $updateData['rekomendasi'] = $data['rekomendasi'];
        }
        if (array_key_exists('tindak_lanjut', $data)) {
            $updateData['tindak_lanjut'] = $data['tindak_lanjut'];
        }
        if (array_key_exists('komentar', $data)) {
            $updateData['komentar'] = $data['komentar'];
        }

        // Jika tidak ada data untuk diupdate, kembalikan true (tidak ada error)
        if (empty($updateData)) {
            return true;
        }

        // Gunakan method update dari model untuk menyimpan data
        return $this->rekamanModel->update($id_rekaman, $updateData);
    }
}