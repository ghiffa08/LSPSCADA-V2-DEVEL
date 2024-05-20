<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table            = 'unit';
    protected $primaryKey       = 'id_unit';
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

    public function AllUnit()
    {
        $Unit = new UnitModel();
        $Unit->select('unit.*, skema.nama_skema');
        $Unit->join('skema', 'skema.id_skema = unit.id_skema', 'left');
        return $Unit->findAll();
    }

    public function getUnit($id_skema)
    {
        return $this->db->table('unit')
            ->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->get()
            ->getResultArray();
    }

    public function deleteUnit($id)
    {
        $query = $this->db->table('unit')->delete(array('id_unit' => $id));
        return $query;
    }
}
