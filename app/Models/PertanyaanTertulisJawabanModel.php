<?php

namespace App\Models;

use CodeIgniter\Model;

class PertanyaanTertulisJawabanModel extends Model
{
    protected $table            = 'pertanyaan_tertulis_jawaban';
    protected $primaryKey       = 'id_jawaban';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_ujian',
        'id_soal',
        'jawaban_pilihan',
        'jawaban_essay',
        'jawaban_benar_salah'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function upsertJawaban(int $id_ujian, array $jawabanData): bool
    {
        $this->where('id_ujian', $id_ujian)->delete();

        if (empty($jawabanData)) {
            return true;
        }

        $batchData = [];
        foreach ($jawabanData as $id_soal => $jawaban) {
            $batchData[] = [
                'id_ujian'           => $id_ujian,
                'id_soal'            => $id_soal,
                'jawaban_pilihan'    => $jawaban['jawaban_pilihan'] ?? null,
                'jawaban_essay'      => trim($jawaban['jawaban_essay'] ?? ''),
                'jawaban_benar_salah' => $jawaban['jawaban_benar_salah'] ?? null,
            ];
        }

        return $this->insertBatch($batchData);
    }
}
