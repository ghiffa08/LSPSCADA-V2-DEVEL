<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class TUKModel extends Model
{

    use DataTableTrait;

    protected $table            = 'tuk';
    protected $primaryKey       = 'id_tuk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_tuk', 'jenis_tuk'];

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
        'nama_tuk' => 'required|max_length[255]',
        'jenis_tuk' => 'required|in_list[Sewaktu, Tempat Kerja, Mandiri]',
    ];

    protected $validationMessages = [
        'nama_tuk' => [
            'required'   => 'Nama tuk wajib diisi.',
            'max_length' => 'Nama tuk maksimal 255 karakter.'
        ],
        'jenis_tuk' => [
            'required' => 'Jenis tuk wajib dipilih.',
            'in_list'  => 'Jenis tuk harus salah satu dari: KKNI, Okupasi, Klaster.'
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
    protected $dataTableSearchFields = ['tuk.nama_tuk', 'tuk.jenis_tuk'];

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
        return $builder->select('tuk.*');
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

    public function deleteTUK($id)
    {
        $query = $this->db->table('tuk')->delete(array('id_tuk' => $id));
        return $query;
    }
}
