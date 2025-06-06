<?php

namespace App\Models;

use App\Entities\Admin;
use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id_admin';
    protected $useAutoIncrement = false;
    protected $returnType       = Admin::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_admin',
        'user_id',
        'full_name',
        'department',
        'role',
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
        'id_admin' => 'required|max_length[36]',
        'user_id' => 'required|integer',
        'full_name' => 'required|max_length[255]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get admin by user ID
     *
     * @param int $userId
     * @return Admin|null
     */
    public function getByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get admin with user data (joined)
     *
     * @param string $idAdmin
     * @return array|null
     */
    public function getWithUser(string $idAdmin)
    {
        $builder = $this->db->table('admin a');
        $builder->select('a.*, u.fullname, u.email as user_email, u.active, u.tanda_tangan');
        $builder->join('users u', 'u.id_user = a.id_user');
        $builder->where('a.id_admin', $idAdmin);

        return $builder->get()->getRowArray();
    }

    /**
     * Get admin activity log
     *
     * @param string $idAdmin
     * @param int $limit
     * @return array
     */
    public function getActivityLog(string $idAdmin, int $limit = 10)
    {
        $builder = $this->db->table('activity_log');
        $builder->where('actor_id', $idAdmin);
        $builder->where('actor_type', 'admin');
        $builder->orderBy('created_at', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }
}
