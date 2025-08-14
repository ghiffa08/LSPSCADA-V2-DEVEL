<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{


    // Total asesi (jika ingin hitung semua pengajuan)
    public function total_asesi()
    {
        return $this->db->table('pengajuan_asesmen')->countAll();
    }
    public function total_admin()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'Admin')
            ->countAllResults();
    }

    public function total_asesor()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups.name', 'Asesor')
            ->countAllResults();
    }

    // Assessor-specific statistics methods

    public function getAsesorTotalAPL1Pending()
    {
        return $this->db->table('apl1')
            ->where('validasi_apl1', 'pending')
            ->countAllResults();
    }

    // Contoh: get pending APL2 (jika ada relasi ke pengajuan_asesmen)
    public function getAsesorTotalAPL2Pending($assessorId)
    {
        return $this->db->table('apl2')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_pengajuan = apl2.id_pengajuan')
            ->where('apl2.validasi_apl2', 'pending')
            ->countAllResults();
    }

    public function getAsesorTotalAPL2Validated($assessorId)
    {
        return $this->db->table('apl2')
            ->where('validasi_apl2', 'validated')
            ->where('validator', $assessorId)
            ->countAllResults();
    }

    public function getAsesorTotalObservasi($assessorId)
    {
        return $this->db->table('observasi')
            ->where('id_asesor', $assessorId)
            ->countAllResults();
    }

    // Contoh: get recent activities (ubah join ke pengajuan_asesmen)
    public function getAsesorRecentActivities($assessorId, $limit = 5)
    {
        $activities = [];

        // Recent APL2 validations
        $apl2Activities = $this->db->table('apl2')
            ->select('apl2.*, users.nama_lengkap as nama_asesi, skema.nama_skema, apl2.updated_at as activity_time')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_pengajuan = apl2.id_pengajuan')
            ->join('users', 'users.id = pengajuan_asesmen.id_asesi')
            ->join('skema', 'skema.id_skema = pengajuan_asesmen.id_skema')
            ->where('apl2.validator', $assessorId)
            ->where('apl2.validasi_apl2 !=', 'pending')
            ->orderBy('apl2.updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($apl2Activities as $activity) {
            $activities[] = [
                'type' => 'apl2_validation',
                'title' => 'Validasi FR.APL.02',
                'description' => "Validasi asesmen mandiri {$activity['nama_asesi']} - {$activity['nama_skema']}",
                'status' => $activity['validasi_apl2'],
                'time' => $activity['activity_time'],
                'icon' => 'fas fa-clipboard-check',
                'color' => $activity['validasi_apl2'] == 'validated' ? 'success' : 'danger'
            ];
        }

        // Recent observations
        $observasiActivities = $this->db->table('observasi')
            ->select('observasi.*, users.nama_lengkap as nama_asesi, skema.nama_skema, observasi.created_at as activity_time')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_pengajuan = observasi.id_pengajuan')
            ->join('users', 'users.id = pengajuan_asesmen.id_asesi')
            ->join('skema', 'skema.id_skema = pengajuan_asesmen.id_skema')
            ->where('observasi.id_asesor', $assessorId)
            ->orderBy('observasi.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($observasiActivities as $activity) {
            $activities[] = [
                'type' => 'observasi',
                'title' => 'Ceklist Observasi',
                'description' => "Observasi {$activity['nama_asesi']} - {$activity['nama_skema']}",
                'status' => 'completed',
                'time' => $activity['activity_time'],
                'icon' => 'fas fa-eye',
                'color' => 'info'
            ];
        }

        // Sort by time and limit
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, $limit);
    }

    public function getAsesorMonthlyStats($assessorId)
    {
        $currentYear = date('Y');
        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);

            $apl2Count = $this->db->table('apl2')
                ->where('validator', $assessorId)
                ->where('validasi_apl2', 'validated')
                ->where("DATE_FORMAT(updated_at, '%Y-%m')", "$currentYear-$month")
                ->countAllResults();

            $observasiCount = $this->db->table('observasi')
                ->where('id_asesor', $assessorId)
                ->where("DATE_FORMAT(created_at, '%Y-%m')", "$currentYear-$month")
                ->countAllResults();

            $months[] = [
                'month' => $i,
                'month_name' => date('M', mktime(0, 0, 0, $i, 1)),
                'apl2_validated' => $apl2Count,
                'observasi_completed' => $observasiCount,
                'total_activities' => $apl2Count + $observasiCount
            ];
        }

        return $months;
    }

    public function getAsesorUpcomingAssessments($assessorId, $limit = 5)
    {
        return $this->db->table('asesmen')
            ->select('asesmen.*, skema.nama_skema, asesmen.tanggal, asesmen.waktu')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->where('asesmen.tanggal >=', date('Y-m-d'))
            ->orderBy('asesmen.tanggal', 'ASC')
            ->orderBy('asesmen.waktu', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
