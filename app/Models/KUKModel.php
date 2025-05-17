<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class KUKModel extends Model
{
    use DataTableTrait;

    protected $table            = 'kuk';
    protected $primaryKey       = 'id_kuk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'id_kuk',
        'id_skema',
        'id_unit',
        'id_elemen',
        'kode_kuk',
        'nama_kuk',
        'pertanyaan'
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
        'id_skema'     => 'required|integer',
        'id_unit'      => 'required|integer',
        'id_elemen'    => 'required|integer',
        'kode_kuk'     => 'required|max_length[10]',
        'nama_kuk'     => 'required|max_length[255]',
        'pertanyaan'   => 'max_length[255]',
    ];

    protected $validationMessages = [
        'id_kuk' => [
            'integer' => 'ID KUK harus berupa angka.'
        ],
        'id_skema' => [
            'required' => 'ID Skema wajib diisi.',
            'integer'  => 'ID Skema harus berupa angka.'
        ],
        'id_unit' => [
            'required' => 'ID Unit wajib diisi.',
            'integer'  => 'ID Unit harus berupa angka.'
        ],
        'id_elemen' => [
            'required' => 'ID Elemen wajib diisi.',
            'integer'  => 'ID Elemen harus berupa angka.'
        ],
        'kode_kuk' => [
            'required'   => 'Kode KUK wajib diisi.',
            'max_length' => 'Kode KUK tidak boleh lebih dari 10 karakter.'
        ],
        'nama_kuk' => [
            'required'   => 'Nama KUK wajib diisi.',
            'max_length' => 'Nama KUK tidak boleh lebih dari 255 karakter.'
        ],
        'pertanyaan' => [
            'max_length' => 'Pertanyaan tidak boleh lebih dari 500 karakter.'
        ]
    ];

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

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['kuk.nama_kuk', 'skema.nama_skema'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema = kuk.id_skema')
            ->join('unit', 'unit.id_unit = kuk.id_unit')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen')
            ->orderBy('skema.nama_skema, unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('kuk.*, 
                skema.nama_skema, 
                unit.nama_unit, 
                elemen.nama_elemen');
    }

    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults($data)
    {
        // You can transform data here if needed
        // For example, format dates, calculate values, etc.
        return $data;
    }

    /**
     * Get all KUK with related data
     */
    public function getAllWithRelations(): array
    {
        return $this->select('kuk.*, 
                skema.nama_skema, 
                unit.nama_unit, 
                elemen.nama_elemen')
            ->join('skema', 'skema.id_skema = kuk.id_skema')
            ->join('unit', 'unit.id_unit = kuk.id_unit')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen')
            ->orderBy('skema.nama_skema, unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk')
            ->findAll();
    }

    /**
     * Get KUK by ID with full relations
     */
    public function getWithRelations(int $id_kuk): ?array
    {
        return $this->select('kuk.*, 
                skema.nama_skema, 
                unit.nama_unit, 
                elemen.nama_elemen')
            ->join('skema', 'skema.id_skema = kuk.id_skema')
            ->join('unit', 'unit.id_unit = kuk.id_unit')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen')
            ->where('kuk.id_kuk', $id_kuk)
            ->first();
    }

    /**
     * Get KUKs by Skema ID
     */
    public function getBySkema(int $id_skema): array
    {
        return $this->where('kuk.id_skema', $id_skema)
            ->select('kuk.*, unit.nama_unit, elemen.nama_elemen')
            ->join('unit', 'unit.id_unit = kuk.id_unit')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen')
            ->orderBy('unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk')
            ->findAll();
    }

    /**
     * Get KUKs by Unit ID
     */
    public function getByUnit(int $id_unit): array
    {
        return $this->where('kuk.id_unit', $id_unit)
            ->select('kuk.*, elemen.nama_elemen')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen')
            ->orderBy('elemen.nama_elemen, kuk.kode_kuk')
            ->findAll();
    }

    /**
     * Get KUKs by Elemen ID
     */
    public function getByElemen(int $id_elemen): array
    {
        return $this->where('kuk.id_elemen', $id_elemen)
            ->orderBy('kuk.kode_kuk')
            ->findAll();
    }

    /**
     * Get KUKs for export
     */
    public function getForExport(): array
    {
        return $this->select('id_skema, id_unit, id_elemen, kode_kuk, nama_kuk, pertanyaan')
            ->orderBy('id_skema, id_unit, id_elemen, kode_kuk')
            ->findAll();
    }

    /**
     * Check if KUK code exists
     */
    public function isCodeExists(string $kode_kuk, ?int $exclude_id = null): bool
    {
        $builder = $this->where('kode_kuk', $kode_kuk);

        if ($exclude_id) {
            $builder->where('id_kuk !=', $exclude_id);
        }

        return $builder->countAllResults() > 0;
    }
}
