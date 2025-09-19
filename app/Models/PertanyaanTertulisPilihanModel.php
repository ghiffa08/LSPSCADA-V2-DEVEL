<?php

namespace App\Models;

use CodeIgniter\Model;

class PertanyaanTertulisPilihanModel extends Model
{
    protected $table            = 'pertanyaan_tertulis_pilihan';
    protected $primaryKey       = 'id_pilihan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_soal',
        'pilihan',
        'is_benar',
        'urutan'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
