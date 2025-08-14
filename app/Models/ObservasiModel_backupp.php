<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Observasi Model - Production Ready
 * 
 * Clean model with optimized queries and caching
 * 
 * @package App\Models
 */
class ObservasiModel extends Model
{
    protected $table = 'observasi';
    protected $primaryKey = 'id_observasi';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'id_asesor',
        'id_asesmen',
        'id_asesi',
        'id_pengajuan',
        'tanggal_observasi',
        'status',
        'catatan',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'id_asesor' => 'required|is_natural_no_zero',
        'id_asesmen' => 'required|is_natural_no_zero',
        'tanggal_observasi' => 'required|valid_date',
        'status' => 'required|in_list[draft,in_progress,completed,cancelled]'
    ];

    protected $validationMessages = [
        'id_asesor' => [
            'required' => 'ID Asesor harus diisi',
            'is_natural_no_zero' => 'ID Asesor tidak valid'
        ],
        'id_asesmen' => [
            'required' => 'ID Asesmen harus diisi', 
            'is_natural_no_zero' => 'ID Asesmen tidak valid'
        ],
        'tanggal_observasi' => [
            'required' => 'Tanggal observasi harus diisi',
            'valid_date' => 'Format tanggal tidak valid'
        ],
        'status' => [
            'required' => 'Status harus diisi',
            'in_list' => 'Status tidak valid'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaultValues'];
    protected $beforeUpdate = ['preventStatusDowngrade'];

    /**
     * Set default values before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaultValues(array $data): array
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'draft';
        }
        
        return $data;
    }

    /**
     * Prevent status downgrade (business rule)
     * 
     * @param array $data
     * @return array
     */
    protected function preventStatusDowngrade(array $data): array
    {
        if (isset($data['id']) && isset($data['data']['status'])) {
            $current = $this->find($data['id']);
            
            if ($current) {
                $statusHierarchy = ['draft', 'in_progress', 'completed'];
                $currentIndex = array_search($current['status'], $statusHierarchy);
                $newIndex = array_search($data['data']['status'], $statusHierarchy);
                
                // Prevent downgrade (except to cancelled)
                if ($newIndex < $currentIndex && $data['data']['status'] !== 'cancelled') {
                    unset($data['data']['status']);
                }
            }
        }
        
        return $data;
    }

    /**
     * Get observasi with complete details
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->select('observasi.*, 
                            asesmen.tujuan as asesmen_tujuan,
                            skema.nama_skema, skema.kode_skema,
                            asesor.nomor_registrasi,
                            users.username as asesor_username')
                    ->join('asesmen', 'observasi.id_asesmen = asesmen.id_asesmen')
                    ->join('skema', 'asesmen.id_skema = skema.id_skema')
                    ->join('asesor', 'observasi.id_asesor = asesor.id_asesor')
                    ->join('users', 'asesor.id_user = users.id')
                    ->where('observasi.id_observasi', $id)
                    ->first();
    }

    /**
     * Get observasi by asesor with pagination
     * 
     * @param int $asesorId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByAsesorPaginated(int $asesorId, array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $builder = $this->select('observasi.*, asesmen.tujuan as asesmen_tujuan, skema.nama_skema')
                        ->join('asesmen', 'observasi.id_asesmen = asesmen.id_asesmen')
                        ->join('skema', 'asesmen.id_skema = skema.id_skema')
                        ->where('observasi.id_asesor', $asesorId);

        // Apply filters
        if (!empty($filters['tanggal_mulai'])) {
            $builder->where('observasi.tanggal_observasi >=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $builder->where('observasi.tanggal_observasi <=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['status'])) {
            $builder->where('observasi.status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('asesmen.tujuan', $filters['search'])
                    ->orLike('skema.nama_skema', $filters['search'])
                    ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $data = $builder->orderBy('observasi.created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->get()
                       ->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Get observasi statistics for asesor
     * 
     * @param int $asesorId
     * @return array
     */
    public function getStatistics(int $asesorId): array
    {
        $stats = $this->select("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        ")->where('id_asesor', $asesorId)
          ->first();

        return $stats ?: [
            'total' => 0,
            'draft' => 0, 
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
    }

    /**
     * Update observasi status
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Check if observasi belongs to asesor
     * 
     * @param int $observasiId
     * @param int $asesorId
     * @return bool
     */
    public function belongsToAsesor(int $observasiId, int $asesorId): bool
    {
        return $this->where('id_observasi', $observasiId)
                    ->where('id_asesor', $asesorId)
                    ->countAllResults() > 0;
    }

    /**
     * Get recent observasi for dashboard
     * 
     * @param int $asesorId
     * @param int $limit
     * @return array
     */
    public function getRecentForDashboard(int $asesorId, int $limit = 5): array
    {
        return $this->select('observasi.*, asesmen.tujuan, skema.nama_skema')
                    ->join('asesmen', 'observasi.id_asesmen = asesmen.id_asesmen')
                    ->join('skema', 'asesmen.id_skema = skema.id_skema')
                    ->where('observasi.id_asesor', $asesorId)
                    ->orderBy('observasi.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
