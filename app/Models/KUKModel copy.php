<?php

namespace App\Models;

use Config\Database;
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
    protected $allowedFields    = ['id_skema', 'id_unit', 'id_elemen', 'kode_kuk', 'nama_kuk', 'pertanyaan'];

    // Validation
    // protected $validationRules = [
    //     'id_skema'   => 'required|integer|is_not_unique[skema.id_skema]',
    //     'id_unit'    => 'required|integer|is_not_unique[unit.id_unit]',
    //     'id_elemen'  => 'required|integer|is_not_unique[elemen.id_elemen]',
    //     'kode_kuk'   => 'required|max_length[25]|regex_match[/^[A-Z0-9.\/]+$/]',
    //     'nama_kuk'   => 'required|max_length[255]',
    //     'pertanyaan' => 'required'
    // ];

    // protected $validationMessages = [
    //     'id_skema' => [
    //         'is_not_unique' => 'Skema yang dipilih tidak valid'
    //     ],
    //     'id_unit' => [
    //         'is_not_unique' => 'Unit yang dipilih tidak valid'
    //     ],
    //     'id_elemen' => [
    //         'is_not_unique' => 'Elemen yang dipilih tidak valid'
    //     ],
    //     'kode_kuk' => [
    //         'regex_match' => 'Kode KUK hanya boleh berisi huruf besar, angka, titik dan slash'
    //     ]
    // ];

    // Callbacks
    protected $beforeInsert = ['cleanData', 'validateRelations'];
    protected $beforeUpdate = ['cleanData', 'validateRelations'];

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
     * Clean and prepare data before insert/update
     */
    protected function cleanData(array $data): array
    {
        foreach ($data['data'] as $key => $value) {
            if (is_string($value)) {
                $data['data'][$key] = trim($value);
            }
        }
        return $data;
    }

    /**
     * Validate relational integrity before insert/update
     */
    protected function validateRelations(array $data): array
    {
        $db = Database::connect();

        // Validate skema exists
        if (!$db->table('skema')->where('id_skema', $data['data']['id_skema'])->countAllResults()) {
            throw new \RuntimeException('Skema tidak ditemukan');
        }

        // Validate unit exists and belongs to skema
        if (!$db->table('unit')
            ->where('id_unit', $data['data']['id_unit'])
            ->where('id_skema', $data['data']['id_skema'])
            ->countAllResults()) {
            throw new \RuntimeException('Unit tidak valid atau tidak termasuk dalam skema yang dipilih');
        }

        // Validate elemen exists and belongs to unit
        if (!$db->table('elemen')
            ->where('id_elemen', $data['data']['id_elemen'])
            ->where('id_unit', $data['data']['id_unit'])
            ->countAllResults()) {
            throw new \RuntimeException('Elemen tidak valid atau tidak termasuk dalam unit yang dipilih');
        }

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
