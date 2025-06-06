<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupUserModel extends Model
{
    protected $table            = 'auth_groups_users';
    protected $primaryKey       = false;
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['group_id', 'user_id'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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

    // JOIN
    public function getGroupUsers()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->join('users', 'users.id=auth_groups_users.user_id', 'left')
            ->select('users.id as userid, users.username, auth_groups.id as groupid, users.fullname as userfullname, auth_groups.name as groupname, auth_groups_users.id as groupuserid')
            ->Get()->getResultArray();
    }

    public function getAsesors()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->join('users', 'users.id=auth_groups_users.user_id', 'left')
            ->where('auth_groups.name', 'Asesor')
            ->select('users.id as userid, users.username, users.email, users.no_telp, users.fullname, auth_groups.id as groupid, users.fullname as userfullname, auth_groups.name as groupname')
            ->Get()->getResultArray();
    }

    public function getAsesi()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->join('users', 'users.id=auth_groups_users.user_id', 'left')
            ->where('auth_groups.name', 'Asesi')
            ->select('users.id as userid, users.username, users.email, users.no_telp, users.fullname, auth_groups.id as groupid, users.fullname as userfullname, auth_groups.name as groupname')
            ->Get()->getResultArray();
    }
    public function getAdmin()
    {
        return $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->join('users', 'users.id=auth_groups_users.user_id', 'left')
            ->where('auth_groups.name', 'Admin')
            ->select('users.id as userid, users.username, users.email, users.no_telp, users.fullname, auth_groups.id as groupid, users.fullname as userfullname, auth_groups.name as groupname')
            ->Get()->getResultArray();
    }

    /**
     * Get all group names (roles) for a user by user_id
     * @param int|string $userId
     * @return array
     */
    public function getRolesByUserId($userId)
    {
        $result = $this->db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->where('auth_groups_users.user_id', $userId)
            ->select('auth_groups.name')
            ->get()->getResultArray();
        return array_map(fn($row) => $row['name'], $result);
    }
}
