<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesmenModel extends Model
{
    protected $table            = 'asesmen';
    protected $primaryKey       = 'id_asesmen';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_skema', 'id_tuk', 'id_tanggal', 'tujuan'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getAllAsesmen()
    {

        return $this->db->table('asesmen')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('asesmen.id_asesmen, asesmen.id_skema, asesmen.id_tuk, asesmen.id_tanggal, asesmen.tujuan, skema.nama_skema, skema.jenis_skema, tuk.nama_tuk, DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal')
            ->Get()->getResultArray();
    }

    public function getJadwal($id_skema)
    {
        return $this->db->table('asesmen')
            ->where('id_skema', $id_skema)
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('asesmen.id_asesmen, asesmen.id_tanggal, DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal, asesmen.id_tuk, tuk.nama_tuk')
            ->Get()->getResultArray();
    }
    public function getTuk($id_skema)
    {
        return $this->db->table('asesmen')
            ->where('id_skema', $id_skema)
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->select('asesmen.id_tuk, tuk.nama_tuk')
            ->Get()->getResultArray();
    }
}
