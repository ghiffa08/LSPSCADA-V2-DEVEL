<?php

namespace App\Models;

use App\Entities\Asesor;
use CodeIgniter\Model;

class AsesorModel extends Model
{
    protected $table            = 'asesor';
    protected $primaryKey       = 'id_asesor';
    protected $useAutoIncrement = false;
    protected $returnType       = Asesor::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesor',
        'user_id',
        'no_registrasi',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'pekerjaan',
        'alamat',
        'no_hp',
        'email',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'id_asesor' => 'required|max_length[36]',
        'user_id' => 'required|integer',
        'no_registrasi' => 'required|max_length[50]|is_unique[asesor.no_registrasi,id_asesor,{id_asesor}]',
        'email' => 'required|valid_email|is_unique[asesor.email,id_asesor,{id_asesor}]',
    ];

    protected $validationMessages   = [
        'no_registrasi' => [
            'required' => 'Nomor registrasi asesor harus diisi',
            'max_length' => 'Nomor registrasi maksimal 50 karakter',
            'is_unique' => 'Nomor registrasi sudah digunakan'
        ],
        'email' => [
            'required' => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah digunakan'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get asesor by user ID
     *
     * @param int $userId
     * @return Asesor|null
     */
    public function getByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get asesor with user data (joined)
     *
     * @param string $idAsesor
     * @return array|null
     */
    public function getWithUser(string $idAsesor)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.fullname, u.email as user_email, u.active, u.tanda_tangan');
        $builder->join('users u', 'u.id_user = a.id_user');
        $builder->where('a.id_asesor', $idAsesor);

        return $builder->get()->getRowArray();
    }

    /**
     * Get all asesor with user data
     *
     * @param bool $activeOnly Only return active assessors
     * @return array
     */
    public function getAllWithUser(bool $activeOnly = false)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.fullname, u.email as user_email, u.active');
        $builder->join('users u', 'u.id_user = a.id_user');

        if ($activeOnly) {
            $builder->where('a.is_active', 1);
            $builder->where('u.active', 1);
        }

        $builder->orderBy('u.fullname', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Get assigned skema for an asesor
     *
     * @param string $idAsesor
     * @return array
     */
    public function getAssignedSkema(string $idAsesor)
    {
        $builder = $this->db->table('asesor_skema as');
        $builder->select('s.*');
        $builder->join('skema s', 's.id_skema = as.id_skema');
        $builder->where('as.id_asesor', $idAsesor);

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesor assessment statistics
     *
     * @param string $idAsesor
     * @return array
     */
    public function getAssessmentStats(string $idAsesor)
    {
        // Get assessment counts by status
        $sql = "SELECT 
                    pa.status,
                    COUNT(pa.id_apl1) as count
                FROM 
                    asesmen a
                JOIN 
                    pengajuan_asesmen pa ON pa.id_asesmen = a.id_asesmen
                WHERE 
                    a.id_asesor = ?
                GROUP BY 
                    pa.status";

        $query = $this->db->query($sql, [$idAsesor]);
        $results = $query->getResultArray();

        $stats = [
            'pending' => 0,
            'approved' => 0,
            'on_progress' => 0,
            'completed' => 0,
            'rejected' => 0,
            'total' => 0
        ];

        foreach ($results as $row) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = (int)$row['count'];
                $stats['total'] += (int)$row['count'];
            }
        }

        return $stats;
    }

    /**
     * Assign a skema to an asesor
     *
     * @param string $idAsesor
     * @param string $idSkema
     * @return bool
     */
    public function assignSkema(string $idAsesor, string $idSkema): bool
    {
        $data = [
            'id_asesor' => $idAsesor,
            'id_skema' => $idSkema
        ];

        // Check if assignment already exists
        $exists = $this->db->table('asesor_skema')
            ->where($data)
            ->countAllResults() > 0;

        if ($exists) {
            return true; // Already assigned
        }

        return $this->db->table('asesor_skema')->insert($data);
    }

    /**
     * Unassign a skema from an asesor
     *
     * @param string $idAsesor
     * @param string $idSkema
     * @return bool
     */
    public function unassignSkema(string $idAsesor, string $idSkema): bool
    {
        return $this->db->table('asesor_skema')
            ->where('id_asesor', $idAsesor)
            ->where('id_skema', $idSkema)
            ->delete();
    }

    /**
     * Search asesor by name or registration number
     *
     * @param string $search
     * @return array
     */
    public function search(string $search)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.fullname, u.email as user_email');
        $builder->join('users u', 'u.id_user = a.id_user');
        $builder->groupStart()
            ->like('u.fullname', $search)
            ->orLike('a.no_registrasi', $search)
            ->orLike('a.email', $search)
            ->groupEnd();

        return $builder->get()->getResultArray();
    }
}
