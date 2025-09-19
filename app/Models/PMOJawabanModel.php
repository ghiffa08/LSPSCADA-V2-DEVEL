<?php

namespace App\Models;

use CodeIgniter\Model;

class PMOJawabanModel extends Model
{
    protected $table            = 'pmo_jawaban';
    protected $primaryKey       = 'id_jawaban';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pmo',
        'id_pertanyaan',
        'jawaban_ya_tidak',
        'jawaban_pilihan',
        'jawaban_essay',
        'tanggapan'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Melakukan "upsert" (update atau insert) untuk jawaban PMO.
     * Menghapus jawaban lama dan memasukkan yang baru untuk memastikan konsistensi.
     */
    public function upsertJawaban(int $id_pmo, array $jawabanData): bool
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Hapus semua jawaban yang ada untuk id_pmo ini
            $this->where('id_pmo', $id_pmo)->delete();

            $batchData = [];
            foreach ($jawabanData as $id_pertanyaan => $jawaban) {
                // Pastikan setidaknya salah satu jenis jawaban atau tanggapan diisi
                if (
                    !empty($jawaban['jawaban_ya_tidak']) ||
                    !empty($jawaban['jawaban_pilihan']) ||
                    !empty(trim($jawaban['jawaban_essay'])) ||
                    !empty(trim($jawaban['tanggapan']))
                ) {
                    $batchData[] = [
                        'id_pmo'           => $id_pmo,
                        'id_pertanyaan'    => $id_pertanyaan,
                        'jawaban_ya_tidak' => $jawaban['jawaban_ya_tidak'] ?? null,
                        'jawaban_pilihan'  => $jawaban['jawaban_pilihan'] ?? null,
                        'jawaban_essay'    => trim($jawaban['jawaban_essay'] ?? ''),
                        'tanggapan'        => trim($jawaban['tanggapan'] ?? ''),
                    ];
                }
            }

            // Jika ada data untuk dimasukkan, lakukan batch insert
            if (!empty($batchData)) {
                $this->insertBatch($batchData);
            }

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in upsertJawaban: ' . $e->getMessage());
            return false;
        }
    }
}
