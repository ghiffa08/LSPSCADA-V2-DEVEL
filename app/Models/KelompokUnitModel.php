<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokUnitModel extends Model
{
    protected $table = 'kelompok_unit';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'id_kelompok',
        'id_unit'
    ];

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
    protected $validationRules = [
        'id_kelompok' => 'required|numeric',
        'id_unit'     => 'required|numeric',
    ];
    protected $validationMessages = [
        'id_kelompok' => [
            'required' => 'ID Kelompok harus diisi',
            'numeric'  => 'ID Kelompok harus berupa angka',
        ],
        'id_unit' => [
            'required' => 'ID Unit harus diisi',
            'numeric'  => 'ID Unit harus berupa angka',
        ],
    ];
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


    /**
     * Get units for a specific kelompok
     *
     * @param int $kelompokId
     * @return array
     */
    public function getUnitsByKelompokId($kelompokId)
    {
        $builder = $this->db->table($this->table . ' ku');
        $builder->select('ku.id, ku.id_unit, u.kode_unit, u.nama_unit');
        $builder->join('unit u', 'u.id_unit = ku.id_unit', 'left');
        $builder->where('ku.id_kelompok', $kelompokId);
        $builder->orderBy('u.kode_unit', 'ASC');

        $query = $builder->get();
        return $query->getResultArray();
    }

    /**
     * Get units with details for a specific kelompok
     *
     * @param int $kelompokId
     * @return array
     */
    public function getUnitsWithDetailsByKelompokId($kelompokId)
    {
        $builder = $this->db->table($this->table . ' ku');
        $builder->select('ku.id, ku.id_unit, u.kode_unit, u.nama_unit, u.jenis, u.level');
        $builder->join('unit u', 'u.id_unit = ku.id_unit', 'left');
        $builder->where('ku.id_kelompok', $kelompokId);
        $builder->orderBy('u.kode_unit', 'ASC');

        $query = $builder->get();
        return $query->getResultArray();
    }

    /**
     * Check if a unit is already assigned to a kelompok
     *
     * @param int $kelompokId
     * @param int $unitId
     * @return bool
     */
    public function isUnitAssigned($kelompokId, $unitId)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id_kelompok', $kelompokId);
        $builder->where('id_unit', $unitId);

        return ($builder->countAllResults() > 0);
    }
}
