<?php

namespace App\Models;

use CodeIgniter\Model;
use Faker\Generator;
use Myth\Auth\Authorization\GroupModel;
use Myth\Auth\Entities\User;

/**
 * @method User|null first()
 */
class UserMythModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'App\\Entities\\User';
    protected $useSoftDeletes = true; // Enable soft deletes
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'email',
        'username',
        'nama_lengkap',
        'password_hash',
        'reset_hash',
        'reset_at',
        'reset_expires',
        'activate_hash',
        'status',
        'status_message',
        'active',
        'force_pass_reset',
        'google_id',
        'avatar'
    ];
    protected $useTimestamps   = true;
    protected $createdField    = 'created_at';
    protected $updatedField    = 'updated_at';
    protected $validationRules = [
        'email'         => 'required|valid_email|is_unique[users.email,id,{id}]',
        'username'      => 'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username,id,{id}]',
        'password_hash' => 'required',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $afterInsert        = ['addToGroup'];

    /**
     * The id of a group to assign.
     * Set internally by withGroup.
     *
     * @var int|null
     */
    protected $assignGroup;


    /**
     * Logs a password reset attempt for posterity sake.
     */
    public function logResetAttempt(string $email, ?string $token = null, ?string $ipAddress = null, ?string $userAgent = null)
    {
        $this->db->table('auth_reset_attempts')->insert([
            'email'      => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Logs an activation attempt for posterity sake.
     */
    public function logActivationAttempt(?string $token = null, ?string $ipAddress = null, ?string $userAgent = null)
    {
        $this->db->table('auth_activation_attempts')->insert([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Sets the group to assign any users created.
     *
     * @return $this
     */
    public function withGroup(string $groupName)
    {
        $group = $this->db->table('auth_groups')->where('name', $groupName)->get()->getFirstRow();

        $this->assignGroup = $group->id;

        return $this;
    }

    /**
     * Clears the group to assign to newly created users.
     *
     * @return $this
     */
    public function clearGroup()
    {
        $this->assignGroup = null;

        return $this;
    }

    /**
     * If a default role is assigned in Config\Auth, will
     * add this user to that group. Will do nothing
     * if the group cannot be found.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function addToGroup($data)
    {
        if (is_numeric($this->assignGroup)) {
            $groupModel = model(GroupModel::class);
            $groupModel->addUserToGroup($data['id'], $this->assignGroup);
        }

        return $data;
    }

    /**
     * Faked data for Fabricator.
     */
    public function fake(Generator &$faker): User
    {
        return new User([
            'email'    => $faker->email,
            'username' => $faker->userName,
            'password' => bin2hex(random_bytes(16)),
        ]);
    }

    /**
     * Get only deleted users
     */
    public function getDeletedUsers()
    {
        return $this->onlyDeleted()->findAll();
    }
    /**
     * Get deleted users with pagination for DataTables
     */    public function getDeletedUsersForDataTable($limit, $start, $search = '', $orderBy = 'deleted_at', $orderDir = 'DESC')
    {
        // Ensure parameters are integers
        $limit = (int) $limit;
        $start = (int) $start;

        $builder = $this->onlyDeleted()
            ->select('users.*, 
                GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as role')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
            ->groupBy('users.id');

        if (!empty($search)) {
            $builder->having('users.username LIKE', "%{$search}%")
                ->orHaving('users.email LIKE', "%{$search}%")
                ->orHaving('users.nama_lengkap LIKE', "%{$search}%")
                ->orHaving('role LIKE', "%{$search}%");
        }

        $totalFiltered = $builder->countAllResults(false);

        $users = $builder->orderBy("users.{$orderBy}", $orderDir)
            ->limit($limit, $start)
            ->get()
            ->getResult();

        return [
            'data' => $users,
            'total_filtered' => $totalFiltered,
            'total_records' => $this->onlyDeleted()->countAllResults()
        ];
    }
    /**
     * Restore soft deleted user
     */
    public function restoreUser($id)
    {
        // Use builder to update soft deleted records
        return $this->builder()
            ->where('id', $id)
            ->where('deleted_at IS NOT NULL', null, false)
            ->update(['deleted_at' => null]);
    }

    /**
     * Permanently delete user
     */
    public function permanentlyDelete($id)
    {
        return $this->delete($id, true);
    }

    /**
     * Get user statistics including deleted
     */
    public function getUserStatistics()
    {
        return [
            'total_users' => $this->withDeleted()->countAllResults(),
            'active_users' => $this->countAllResults(),
            'deleted_users' => $this->onlyDeleted()->countAllResults(),
            'total_admin' => $this->getUserCountByRole('Admin'),
            'total_asesor' => $this->getUserCountByRole('Asesor'),
            'total_asesi' => $this->getUserCountByRole('Asesi'),
        ];
    }

    /**
     * Get user count by role
     */
    public function getUserCountByRole($roleName)
    {
        return $this->select('users.id')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
            ->where('auth_groups.name', $roleName)
            ->countAllResults();
    }
}
