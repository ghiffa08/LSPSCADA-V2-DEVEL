<?php

namespace App\Models;

use CodeIgniter\Model;

class SkemaSiswaModel extends Model
{
    protected $table            = 'skema_siswa';
    protected $primaryKey       = 'id_skema_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

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

    public function getByIdSiswa($id_siswa)
    {
        // return $this->db->table('skema_siswa')
        //     ->where('id_siswa', $id_siswa)
        //     ->join('skema', 'skema.id_skema=skema_siswa.id_skema', 'left')
        //     ->join('users', 'users.id=skema_siswa.id_siswa', 'left')
        //     ->join('unit', 'unit.id_unit=skema_siswa.id_unit', 'left')
        //     ->select('skema_siswa.*, skema.nama_skema,users.fullname,unit.nama_unit ')
        //     ->Get()->getRowArray();
    }

    public function getSkemaSiswa($id_siswa)
    {
        return $this->db->table('skema_siswa')
            ->where('id_siswa', $id_siswa)
            ->join('skema', 'skema.id_skema=skema_siswa.id_skema', 'left')
            ->select('skema.nama_skema, skema.id_skema ')
            ->Get()->getRowArray();
    }
}
