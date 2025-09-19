<?php

namespace App\Models;

use CodeIgniter\Model;

class PmoPilihanJawabanModel extends Model
{
    protected $table            = 'pmo_pilihan_jawaban';
    protected $primaryKey       = 'id_pilihan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pertanyaan', 'pilihan', 'urutan'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
