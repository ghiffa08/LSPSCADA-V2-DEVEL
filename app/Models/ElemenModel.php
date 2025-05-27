<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class ElemenModel extends Model
{
    use DataTableTrait;

    protected $table            = 'elemen';
    protected $primaryKey       = 'id_elemen';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_elemen',
        'id_skema',
        'id_unit',
        'kode_elemen',
        'nama_elemen'
    ];

    // protected $useTimestamps = false;

    protected $validationRules = [
        'id_skema'     => 'required|integer',
        'id_unit'      => 'required|integer',
        'kode_elemen'  => 'required|max_length[10]',
        'nama_elemen'  => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'id_skema' => [
            'required' => 'ID Skema wajib diisi.',
            'integer'  => 'ID Skema harus berupa angka.'
        ],
        'id_unit' => [
            'required' => 'ID Unit wajib diisi.',
            'integer'  => 'ID Unit harus berupa angka.'
        ],
        'kode_elemen' => [
            'required'     => 'Kode Elemen wajib diisi.',
            'max_length'   => 'Kode Elemen tidak boleh lebih dari 10 karakter.'
        ],
        'nama_elemen' => [
            'required'     => 'Nama Elemen wajib diisi.',
            'max_length'   => 'Nama Elemen tidak boleh lebih dari 255 karakter.'
        ],
    ];


    // Fields that should be searched when using DataTable
    protected array $dataTableSearchFields = ['elemen.kode_elemen', 'elemen.nama_elemen'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder->join('skema', 'skema.id_skema = elemen.id_skema')
            ->join('unit', 'unit.id_unit = elemen.id_unit')
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
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('elemen.*, skema.nama_skema, unit.nama_unit');
    }

    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults(array $data): array
    {
        // You can transform data here if needed
        // For example, format dates, calculate values, etc.
        return $data;
    }

    /**
     * Mendapatkan semua elemen dengan nama unit
     * @return array
     */
    public function getAllElements(): array
    {
        return $this->select('elemen.*, unit.nama_unit')
            ->join('unit', 'unit.id_unit = elemen.id_unit', 'left')
            ->findAll();
    }

    /**
     * Mendapatkan elemen berdasarkan ID unit
     * @param int $id_unit
     * @return array
     */
    public function getElementsByUnit(int $id_unit): array
    {
        return $this->where('id_unit', $id_unit)
            ->orderBy('nama_elemen')
            ->findAll();
    }

    /**
     * Mendapatkan elemen (opsional: berdasarkan ID unit)
     * (Diubah dari getActiveElements karena tidak ada status)
     * @param int|null $id_unit
     * @return array
     */
    public function getElements(int $id_unit = null): array
    {
        $builder = $this->builder();

        if ($id_unit !== null) {
            $builder->where('id_unit', $id_unit);
        }

        return $builder->orderBy('nama_elemen')
            ->get()
            ->getResultArray();
    }

    /**
     * Memeriksa apakah elemen valid
     * (Diubah dari isValidElement karena tidak ada status)
     * @param int $id_elemen
     * @return bool
     */
    public function elementExists(int $id_elemen): bool
    {
        return $this->where('id_elemen', $id_elemen)
            ->countAllResults() > 0;
    }
}
