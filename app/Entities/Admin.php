<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Admin extends Entity
{
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id_admin' => 'string',
        'user_id' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Get the User entity associated with this Admin
     *
     * @return User|null
     */
    public function getUser()
    {
        $userModel = model('UserModel');
        $user = $userModel->find($this->attributes['user_id']);

        if ($user) {
            return new User($user);
        }

        return null;
    }

    /**
     * Get application statistics
     *
     * @return array
     */
    public function getApplicationStats()
    {
        $db = \Config\Database::connect();

        $stats = [
            'total_asesi' => $db->table('asesi')->countAllResults(),
            'total_asesor' => $db->table('asesor')->where('is_active', 1)->countAllResults(),
            'total_skema' => $db->table('skema')->countAllResults(),
            'total_active_assessment' => $db->table('pengajuan_asesmen')->where('status', 'on_progress')->countAllResults(),
            'pending_applications' => $db->table('pengajuan_asesmen')->where('status', 'pending')->countAllResults(),
            'completed_assessments' => $db->table('pengajuan_asesmen')->where('status', 'completed')->countAllResults(),
        ];

        return $stats;
    }

    /**
     * Get latest application submissions
     *
     * @param int $limit
     * @return array
     */
    public function getLatestApplications(int $limit = 5): array
    {
        $db = \Config\Database::connect();

        return $db->table('pengajuan_asesmen pa')
            ->select('pa.*, a.nama as nama_asesi, s.nama_skema, u.fullname as nama_asesor')
            ->join('asesi a', 'a.id_asesi = pa.id_asesi')
            ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->join('users u', 'u.id = asm.id_asesor', 'left')
            ->orderBy('pa.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get urgent tasks that need admin attention
     *
     * @return array
     */
    public function getUrgentTasks(): array
    {
        $db = \Config\Database::connect();

        $tasks = [];

        // Pending applications older than 3 days
        $oldPending = $db->table('pengajuan_asesmen pa')
            ->select('pa.*, a.nama as nama_asesi, s.nama_skema')
            ->join('asesi a', 'a.id_asesi = pa.id_asesi')
            ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->where('pa.status', 'pending')
            ->where('pa.created_at <', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->get()
            ->getResultArray();

        foreach ($oldPending as $item) {
            $tasks[] = [
                'type' => 'pending_application',
                'title' => 'Pengajuan menunggu persetujuan',
                'description' => "Aplikasi {$item['nama_asesi']} untuk skema {$item['nama_skema']} belum diproses selama lebih dari 3 hari",
                'url' => site_url("admin/pengajuan/view/{$item['id_apl1']}"),
                'created_at' => $item['created_at'],
                'priority' => 'high'
            ];
        }

        // Unassigned assessments
        $unassignedAssessments = $db->table('asesmen')
            ->select('asesmen.*, skema.nama_skema')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->where('asesmen.id_asesor', null)
            ->get()
            ->getResultArray();

        foreach ($unassignedAssessments as $item) {
            $tasks[] = [
                'type' => 'unassigned_assessment',
                'title' => 'Asesmen belum memiliki asesor',
                'description' => "Asesmen untuk skema {$item['nama_skema']} belum ditugaskan ke asesor",
                'url' => site_url("admin/asesmen/edit/{$item['id_asesmen']}"),
                'created_at' => $item['created_at'],
                'priority' => 'medium'
            ];
        }

        // Sort tasks by priority and date
        usort($tasks, function($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            }

            $priorities = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priorities[$a['priority']] - $priorities[$b['priority']];
        });

        return $tasks;
    }
}
