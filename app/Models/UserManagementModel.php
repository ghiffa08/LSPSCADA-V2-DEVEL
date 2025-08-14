<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class UserManagementModel extends Model
{
    use DataTableTrait;

    // Database table and primary key
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'email',
        'nama_lengkap',
        'active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Fields that should be searched when using DataTable
    protected array $dataTableSearchFields = [
        'users.username',
        'users.email',
        'users.nama_lengkap',
        'auth_groups.name'
    ];
    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.deleted_at IS NULL') // Explicitly exclude deleted users
            ->groupBy('users.id'); // Group by user ID to avoid duplicates
    }
    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('
            users.id as id,
            users.username,
            users.email,
            users.nama_lengkap,
            users.active,
            users.created_at,
            users.updated_at,
            GROUP_CONCAT(DISTINCT auth_groups.name SEPARATOR ", ") as roles
        ');
    }
    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults($data)
    {
        foreach ($data as &$row) {
            // Handle null values by converting to empty string or appropriate defaults
            $row['username'] = $row['username'] ?? '';
            $row['email'] = $row['email'] ?? '';
            $row['nama_lengkap'] = $row['nama_lengkap'] ?? '';
            $row['active'] = isset($row['active']) ? (int)$row['active'] : 0;
            $row['created_at'] = $row['created_at'] ?? null;
            $row['updated_at'] = $row['updated_at'] ?? null;

            // Convert roles string to array for JavaScript
            $roles = !empty($row['roles']) ? explode(', ', $row['roles']) : [];
            $row['roles_array'] = $roles;
            $row['role_count'] = count($roles);

            // Keep original roles as string for display
            if (empty($row['roles']) || $row['roles'] === null) {
                $row['roles'] = 'No Role';
            }
        }

        return $data;
    }
    /**
     * Get users by role with SSR support
     */
    public function getUsersByRole($roleName = null)
    {
        $builder = $this->db->table($this->table);
        $this->applyDataTableJoins($builder);
        $this->applyDataTableSelects($builder);

        if ($roleName && $roleName !== 'all') {
            $builder->where('auth_groups.name', $roleName);
        }

        $builder->orderBy('users.nama_lengkap', 'ASC');

        $data = $builder->get()->getResultArray();
        return $this->transformDataTableResults($data);
    }
    /**
     * Get user statistics for dashboard
     */
    public function getUserStatistics()
    {
        $stats = [];

        // Total users (excluding deleted)
        $stats['total_users'] = $this->where('deleted_at IS NULL')->countAllResults();

        // Active users (excluding deleted)
        $stats['active_users'] = $this->where('active', 1)->where('deleted_at IS NULL')->countAllResults();

        // Users by role (excluding deleted)
        $roleStats = $this->db->table('auth_groups')
            ->select('auth_groups.name, COUNT(DISTINCT users.id) as user_count')
            ->join('auth_groups_users', 'auth_groups_users.group_id = auth_groups.id', 'left')
            ->join('users', 'users.id = auth_groups_users.user_id AND users.deleted_at IS NULL', 'left')
            ->groupBy('auth_groups.id, auth_groups.name')
            ->orderBy('auth_groups.name')
            ->get()->getResultArray();

        foreach ($roleStats as $role) {
            $stats['role_' . strtolower($role['name'])] = $role['user_count'];
        }

        return $stats;
    }

    /**
     * Get user details by ID including roles
     */
    public function getUserById($userId)
    {
        $builder = $this->db->table($this->table);
        $builder->select('
            users.*,
            GROUP_CONCAT(DISTINCT auth_groups.name SEPARATOR ", ") as roles
        ')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.id', $userId)
            ->groupBy('users.id');

        $result = $builder->get()->getRowArray();

        if ($result) {
            // Convert roles string to array
            $roles = !empty($result['roles']) ? explode(', ', $result['roles']) : [];
            $result['roles_array'] = $roles;
            $result['role_count'] = count($roles);

            if (empty($result['roles'])) {
                $result['roles'] = 'No Role';
            }
        }

        return $result;
    }

    /**
     * Get active users (status = 1)
     */
    public function getActiveUsers(): array
    {
        return $this->where('active', 1)
            ->orderBy('nama_lengkap', 'ASC')
            ->findAll();
    }

    /**
     * Check if user exists and is active
     */
    public function isValidUser(int $userId): bool
    {
        return $this->where('id', $userId)
            ->where('active', 1)
            ->countAllResults() > 0;
    }
    /**
     * Get data for DataTable with role filter support
     *
     * @param int $limit Limit per page
     * @param int $start Offset
     * @param string $search Search keyword
     * @param string $orderColumn Column to order by
     * @param string $orderDir Order direction (asc/desc)
     * @param string $roleFilter Role filter
     * @return array
     */
    public function getDataTableWithRoleFilter($limit = 10, $start = 0, $search = '', $orderColumn = null, $orderDir = 'asc', $roleFilter = '')
    {
        // Start building the query using the database table directly
        $builder = $this->db->table($this->table);

        // Apply joins
        $builder = $this->applyDataTableJoins($builder);

        // Apply custom selects
        $builder = $this->applyDataTableSelects($builder);

        // Apply role filter BEFORE grouping if provided
        if (!empty($roleFilter) && $roleFilter !== 'all') {
            $builder->where('auth_groups.name', $roleFilter);
        }        // Get total count before search filtering (rebuild query for count)
        $countBuilder = $this->db->table($this->table);
        $countBuilder->select('users.id')  // Only select users.id to avoid duplicate column issues
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.deleted_at IS NULL'); // Exclude deleted users

        if (!empty($roleFilter) && $roleFilter !== 'all') {
            $countBuilder->where('auth_groups.name', $roleFilter);
        }

        $countBuilder->groupBy('users.id');
        $total = $countBuilder->countAllResults();

        // Apply search if provided
        if ($search !== '' && !empty($this->dataTableSearchFields)) {
            $builder->groupStart();
            foreach ($this->dataTableSearchFields as $index => $field) {
                if ($index === 0) {
                    $builder->like($field, $search);
                } else {
                    $builder->orLike($field, $search);
                }
            }
            $builder->groupEnd();
        }        // Get filtered count (rebuild query for count)
        $filteredBuilder = $this->db->table($this->table);
        $filteredBuilder->select('users.id')  // Only select users.id to avoid duplicate column issues
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.deleted_at IS NULL'); // Exclude deleted users

        if (!empty($roleFilter) && $roleFilter !== 'all') {
            $filteredBuilder->where('auth_groups.name', $roleFilter);
        }

        $filteredBuilder->groupBy('users.id');

        // Apply the same search filters for count
        if ($search !== '' && !empty($this->dataTableSearchFields)) {
            $filteredBuilder->groupStart();
            foreach ($this->dataTableSearchFields as $index => $field) {
                if ($index === 0) {
                    $filteredBuilder->like($field, $search);
                } else {
                    $filteredBuilder->orLike($field, $search);
                }
            }
            $filteredBuilder->groupEnd();
        }

        $filtered = $filteredBuilder->countAllResults();

        // Apply sorting
        if ($orderColumn) {
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            $builder->orderBy('users.nama_lengkap', 'ASC');
        }

        // Apply limit and offset for pagination
        $data = $builder->limit($limit, $start)->get()->getResultArray();

        // Apply custom transformations
        if (method_exists($this, 'transformDataTableResults')) {
            $data = $this->transformDataTableResults($data);
        }
        return [
            'total' => $total,
            'filtered' => $filtered,
            'data' => $data
        ];
    }

    /**
     * Soft delete user - mark as deleted instead of permanently removing
     *
     * @param int $userId
     * @return bool
     */
    public function softDeleteUser($userId)
    {
        return $this->delete($userId);
    }
    /**
     * Restore soft deleted user
     *
     * @param int $userId
     * @return array
     */
    public function restoreUser($userId)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Check if user exists in deleted records
            $user = $this->onlyDeleted()->find($userId);
            if (!$user) {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Pengguna yang diarsipkan tidak ditemukan'
                ];
            }

            // Use direct database query to restore user (set deleted_at to NULL)
            $result = $db->table($this->table)
                ->where('id', $userId)
                ->update(['deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);

            if ($result) {
                $db->transCommit();
                return [
                    'success' => true,
                    'message' => 'Pengguna berhasil dipulihkan dari arsip'
                ];
            } else {
                $db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Gagal memulihkan pengguna'
                ];
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Error restoring user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create admin user with Admin group
     *
     * @param array $userData
     * @return array
     */
    public function createAdminUser($userData)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Create user
            $userModel = new \App\Models\UserMythModel();
            $groupModel = new \Myth\Auth\Models\GroupModel();

            $user = new \App\Entities\User($userData);
            $user->activate(); // Activate immediately for admin

            $userModel = $userModel->withGroup('Admin');

            if (!$userModel->save($user)) {
                $db->transRollback();
                return [
                    'success' => false,
                    'errors' => $userModel->errors(),
                    'message' => 'Failed to create admin user'
                ];
            }

            $db->transCommit();
            return [
                'success' => true,
                'user_id' => $userModel->getInsertID(),
                'message' => 'Admin user created successfully'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create asesor user with Asesor group
     *
     * @param array $userData
     * @return array
     */    public function createAsesorUser($userData)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Create user
            $userModel = new \App\Models\UserMythModel();
            $asesorModel = new \App\Models\AsesorModel();
            $groupModel = new \Myth\Auth\Models\GroupModel();

            $user = new \App\Entities\User($userData);
            $user->activate(); // Activate immediately for asesor

            $userModel = $userModel->withGroup('Asesor');

            if (!$userModel->save($user)) {
                $db->transRollback();
                return [
                    'success' => false,
                    'errors' => $userModel->errors(),
                    'message' => 'Failed to create asesor user'
                ];
            }

            $userId = $userModel->getInsertID();

            // Create asesor record
            $asesorData = [
                'id_user' => $userId,
                'nomor_registrasi' => $userData['nomor_registrasi'] ?? null
            ];

            if (!$asesorModel->save($asesorData)) {
                $db->transRollback();
                return [
                    'success' => false,
                    'errors' => $asesorModel->errors(),
                    'message' => 'Failed to create asesor record'
                ];
            }

            // Get the new asesor ID
            $asesorId = $asesorModel->getInsertID();

            // Handle skema (now single skema instead of multiple)
            $skema_id = $userData['skema_id'] ?? null;

            if (!empty($skema_id)) {
                // Log debug info
                log_message('debug', 'Create Asesor - Handling skema_id: ' . $skema_id);

                // Update asesor with skema
                $asesorData['id_skema'] = $skema_id;

                // Update the asesor record with skema
                if (!$asesorModel->update($asesorId, ['id_skema' => $skema_id])) {
                    log_message('error', 'Failed to assign skema to asesor: ' . $asesorId);
                    $db->transRollback();
                    return [
                        'success' => false,
                        'message' => 'Failed to assign skema to asesor'
                    ];
                }

                log_message('debug', 'Create Asesor - Successfully assigned skema ' . $skema_id . ' to asesor ' . $asesorId);
            }

            $db->transCommit();
            return [
                'success' => true,
                'user_id' => $userId,
                'asesor_id' => $asesorModel->getInsertID(),
                'message' => 'Asesor user created successfully'
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Error creating asesor user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all available roles/groups
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        return $this->db->table('auth_groups')
            ->select('id, name, description')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Change user role/group
     *
     * @param int $userId
     * @param string $newRoleName
     * @return bool
     */
    public function changeUserRole($userId, $newRoleName)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Get group ID
            $group = $db->table('auth_groups')
                ->where('name', $newRoleName)
                ->get()
                ->getFirstRow('array');

            if (!$group) {
                throw new \Exception('Role not found');
            }

            // Remove existing group memberships
            $db->table('auth_groups_users')
                ->where('user_id', $userId)
                ->delete();

            // Add to new group
            $db->table('auth_groups_users')->insert([
                'group_id' => $group['id'],
                'user_id' => $userId
            ]);

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
    /**
     * Get deleted users for recovery purposes
     *
     * @return array
     */
    public function getDeletedUsers()
    {
        return $this->onlyDeleted()
            ->select('users.*, 
                      GROUP_CONCAT(auth_groups.name SEPARATOR ", ") as roles,
                      users.deleted_at')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->groupBy('users.id')
            ->orderBy('users.deleted_at', 'DESC')
            ->findAll();
    }

    /**
     * Get deleted users with DataTable support
     *
     * @param array $requestData
     * @return array
     */
    public function getDeletedUsersDataTable($requestData = [])
    {
        $builder = $this->onlyDeleted()
            ->select('users.id, users.username, users.nama_lengkap, users.email, 
                      users.active, users.created_at, users.deleted_at,
                      GROUP_CONCAT(auth_groups.name SEPARATOR ", ") as roles')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->groupBy('users.id');

        return $this->processDataTableRequest($builder, $requestData);
    }
    /**
     * Permanently delete a user
     *
     * @param int $userId
     * @return array
     */
    public function permanentDelete($userId)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Check if user exists in deleted records
            $user = $this->onlyDeleted()->find($userId);
            if (!$user) {
                throw new \Exception('Pengguna yang diarsipkan tidak ditemukan');
            }

            // Delete related records first
            $db->table('auth_groups_users')->where('user_id', $userId)->delete();
            $db->table('auth_identities')->where('user_id', $userId)->delete();
            $db->table('auth_logins')->where('user_id', $userId)->delete();
            $db->table('auth_permissions_users')->where('user_id', $userId)->delete();
            $db->table('auth_remember_tokens')->where('user_id', $userId)->delete();

            // Permanently delete the user using direct database query
            $result = $db->table($this->table)->where('id', $userId)->delete();

            if ($result) {
                $db->transCommit();
                return [
                    'success' => true,
                    'message' => 'Pengguna berhasil dihapus secara permanen'
                ];
            } else {
                throw new \Exception('Gagal menghapus pengguna secara permanen');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Error permanently deleting user: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get statistics including deleted users
     *
     * @return array
     */
    public function getUserStatisticsWithDeleted()
    {
        $stats = $this->getUserStatistics();

        // Add deleted users count
        $deletedCount = $this->onlyDeleted()->countAllResults();
        $stats['deleted_users'] = $deletedCount;

        return $stats;
    }

    /**
     * Get deleted user details by ID for modal display
     *
     * @param int $userId
     * @return array|null
     */
    public function getDeletedUserDetails($userId)
    {
        $builder = $this->onlyDeleted()
            ->select('users.*, 
                      GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as roles,
                      users.deleted_at')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.id', $userId)
            ->groupBy('users.id');

        $result = $builder->get()->getRowArray();

        if ($result) {
            // Convert roles string to array for better handling
            $roles = !empty($result['roles']) ? explode(', ', $result['roles']) : [];
            $result['roles_array'] = $roles;
            $result['role_count'] = count($roles);

            if (empty($result['roles'])) {
                $result['roles'] = 'No Role';
            }

            // Format dates for display
            if ($result['created_at']) {
                $result['created_at_formatted'] = date('d M Y H:i', strtotime($result['created_at']));
            }
            if ($result['updated_at']) {
                $result['updated_at_formatted'] = date('d M Y H:i', strtotime($result['updated_at']));
            }
            if ($result['deleted_at']) {
                $result['deleted_at_formatted'] = date('d M Y H:i', strtotime($result['deleted_at']));
            }
        }

        return $result;
    }
}
