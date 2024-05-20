<?php

namespace App\Models;

use CodeIgniter\Model;

class SubelemenModel extends Model
{
    protected $table            = 'subelemen';
    protected $primaryKey       = 'id_subelemen';
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

    public function deleteSubelemen($id)
    {
        $query = $this->db->table('subelemen')->delete(array('id_subelemen' => $id));
        return $query;
    }

    public function getAll()
    {
        return $this->db->table('subelemen')
            ->join('skema', 'skema.id_skema=subelemen.id_skema', 'left')
            ->join('unit', 'unit.id_unit=subelemen.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=subelemen.id_elemen', 'left')
            ->select('subelemen.pertanyaan, subelemen.id_subelemen, skema.id_skema, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen')
            ->Get()->getResultArray();
    }

    public function getbySkema($id_skema)
    {
        return $this->db->table('subelemen')
            ->where('subelemen.id_skema', $id_skema)
            ->join('skema', 'skema.id_skema=subelemen.id_skema', 'left')
            ->join('unit', 'unit.id_unit=subelemen.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=subelemen.id_elemen', 'left')
            ->select('subelemen.pertanyaan, subelemen.id_subelemen, subelemen.id_skema, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen')
            ->Get()->getResultArray();
    }

    public function getbyUnit($id_unit)
    {
        return $this->db->table('subelemen')
            ->where('subelemen.id_unit', $id_unit)
            ->join('skema', 'skema.id_skema=subelemen.id_skema', 'left')
            ->join('unit', 'unit.id_unit=subelemen.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=subelemen.id_elemen', 'left')
            ->select('subelemen.pertanyaan, subelemen.id_subelemen, subelemen.id_skema, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen')
            ->Get()->getResultArray();
    }
}
