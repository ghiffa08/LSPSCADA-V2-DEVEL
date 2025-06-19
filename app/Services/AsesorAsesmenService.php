<?php

namespace App\Services;

use App\Models\AsesmenModel;
use App\Models\SkemaModel;
use App\Models\UserMythModel;
use CodeIgniter\Database\ConnectionInterface;

class AsesorAsesmenService
{
    protected $asesmenModel;
    protected $skemaModel;
    protected $userModel;
    protected $db;
    protected $cache;

    public function __construct()
    {
        $this->asesmenModel = new AsesmenModel();
        $this->skemaModel = new SkemaModel();
        $this->userModel = new UserMythModel();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get all skema sertifikasi for a specific asesor
     */
    public function getSkemaForAsesor(int $asesorId, array $options = []): array
    {
        $cacheKey = "asesor_skema_{$asesorId}";

        // Try cache first
        if (!isset($options['no_cache'])) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            // Get asesmen where this asesor is assigned
            $builder = $this->db->table('asesmen a')
                ->select('s.id_skema, s.nama_skema, s.kode_skema, s.status_skema, 
                         a.id_asesmen, a.tujuan, a.created_at as asesmen_created,
                         COUNT(obs.id_observasi) as total_observasi,
                         COUNT(CASE WHEN obs.status = "completed" THEN 1 END) as completed_observasi')
                ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
                ->join('observasi obs', 'a.id_asesmen = obs.id_asesmen', 'left')
                ->where('aa.id_asesor', $asesorId)
                ->groupBy('s.id_skema, a.id_asesmen')
                ->orderBy('a.created_at', 'DESC');

            // Apply filters if provided
            if (isset($options['status']) && !empty($options['status'])) {
                $builder->where('s.status_skema', $options['status']);
            }

            if (isset($options['limit']) && $options['limit'] > 0) {
                $builder->limit($options['limit']);
            }

            $result = $builder->get()->getResultArray();

            // Group by skema to avoid duplicates
            $skemaList = [];
            foreach ($result as $row) {
                $skemaId = $row['id_skema'];

                if (!isset($skemaList[$skemaId])) {
                    $skemaList[$skemaId] = [
                        'id_skema' => $row['id_skema'],
                        'nama_skema' => $row['nama_skema'],
                        'kode_skema' => $row['kode_skema'],
                        'status_skema' => $row['status_skema'],
                        'total_asesmen' => 0,
                        'total_observasi' => 0,
                        'completed_observasi' => 0,
                        'asesmen_list' => []
                    ];
                }

                $skemaList[$skemaId]['total_asesmen']++;
                $skemaList[$skemaId]['total_observasi'] += $row['total_observasi'];
                $skemaList[$skemaId]['completed_observasi'] += $row['completed_observasi'];

                $skemaList[$skemaId]['asesmen_list'][] = [
                    'id_asesmen' => $row['id_asesmen'],
                    'tujuan' => $row['tujuan'],
                    'created_at' => $row['asesmen_created'],
                    'total_observasi' => $row['total_observasi'],
                    'completed_observasi' => $row['completed_observasi']
                ];
            }

            $result = array_values($skemaList);

            // Cache for 15 minutes
            $this->cache->save($cacheKey, $result, 900);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema for asesor: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed asesmen info for asesor
     */
    public function getAsesmenDetailForAsesor(int $asesorId, int $asesmenId): ?array
    {
        try {
            $builder = $this->db->table('asesmen a')
                ->select('a.*, s.nama_skema, s.kode_skema, s.status_skema,
                         t.nama_tuk, t.alamat_tuk,
                         tg.tanggal_mulai, tg.tanggal_selesai,
                         COUNT(obs.id_observasi) as total_observasi,
                         COUNT(CASE WHEN obs.status = "completed" THEN 1 END) as completed_observasi,
                         COUNT(CASE WHEN obs.status = "pending" THEN 1 END) as pending_observasi')
                ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
                ->join('tuk t', 'a.id_tuk = t.id_tuk', 'inner')
                ->join('tanggal_asesmen tg', 'a.id_tanggal = tg.id_tanggal', 'inner')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
                ->join('observasi obs', 'a.id_asesmen = obs.id_asesmen', 'left')
                ->where('aa.id_asesor', $asesorId)
                ->where('a.id_asesmen', $asesmenId)
                ->groupBy('a.id_asesmen');

            $result = $builder->get()->getRowArray();

            if (!$result) {
                return null;
            }

            // Get observasi list for this asesmen
            $result['observasi_list'] = $this->getObservasiForAsesmen($asesmenId);

            // Get asesi list for this asesmen
            $result['asesi_list'] = $this->getAsesiForAsesmen($asesmenId);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesmen detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get observasi list for asesmen
     */
    public function getObservasiForAsesmen(int $asesmenId): array
    {
        try {
            return $this->db->table('observasi obs')
                ->select('obs.*, u.nama_lengkap as asesor_nama')
                ->join('users u', 'obs.id_asesor = u.id', 'left')
                ->where('obs.id_asesmen', $asesmenId)
                ->orderBy('obs.created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error getting observasi for asesmen: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get asesi list for asesmen
     */
    public function getAsesiForAsesmen(int $asesmenId): array
    {
        try {
            return $this->db->table('asesmen_asesi aa')
                ->select('aa.*, u.nama_lengkap as asesi_nama, u.email as asesi_email')
                ->join('users u', 'aa.id_asesi = u.id', 'inner')
                ->where('aa.id_asesmen', $asesmenId)
                ->orderBy('u.nama_lengkap', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi for asesmen: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get asesor statistics
     */
    public function getAsesorStatistics(int $asesorId): array
    {
        $cacheKey = "asesor_stats_{$asesorId}";

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stats = [
                'total_skema' => 0,
                'total_asesmen' => 0,
                'total_observasi' => 0,
                'completed_observasi' => 0,
                'pending_observasi' => 0,
                'total_asesi' => 0
            ];

            // Get total skema
            $stats['total_skema'] = $this->db->table('asesmen a')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen')
                ->where('aa.id_asesor', $asesorId)
                ->selectCount('DISTINCT a.id_skema', 'total')
                ->get()
                ->getRow()
                ->total;

            // Get total asesmen
            $stats['total_asesmen'] = $this->db->table('asesor_asesmen')
                ->where('id_asesor', $asesorId)
                ->countAllResults();

            // Get observasi statistics
            $obsStats = $this->db->table('observasi obs')
                ->select('COUNT(*) as total, 
                         COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
                         COUNT(CASE WHEN status = "pending" THEN 1 END) as pending')
                ->join('asesmen a', 'obs.id_asesmen = a.id_asesmen')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen')
                ->where('aa.id_asesor', $asesorId)
                ->get()
                ->getRowArray();

            $stats['total_observasi'] = $obsStats['total'] ?? 0;
            $stats['completed_observasi'] = $obsStats['completed'] ?? 0;
            $stats['pending_observasi'] = $obsStats['pending'] ?? 0;

            // Get total asesi
            $stats['total_asesi'] = $this->db->table('asesmen_asesi asa')
                ->join('asesmen a', 'asa.id_asesmen = a.id_asesmen')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen')
                ->where('aa.id_asesor', $asesorId)
                ->selectCount('DISTINCT asa.id_asesi', 'total')
                ->get()
                ->getRow()
                ->total;

            // Cache for 5 minutes
            $this->cache->save($cacheKey, $stats, 300);

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesor statistics: ' . $e->getMessage());
            return $stats ?? [];
        }
    }

    /**
     * Check if asesor has access to specific asesmen
     */
    public function asesorHasAccessToAsesmen(int $asesorId, int $asesmenId): bool
    {
        try {
            $count = $this->db->table('asesor_asesmen')
                ->where('id_asesor', $asesorId)
                ->where('id_asesmen', $asesmenId)
                ->countAllResults();

            return $count > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error checking asesor access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear asesor-related caches
     */
    public function clearAsesorCache(int $asesorId): void
    {
        $this->cache->delete("asesor_skema_{$asesorId}");
        $this->cache->delete("asesor_stats_{$asesorId}");
    }

    /**
     * Create new asesmen assignment for asesor
     */
    public function assignAsesorToAsesmen(int $asesorId, int $asesmenId, array $options = []): bool
    {
        try {
            // Check if assignment already exists
            $exists = $this->db->table('asesor_asesmen')
                ->where('id_asesor', $asesorId)
                ->where('id_asesmen', $asesmenId)
                ->countAllResults();

            if ($exists > 0) {
                return false; // Already assigned
            }

            $data = [
                'id_asesor' => $asesorId,
                'id_asesmen' => $asesmenId,
                'assigned_at' => date('Y-m-d H:i:s'),
                'status' => $options['status'] ?? 'active'
            ];

            $inserted = $this->db->table('asesor_asesmen')->insert($data);

            if ($inserted) {
                $this->clearAsesorCache($asesorId);

                log_message('info', "Asesor {$asesorId} assigned to asesmen {$asesmenId}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error assigning asesor to asesmen: ' . $e->getMessage());
            return false;
        }
    }
}
