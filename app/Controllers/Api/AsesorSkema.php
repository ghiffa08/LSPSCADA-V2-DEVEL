<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AsesorAsesmenService;
use App\Models\SkemaModel;
use App\Models\AsesmenModel;

class AsesorSkema extends ResourceController
{
    protected AsesorAsesmenService $asesorAsesmenService;
    protected SkemaModel $skemaModel;
    protected AsesmenModel $asesmenModel;
    protected int $asesorId;
    protected $db;

    public function __construct()
    {
        helper('auth');

        $this->asesorAsesmenService = new AsesorAsesmenService();
        $this->skemaModel = new SkemaModel();
        $this->asesmenModel = new AsesmenModel();
        $this->db = \Config\Database::connect();

        // Security check
        if (!in_groups(['Asesor', 'Admin'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        $this->asesorId = user()->id;
    }

    /**
     * Get skema details by ID
     */
    public function detail($skemaId = null)
    {
        try {
            if (!$skemaId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Skema ID required'
                ]);
            }

            $skema = $this->skemaModel->find($skemaId);

            if (!$skema) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Skema tidak ditemukan'
                ]);
            }

            // Get related asesmen for this asesor and skema
            $asesmenList = $this->db->table('asesmen a')
                ->select('a.*, t.nama_tuk, tg.tanggal_mulai, tg.tanggal_selesai')
                ->join('tuk t', 'a.id_tuk = t.id_tuk', 'inner')
                ->join('tanggal_asesmen tg', 'a.id_tanggal = tg.id_tanggal', 'inner')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
                ->where('a.id_skema', $skemaId)
                ->where('aa.id_asesor', $this->asesorId)
                ->orderBy('tg.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

            $html = view('asesor/observasi/skema_detail_modal', [
                'skema' => $skema,
                'asesmenList' => $asesmenList
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'html' => $html
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting skema detail: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Get asesmen list by skema
     */
    public function asesmenBySkema($skemaId = null)
    {
        try {
            if (!$skemaId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Skema ID required'
                ]);
            }

            // Get asesmen list for this skema and asesor
            $asesmenList = $this->db->table('asesmen a')
                ->select('a.*, s.nama_skema, s.kode_skema,
                         t.nama_tuk, t.alamat_tuk,
                         tg.tanggal_mulai, tg.tanggal_selesai,
                         COUNT(obs.id_observasi) as total_observasi,
                         COUNT(CASE WHEN obs.status = "completed" THEN 1 END) as completed_observasi')
                ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
                ->join('tuk t', 'a.id_tuk = t.id_tuk', 'inner')
                ->join('tanggal_asesmen tg', 'a.id_tanggal = tg.id_tanggal', 'inner')
                ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
                ->join('observasi obs', 'a.id_asesmen = obs.id_asesmen', 'left')
                ->where('a.id_skema', $skemaId)
                ->where('aa.id_asesor', $this->asesorId)
                ->groupBy('a.id_asesmen')
                ->orderBy('tg.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

            $html = view('asesor/observasi/asesmen_list_modal', [
                'asesmenList' => $asesmenList,
                'skemaId' => $skemaId
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'html' => $html
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesmen by skema: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Get recent activities for asesor
     */
    public function recentActivities()
    {
        try {
            // Get recent activities
            $activities = $this->db->table('observasi obs')
                ->select('obs.*, a.tujuan, s.nama_skema, s.kode_skema,
                         u.nama_lengkap as asesi_nama')
                ->join('asesmen a', 'obs.id_asesmen = a.id_asesmen', 'inner')
                ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
                ->join('asesmen_asesi aa', 'a.id_asesmen = aa.id_asesmen', 'left')
                ->join('users u', 'aa.id_asesi = u.id', 'left')
                ->where('obs.id_asesor', $this->asesorId)
                ->orderBy('obs.updated_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            $html = view('asesor/observasi/recent_activities', [
                'activities' => $activities
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'html' => $html
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting recent activities: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Get statistics for asesor dashboard
     */
    public function statistics()
    {
        try {
            $stats = $this->asesorAsesmenService->getAsesorStatistics($this->asesorId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesor statistics: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }
}
