<?php

namespace App\Models;

use CodeIgniter\Model;

class ElemenModel extends Model
{
    protected $table            = 'elemen';
    protected $primaryKey       = 'id_elemen';
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

    public function AllElemen()
    {
        $Elemen = new ElemenModel();
        $Elemen->select('elemen.*, u.nama_unit'); // Menggunakan alias 'u' untuk tabel unit
        $Elemen->join('unit as u', 'u.id_unit = elemen.id_unit', 'left'); // Menggunakan alias 'u' untuk tabel unit
        return $Elemen->findAll();
    }

    public function getElemen($id_unit)
    {
        return $this->db->table('elemen')
            ->where('id_unit', $id_unit)
            ->get()
            ->getResultArray();
    }

    public function deleteElemen($id)
    {
        $query = $this->db->table('elemen')->delete(array('id_elemen' => $id));
        return $query;
    }
}
