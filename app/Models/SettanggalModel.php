<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class SettanggalModel extends Model
{
    use DataTableTrait;

    protected $table            = 'set_tanggal';
    protected $primaryKey       = 'id_tanggal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tanggal', 'keterangan', 'status'];

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

    // Validation rules
    protected $validationRules = [
        'tanggal'    => 'required|valid_date[Y-m-d H:i:s]',
        'keterangan' => 'required|max_length[255]',
        'status'     => 'required|in_list[Y,N]'
    ];

    protected $validationMessages = [
        'tanggal' => [
            'required'    => 'Tanggal wajib diisi.',
            'valid_date'  => 'Format tanggal tidak valid. Gunakan format yang sesuai.'
        ],
        'keterangan' => [
            'required'   => 'Keterangan wajib diisi.',
            'max_length' => 'Keterangan maksimal 255 karakter.'
        ],
        'status' => [
            'required' => 'Status wajib dipilih.',
            'in_list'  => 'Status hanya boleh Y atau N.'
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
    protected $dataTableSearchFields = ['set_tanggal.tanggal, set_tanggal.keterangan'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder;
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('set_tanggal.*');
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

    public function deleteSettanggal($id)
    {
        $query = $this->db->table('set_tanggal')->delete(array('id_tanggal' => $id));
        return $query;
    }
}
