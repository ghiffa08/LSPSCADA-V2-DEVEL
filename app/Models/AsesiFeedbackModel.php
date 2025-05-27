<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class AsesiFeedbackModel extends Model
{
    use DataTableTrait;

    protected $table            = 'asesi_feedback';
    protected $primaryKey       = 'id_feedback';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesi',
        'id_asesor',
        'id_asesmen',
        'tanggal_mulai',
        'tanggal_selesai',
        'pernyataan_1',
        'komentar_1',
        'pernyataan_2',
        'komentar_2',
        'pernyataan_3',
        'komentar_3',
        'pernyataan_4',
        'komentar_4',
        'pernyataan_5',
        'komentar_5',
        'pernyataan_6',
        'komentar_6',
        'pernyataan_7',
        'komentar_7',
        'pernyataan_8',
        'komentar_8',
        'pernyataan_9',
        'komentar_9',
        'pernyataan_10',
        'komentar_10',
        'komentar_tambahan',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_asesi'      => 'required',
        'id_asesor'     => 'required',
        'id_asesmen'    => 'required',
        'tanggal_mulai' => 'required|valid_date',
        'tanggal_selesai' => 'required|valid_date',
        'pernyataan_1'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_2'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_3'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_4'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_5'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_6'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_7'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_8'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_9'  => 'required|in_list[Ya,Tidak]',
        'pernyataan_10' => 'required|in_list[Ya,Tidak]',
    ];

    protected $validationMessages = [
        'id_asesi' => [
            'required' => 'ID Asesi harus diisi',
        ],
        'id_asesor' => [
            'required' => 'ID Asesor harus diisi',
        ],
        'id_asesmen' => [
            'required' => 'ID Asesmen harus diisi',
        ],
        'tanggal_mulai' => [
            'required' => 'Tanggal mulai asesmen harus diisi',
            'valid_date' => 'Format tanggal mulai tidak valid',
        ],
        'tanggal_selesai' => [
            'required' => 'Tanggal selesai asesmen harus diisi',
            'valid_date' => 'Format tanggal selesai tidak valid',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // DataTable search fields
    protected $dataTableSearchFields = [
        'asesi_user.fullname',
        'asesor_user.fullname',
        'skema.nama_skema',
        'asesi_feedback.created_at'
    ];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('asesi', 'asesi.id_asesi = asesi_feedback.id_asesi')
            ->join('users as asesi_user', 'asesi_user.id = asesi.user_id')
            ->join('users as asesor_user', 'asesor_user.id = asesi_feedback.id_asesor')
            ->join('asesmen', 'asesmen.id_asesmen = asesi_feedback.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'asesi_feedback.*, 
            asesi_user.fullname AS nama_asesi, 
            asesor_user.fullname AS nama_asesor,
            skema.nama_skema'
        );
    }

    /**
     * Check if a feedback already exists for a specific assessment
     *
     * @param string $id_asesi Assessee ID
     * @param int $id_asesmen Assessment ID
     * @return array|null The existing feedback or null if none exists
     */
    public function getFeedbackByAsesmenId(string $id_asesi, int $id_asesmen)
    {
        return $this->where('id_asesi', $id_asesi)
                    ->where('id_asesmen', $id_asesmen)
                    ->first();
    }

    /**
     * Get feedback details by ID with related information
     *
     * @param int $id_feedback Feedback ID
     * @return array|null
     */
    public function getFeedbackDetail($id_feedback)
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            asesi_feedback.*, 
            asesi_user.fullname AS nama_asesi, 
            asesor_user.fullname AS nama_asesor,
            skema.nama_skema,
            skema.kode_skema
        ');
        $builder->join('asesi', 'asesi.id_asesi = asesi_feedback.id_asesi');
        $builder->join('users as asesi_user', 'asesi_user.id = asesi.user_id');
        $builder->join('users as asesor_user', 'asesor_user.id = asesi_feedback.id_asesor');
        $builder->join('asesmen', 'asesmen.id_asesmen = asesi_feedback.id_asesmen');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema');
        $builder->where('asesi_feedback.id_feedback', $id_feedback);

        return $builder->get()->getRowArray();
    }
}
