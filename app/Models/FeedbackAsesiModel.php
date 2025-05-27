<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class FeedbackAsesiModel extends Model
{
    use DataTableTrait;

    protected $table            = 'feedback_asesi';
    protected $primaryKey       = 'id_feedback';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [
        'id_asesor',
        'id_asesi',
        'id_skema',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan_lain',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
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
    protected $dataTableSearchFields = ['feedback_asesi.id_asesor'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('asesi', 'asesi.id_asesi = feedback_asesi.id_asesi')
            ->join('users as asesi_user', 'asesi_user.id = asesi.user_id')
            ->join('skema', 'skema.id_skema = feedback_asesi.id_skema')
            ->join('users as asesor', 'asesor.id = feedback_asesi.id_asesor');
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
            'feedback_asesi.*, 
            asesor.fullname AS nama_asesor, 
            asesi_user.fullname AS nama_asesi, 
            skema.nama_skema'
        );
    }

    /**
     * Get feedback data by ID with full details
     *
     * @param int $id_feedback ID of the feedback
     * @return array|null Feedback data or null if not found
     */
    public function getById(int $id): ?array
    {
        $builder = $this->db->table('feedback_asesi');

        $builder->select([
            'feedback_asesi.*',
            'asesor.fullname AS nama_asesor',
            'asesor.tanda_tangan AS ttd_asesor',
            'asesi_user.fullname AS nama_asesi',
            'asesi_user.tanda_tangan AS ttd_asesi',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);

        // Join tables
        $builder->join('asesi', 'asesi.id_asesi = feedback_asesi.id_asesi');
        $builder->join('users as asesi_user', 'asesi_user.id = asesi.user_id');
        $builder->join('skema', 'skema.id_skema = feedback_asesi.id_skema');
        $builder->join('users as asesor', 'asesor.id = feedback_asesi.id_asesor');

        // Filter by feedback ID
        $builder->where('feedback_asesi.id_feedback', $id);

        $result = $builder->get()->getRowArray();

        // Convert binary signatures to base64
        if ($result) {
            if (!empty($result['ttd_asesi'])) {
                $result['ttd_asesi_base64'] = 'data:image/png;base64,' . base64_encode($result['ttd_asesi']);
            }
            if (!empty($result['ttd_asesor'])) {
                $result['ttd_asesor_base64'] = 'data:image/png;base64,' . base64_encode($result['ttd_asesor']);
            }
        }

        return $result;
    }

    /**
     * Get feedback details by feedback ID
     *
     * @param int $id_feedback ID of the feedback
     * @return array Feedback details
     */
    public function getFeedbackDetails(int $id_feedback): array
    {
        $builder = $this->db->table('detail_feedback_asesi');
        $builder->select('detail_feedback_asesi.*, komponen_feedback.pernyataan');
        $builder->join('komponen_feedback', 'komponen_feedback.id_komponen = detail_feedback_asesi.id_komponen', 'left');
        $builder->where('detail_feedback_asesi.id_feedback', $id_feedback);
        $builder->orderBy('komponen_feedback.urutan', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get feedback details by feedback ID with komponen information
     *
     * @param int $id_feedback ID of the feedback
     * @return array Feedback details with komponen information
     */
    public function getFeedbackDetailsByIdWithKomponen(int $id_feedback): array
    {
        $builder = $this->db->table('detail_feedback_asesi');
        $builder->select('
            detail_feedback_asesi.*,
            komponen_feedback.pernyataan,
            komponen_feedback.urutan
        ');
        $builder->join('komponen_feedback', 'komponen_feedback.id_komponen = detail_feedback_asesi.id_komponen', 'left');
        $builder->where('detail_feedback_asesi.id_feedback', $id_feedback);
        $builder->orderBy('komponen_feedback.urutan', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Save feedback data with details
     *
     * @param array $masterData Master feedback data
     * @param array|null $detailData Detail feedback data
     * @return bool|array Returns inserted ID on success or boolean success status
     */
    public function saveFeedbackData(array $masterData, ?array $detailData = null)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Get or create the master feedback record
            $id_feedback = $masterData['id_feedback'] ?? null;

            if (!$id_feedback) {
                // Check if there's an existing record
                $existing = $db->table($this->table)
                    ->where('id_asesi', $masterData['id_asesi'])
                    ->where('id_skema', $masterData['id_skema'])
                    ->get()
                    ->getRow();

                if ($existing) {
                    $id_feedback = $existing->id_feedback;
                    $masterData['updated_at'] = date('Y-m-d H:i:s');
                    $db->table($this->table)
                        ->where('id_feedback', $id_feedback)
                        ->update($masterData);
                } else {
                    $masterData['created_at'] = date('Y-m-d H:i:s');
                    $masterData['updated_at'] = date('Y-m-d H:i:s');
                    $db->table($this->table)->insert($masterData);
                    $id_feedback = $db->insertID();
                }
            } else {
                $masterData['updated_at'] = date('Y-m-d H:i:s');
                $db->table($this->table)
                    ->where('id_feedback', $id_feedback)
                    ->update($masterData);
            }

            // Process detail data if provided
            if ($detailData && isset($detailData['komponen'])) {
                $this->saveDetailData($id_feedback, $detailData['komponen'], $detailData['komentar'] ?? []);
            }

            $db->transComplete();

            return [
                'id_feedback' => $id_feedback,
                'success' => true
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in saveFeedbackData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save detail feedback data
     *
     * @param int $id_feedback ID of the feedback
     * @param array $komponen Component data (id_komponen => jawaban)
     * @param array $komentar Comment data (id_komponen => komentar)
     * @return bool Success status
     */
    private function saveDetailData(int $id_feedback, array $komponen, array $komentar): bool
    {
        $table = 'detail_feedback_asesi';
        $db = $this->db;

        // Delete existing details for this feedback
        $db->table($table)
            ->where('id_feedback', $id_feedback)
            ->delete();

        // Prepare batch data for insertion
        $batch_data = [];
        foreach ($komponen as $id_komponen => $jawaban) {
            $batch_data[] = [
                'id_feedback' => $id_feedback,
                'id_komponen' => $id_komponen,
                'jawaban' => $jawaban,
                'komentar' => $komentar[$id_komponen] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($batch_data)) {
            $db->table($table)->insertBatch($batch_data);
        }

        return true;
    }
}
