<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class UnitModel extends Model
{
    use DataTableTrait;

    protected $table            = 'unit';
    protected $primaryKey       = 'id_unit';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_unit',
        'id_skema',
        'kode_unit',
        'nama_unit',
        'keterangan',
        'status',
        'created_at',
        'updated_at'
    ];

    // Validation
    protected $validationRules = [
        'id_skema'  => 'required|integer|is_not_unique[skema.id_skema]',
        'kode_unit' => 'required|max_length[20]',
        'nama_unit' => 'required|max_length[255]',
        'status'    => 'required|in_list[Y,N]'
    ];

    protected $validationMessages = [
        'id_skema' => [
            'is_not_unique' => 'Skema yang dipilih tidak valid'
        ]
    ];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['unit.nama_unit', 'unit.keterangan'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema = unit.id_skema')
            ->where('unit.status', 'Y')
            ->where('skema.status', 'Y')
            ->orderBy('skema.nama_skema, unit.kode_unit');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('unit.*, skema.nama_skema');
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

    // Callbacks
    protected $beforeInsert = ['cleanData', 'validateScheme'];
    protected $beforeUpdate = ['cleanData', 'validateScheme'];

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
     * Validate that the scheme exists and is active
     */
    protected function validateScheme(array $data): array
    {
        $skemaModel = new SkemaModel();
        if (!$skemaModel->isValidScheme($data['data']['id_skema'])) {
            throw new \RuntimeException('Skema tidak valid atau tidak aktif');
        }
        return $data;
    }

    /**
     * Get all active units with scheme information
     */
    public function getActiveUnits(): array
    {
        return $this->select('unit.*, skema.nama_skema')
            ->join('skema', 'skema.id_skema = unit.id_skema')
            ->where('unit.status', 'Y')
            ->where('skema.status', 'Y')
            ->orderBy('skema.nama_skema, unit.kode_unit')
            ->findAll();
    }

    /**
     * Get units by scheme ID (only active)
     */
    public function getUnitsByScheme(int $id_skema): array
    {
        return $this->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->orderBy('kode_unit')
            ->findAll();
    }

    /**
     * Get units for dropdown by scheme ID
     */
    public function getUnitsForDropdown(int $id_skema): array
    {
        $units = $this->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->orderBy('kode_unit')
            ->findAll();

        $dropdown = [];
        foreach ($units as $unit) {
            $dropdown[$unit['id_unit']] = $unit['kode_unit'] . ' - ' . $unit['nama_unit'];
        }

        return $dropdown;
    }

    /**
     * Delete a unit and its related data (with transaction)
     */
    public function deleteUnit(int $id_unit): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Delete related elements first
            $this->db->table('elemen')
                ->where('id_unit', $id_unit)
                ->delete();

            // Delete the unit
            $this->where('id_unit', $id_unit)->delete();

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to delete unit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if unit exists and is active
     */
    public function isValidUnit(int $id_unit): bool
    {
        return $this->where('id_unit', $id_unit)
            ->where('status', 'Y')
            ->countAllResults() > 0;
    }

    /**
     * Get unit details with scheme information
     */
    public function getUnitWithScheme(int $id_unit): ?array
    {
        return $this->select('unit.*, skema.nama_skema')
            ->join('skema', 'skema.id_skema = unit.id_skema')
            ->where('unit.id_unit', $id_unit)
            ->first();
    }
}
