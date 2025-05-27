<?php

namespace App\Models;

use CodeIgniter\Model;

class KomponenFeedbackModel extends Model
{
    protected $table            = 'komponen_feedback';
    protected $primaryKey       = 'id_komponen';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [
        'pernyataan',
        'urutan',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get all komponen ordered by urutan
     */
    public function getAllKomponen()
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }

    /**
     * Get maximum order value
     */
    public function getMaxUrutan()
    {
        $result = $this->selectMax('urutan')->first();
        return $result ? (int)$result['urutan'] : 0;
    }
}
