<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class SkemaModel extends Model
{
    use DataTableTrait;

    // Database table and primary key
    protected $DBGroup          = 'default';

    protected $table            = 'skema';
    protected $primaryKey       = 'id_skema';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_skema',
        'kode_skema',
        'nama_skema',
        'jenis_skema',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'kode_skema' => 'required|max_length[30]',
        'nama_skema' => 'required|max_length[255]',
        'jenis_skema' => 'required|in_list[KKNI,Okupasi,Klaster]',
        'status'     => 'required|in_list[Y,N]'
    ];

    protected $validationMessages = [
        'kode_skema' => [
            'required'   => 'Kode skema wajib diisi.',
            'max_length' => 'Kode skema maksimal 30 karakter.',
        ],
        'nama_skema' => [
            'required'   => 'Nama skema wajib diisi.',
            'max_length' => 'Nama skema maksimal 255 karakter.'
        ],
        'jenis_skema' => [
            'required' => 'Jenis skema wajib dipilih.',
            'in_list'  => 'Jenis skema harus salah satu dari: KKNI, Okupasi, Klaster.'
        ],
        'status' => [
            'required' => 'Status wajib dipilih.',
            'in_list'  => 'Status harus Y atau N.'
        ]
    ];

    // Fields that should be searched when using DataTable
    protected array $dataTableSearchFields = ['skema.kode_skema', 'skema.nama_skema'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder;
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('skema.*');
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

    // Callbacks
    protected $beforeInsert = ['cleanData'];
    protected $beforeUpdate = ['cleanData'];

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
     * Get active schemes (status = 'Y')
     */
    public function getActiveSchemes(): array
    {
        return $this->where('status', 'Y')
            ->orderBy('nama_skema', 'ASC')
            ->findAll();
    }

    /**
     * Get scheme data by ID (only active schemes)
     */
    public function getSchemeData(int $id_skema): ?array
    {
        return $this->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->first();
    }

    /**
     * Get work groups for a scheme
     */
    public function getWorkGroups(int $id_skema): array
    {
        return $this->db->table('kelompok_kerja')
            ->where('id_skema', $id_skema)
            ->orderBy('nama_kelompok', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get work groups with units for a scheme
     */
    public function getWorkGroupsWithUnits(int $id_skema): array
    {
        $builder = $this->db->table('kelompok_kerja as kk');
        $builder->select('
            kk.id_kelompok,
            kk.nama_kelompok,
            u.kode_unit,
            u.nama_unit as judul_unit
        ')
            ->join('kelompok_unit as ku', 'ku.id_kelompok = kk.id_kelompok')
            ->join('unit as u', 'u.id_unit = ku.id_unit')
            ->where('kk.id_skema', $id_skema)
            ->orderBy('kk.nama_kelompok', 'ASC')
            ->orderBy('u.kode_unit', 'ASC');

        $result = $builder->get()->getResultArray();

        $groupedData = [];
        foreach ($result as $row) {
            $groupName = $row['nama_kelompok'];

            if (!isset($groupedData[$groupName])) {
                $groupedData[$groupName] = [];
            }

            $groupedData[$groupName][] = [
                'nama_kelompok' => $groupName,
                'kode_unit'     => $row['kode_unit'],
                'judul_unit'    => $row['judul_unit']
            ];
        }

        return $groupedData;
    }

    /**
     * Delete a scheme and its related data (with transaction)
     */
    public function deleteScheme(int $id_skema): bool
    {
        $this->db->transStart();

        try {
            // Delete related data first
            $this->db->table('kelompok_unit')
                ->whereIn('id_kelompok', function ($builder) use ($id_skema) {
                    $builder->select('id_kelompok')
                        ->from('kelompok_kerja')
                        ->where('id_skema', $id_skema);
                })
                ->delete();

            $this->db->table('kelompok_kerja')
                ->where('id_skema', $id_skema)
                ->delete();

            // Delete the scheme
            $this->where('id_skema', $id_skema)->delete();

            $this->db->transComplete();
            return $this->db->transStatus();
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Failed to delete scheme: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if scheme exists and is active
     */
    public function isValidScheme(int $id_skema): bool
    {
        return $this->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->countAllResults() > 0;
    }
}
