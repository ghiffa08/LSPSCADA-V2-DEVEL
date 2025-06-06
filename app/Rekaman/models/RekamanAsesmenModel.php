<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class RekamanAsesmenModel extends Model
{
    use DataTableTrait;

    protected $table            = 'rekaman_asesmen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_apl1',
        'rekomendasi',
        'tindak_lanjut',
        'komentar',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_apl1'       => 'required',
        'rekomendasi'   => 'required|in_list[kompeten,belum_kompeten]',
        'tindak_lanjut' => 'permit_empty',
        'komentar'      => 'permit_empty'
    ];

    protected $validationMessages = [
        'id_apl1' => [
            'required' => 'ID APL1 wajib diisi.',
            'integer'  => 'ID APL1 harus berupa angka.'
        ],
        'rekomendasi' => [
            'required' => 'Rekomendasi wajib diisi.',
            'in_list'  => 'Rekomendasi harus berupa: kompeten atau belum_kompeten.'
        ]
    ];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['rekaman_asesmen.komentar', 'rekaman_asesmen.tindak_lanjut'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins(object $builder): object
    {
        return $builder->join('pengajuan_asesmen', 'pengajuan_asesmen.id_apl1 = rekaman_asesmen.id_apl1', 'left');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('rekaman_asesmen.*, pengajuan_asesmen.id_asesi');
    }

    /**
     * Get rekaman asesmen by APL1 ID
     *
     * @param int $id_apl1
     * @return array|null
     */
    public function getByApl1Id(int $id_apl1): ?array
    {
        return $this->where('id_apl1', $id_apl1)
            ->first();
    }

    /**
     * Get rekaman asesmen with Asesi data
     *
     * @param int $id
     * @return array|null
     */
    public function getWithAsesiData(int $id): ?array
    {
        return $this->select('
            rekaman_asesmen.*, 
            pengajuan_asesmen.id_asesi, 
            asesi.user_id, 
            asesi.nik, 
            asesi.nama AS nama_asesi, 
            skema.nama_skema, 
            skema.jenis_skema
        ')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_apl1 = rekaman_asesmen.id_apl1', 'left')
            ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->where('rekaman_asesmen.id', $id)
            ->first();
    }

    /**
     * Get rekaman asesmen listing with related data
     *
     * @return array
     */
    public function getRekamanAsesmenList(): array
    {
        return $this->select('
            rekaman_asesmen.*, 
            pengajuan_asesmen.id_asesi, 
            asesi.nama AS nama_asesi,
            skema.nama_skema
        ')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_apl1 = rekaman_asesmen.id_apl1', 'left')
            ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->findAll();
    }
}
