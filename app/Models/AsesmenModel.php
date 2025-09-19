<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class AsesmenModel extends Model
{
    use DataTableTrait;

    protected $table            = 'asesmen';
    protected $primaryKey       = 'id_asesmen';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_skema', 'id_tuk', 'id_tanggal', 'tujuan'];

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
        'id_skema'       => 'required|integer',
        'id_tuk'         => 'required|integer',
        'id_tanggal'     => 'required|integer',
        'tujuan'       => 'required|max_length[255]'
    ];

    protected $validationMessages = [
        'id_skema' => [
            'required' => 'ID Skema wajib diisi.',
            'integer'  => 'ID Skema harus berupa angka.'
        ],
        'id_tuk' => [
            'required' => 'ID Skema wajib diisi.',
            'integer'  => 'ID Skema harus berupa angka.'
        ],
        'id_tanggal' => [
            'required' => 'ID Skema wajib diisi.',
            'integer'  => 'ID Skema harus berupa angka.'
        ],
        'tujuan' => [
            'required'   => 'Nama KUK wajib diisi.',
            'max_length' => 'Nama KUK tidak boleh lebih dari 255 karakter.'
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
    protected $dataTableSearchFields = ['skema.nama_skema', 'tuk_nama_tuk', 'set_tanggal.tanggal_asesmen'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('asesmen.*, skema.nama_skema, tuk.nama_tuk, DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal_asesmen');
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

    public function getAllAsesmen()
    {
        return $this->db->table('asesmen')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('asesmen.id_asesmen, asesmen.id_skema, asesmen.id_tuk, asesmen.id_tanggal, asesmen.tujuan, skema.nama_skema, skema.kode_skema, skema.jenis_skema, tuk.nama_tuk, tuk.jenis_tuk, DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal')
            ->get()->getResultArray();
    }

    /**
     * Get asesmen by ID with complete related data
     *
     * @param int $id
     * @param bool $useCache Whether to use internal request cache
     * @return array|null
     */
    public function getAsesmenById(int $id, bool $useCache = false)
    {
        // Check cache first if enabled
        $cacheKey = "asesmen_{$id}";
        if ($useCache && isset($this->tempCache[$cacheKey])) {
            return $this->tempCache[$cacheKey];
        }

        try {
            $result = $this->db->table('asesmen')
                ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
                ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
                ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
                ->select('
                asesmen.id_asesmen, 
                asesmen.id_skema, 
                asesmen.id_tuk, 
                asesmen.id_tanggal, 
                asesmen.tujuan, 
                skema.nama_skema, 
                skema.kode_skema, 
                skema.jenis_skema, 
                tuk.nama_tuk, 
                tuk.jenis_tuk, 
                DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal,
                set_tanggal.tanggal AS tanggal_raw
            ')
                ->where('asesmen.id_asesmen', $id)
                ->get()
                ->getRowArray();

            // Store in cache if enabled and result found
            if ($useCache && $result) {
                $this->tempCache[$cacheKey] = $result;
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error in getAsesmenById: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get filtered and paginated asesmen data with total count.
     *
     * @param string $searchTerm
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function getFilteredPaginatedAsesmen(string $searchTerm = '', int $limit = 10, int $offset = 0)
    {
        $builder = $this->db->table('asesmen')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('
                asesmen.id_asesmen, 
                skema.nama_skema, 
                skema.jenis_skema, 
                tuk.nama_tuk, 
                tuk.jenis_tuk, 
                set_tanggal.tanggal
            ');

        // Apply search filter if a search term is provided
        if (!empty($searchTerm)) {
            $builder->groupStart();
            $builder->like('skema.nama_skema', $searchTerm);
            $builder->orLike('skema.jenis_skema', $searchTerm);
            $builder->orLike('tuk.nama_tuk', $searchTerm);
            $builder->groupEnd();
        }

        // To get the total count of filtered rows, we clone the builder *before* applying limit and offset
        $totalRows = $builder->countAllResults(false); // `false` prevents the query from resetting

        // Now apply limit and offset for pagination
        $builder->limit($limit, $offset);

        // Get the paginated results
        $data = $builder->get()->getResultArray();

        // Return both data and total count
        return [
            'data'      => $data,
            'totalRows' => $totalRows
        ];
    }

    public function getJadwal($id_skema)
    {
        return $this->db->table('asesmen')
            ->where('id_skema', $id_skema)
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('asesmen.id_asesmen, asesmen.id_tanggal, DATE_FORMAT(set_tanggal.tanggal, "%d/%m/%Y") AS tanggal, set_tanggal.keterangan, asesmen.id_tuk, tuk.nama_tuk')
            ->get()->getResultArray();
    }
    public function getTuk($id_skema)
    {
        return $this->db->table('asesmen')
            ->where('id_skema', $id_skema)
            ->join('tuk', 'tuk.id_tuk=asesmen.id_tuk', 'left')
            ->select('asesmen.id_tuk, tuk.nama_tuk')
            ->get()->getResultArray();
    }
}
