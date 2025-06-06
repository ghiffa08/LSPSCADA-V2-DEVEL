<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Asesor extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'tanggal_lahir'];
    protected $casts = [
        'id_asesor' => 'string',
        'user_id' => 'integer',
        'no_registrasi' => 'string',
        'is_active' => 'boolean'
    ];

    /**
     * Get the User entity associated with this Asesor
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
     * Get assigned assessments for this asesor
     *
     * @param string|null $status Filter by status
     * @return array
     */
    public function getAssignedAssessments(?string $status = null)
    {
        $asesmenModel = model('AsesmenModel');
        $query = $asesmenModel->where('id_asesor', $this->attributes['id_asesor']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->findAll();
    }

    /**
     * Get assessments count grouped by status
     *
     * @return array
     */
    public function getAssessmentsCountByStatus()
    {
        $db = \Config\Database::connect();

        $result = $db->table('asesmen')
                    ->select('status, COUNT(*) as count')
                    ->where('id_asesor', $this->attributes['id_asesor'])
                    ->groupBy('status')
                    ->get()
                    ->getResultArray();

        // Convert to associative array with status as key
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }

        return $counts;
    }

    /**
     * Get the skema (competency schemes) that this asesor is qualified to assess
     *
     * @return array
     */
    public function getSkema()
    {
        $db = \Config\Database::connect();

        return $db->table('asesor_skema')
                 ->select('skema.*')
                 ->join('skema', 'skema.id_skema = asesor_skema.id_skema')
                 ->where('asesor_skema.id_asesor', $this->attributes['id_asesor'])
                 ->get()
                 ->getResultArray();
    }

    /**
     * Check if asesor is qualified for a specific skema
     *
     * @param string|int $idSkema
     * @return bool
     */
    public function isQualifiedForSkema($idSkema): bool
    {
        $db = \Config\Database::connect();

        $count = $db->table('asesor_skema')
                   ->where('id_asesor', $this->attributes['id_asesor'])
                   ->where('id_skema', $idSkema)
                   ->countAllResults();

        return $count > 0;
    }

    /**
     * Get the asesi (students/applicants) currently being assessed by this asesor
     *
     * @return array
     */
    public function getActiveAsesi()
    {
        $db = \Config\Database::connect();

        return $db->table('pengajuan_asesmen')
                 ->select('asesi.*')
                 ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
                 ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi')
                 ->where('asesmen.id_asesor', $this->attributes['id_asesor'])
                 ->where('pengajuan_asesmen.status', 'active')
                 ->get()
                 ->getResultArray();
    }
}
