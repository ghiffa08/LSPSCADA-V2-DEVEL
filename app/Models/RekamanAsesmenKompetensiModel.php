<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class RekamanAsesmenKompetensiModel extends Model
{
    use DataTableTrait;

    protected $table            = 'rekaman_asesmen_kompetensi';
    protected $primaryKey       = 'id_rekaman_asesmen_kompetensi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'id_rekaman_asesmen',
        'id_unit',
        'observasi',
        'portofolio',
        'pihak_ketiga',
        'lisan',
        'tertulis',
        'proyek_kerja',
        'lainnya',
        'keterangan',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'id_rekaman_asesmen'         => 'required|integer',
        'id_unit'            => 'required|integer',
        'observasi'   => 'required|integer|in_list[0,1]',
        'portofolio'  => 'required|integer|in_list[0,1]',
        'pihak_ketiga' => 'required|integer|in_list[0,1]',
        'lisan'       => 'required|integer|in_list[0,1]',
        'tertulis'    => 'required|integer|in_list[0,1]',
        'proyek_kerja'      => 'required|integer|in_list[0,1]',
        'lainnya'     => 'required|integer|in_list[0,1]'
    ];

    protected $validationMessages = [
        'id_rekaman_asesmen' => [
            'required' => 'ID Rekaman wajib diisi.',
            'integer'  => 'ID Rekaman harus berupa angka.'
        ],
        'id_unit' => [
            'required' => 'ID Unit wajib diisi.',
            'integer'  => 'ID Unit harus berupa angka.'
        ]
    ];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = [];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder->join('rekaman_asesmen', 'rekaman_asesmen.id = rekaman_asesmen_kompetensi.id_rekaman', 'left')
            ->join('unit_kompetensi', 'unit_kompetensi.id = rekaman_asesmen_kompetensi.id_unit', 'left');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('rekaman_asesmen_kompetensi.*, unit_kompetensi.kode_unit, unit_kompetensi.nama_unit');
    }

    /**
     * Get kompetensi by rekaman ID
     *
     * @param int $id_rekaman
     * @return array
     */
    public function getByRekamanId(int $id_rekaman): array
    {
        return $this->select('
            rekaman_asesmen_kompetensi.*, 
            unit_kompetensi.kode_unit, 
            unit_kompetensi.nama_unit
        ')
            ->join('unit_kompetensi', 'unit_kompetensi.id = rekaman_asesmen_kompetensi.id_unit', 'left')
            ->where('rekaman_asesmen_kompetensi.id_rekaman', $id_rekaman)
            ->findAll();
    }

    /**
     * Insert batch of kompetensi records for a rekaman
     *
     * @param int $id_rekaman
     * @param array $unit_ids
     * @param array $metode_data Associative array of metode selections
     * @return bool
     */
    public function saveBatchKompetensi(int $id_rekaman, array $unit_ids, array $metode_data = []): bool
    {
        $batch_data = [];

        foreach ($unit_ids as $id_unit) {
            $item = [
                'id_rekaman_asesmen' => $id_rekaman,
                'id_unit' => $id_unit,
                'observasi' => isset($metode_data[$id_unit]['observasi']) ? 1 : 0,
                'portofolio' => isset($metode_data[$id_unit]['portofolio']) ? 1 : 0,
                'pihak_ketiga' => isset($metode_data[$id_unit]['pihak_ketiga']) ? 1 : 0,
                'lisan' => isset($metode_data[$id_unit]['lisan']) ? 1 : 0,
                'tertulis' => isset($metode_data[$id_unit]['tertulis']) ? 1 : 0,
                'proyek_kerja' => isset($metode_data[$id_unit]['proyek']) ? 1 : 0,
                'lainnya' => isset($metode_data[$id_unit]['lainnya']) ? 1 : 0,
            ];

            $batch_data[] = $item;
        }

        if (!empty($batch_data)) {
            return $this->insertBatch($batch_data);
        }

        return true;
    }

    /**
     * Delete all kompetensi records for a rekaman
     *
     * @param int $id_rekaman
     * @return bool
     */
    public function deleteByRekamanId(int $id_rekaman): bool
    {
        return $this->where('id_rekaman', $id_rekaman)->delete();
    }
}
