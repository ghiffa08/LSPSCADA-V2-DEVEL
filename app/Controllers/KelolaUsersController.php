<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;
use CodeIgniter\HTTP\ResponseInterface;

class KelolaUsersController extends BaseController
{
    protected $userMythModel; // Renamed to avoid conflict with BaseController
    protected $groupModel;
    protected $groupUserModel;
    protected $db;

    public function __construct()
    {
        helper('auth');

        // Use UserMythModel specifically for soft delete functionality
        $this->userMythModel = new UserMythModel();
        $this->groupModel = new GroupModel();
        $this->groupUserModel = new GroupUserModel();
        $this->db = \Config\Database::connect();

        // Check if user is admin
        if (!in_groups(['Admin'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }
    }

    /**
     * Display users management page
     */
    public function index()
    {
        $data = [
            'siteTitle' => 'Kelola Users'
        ];

        return view('admin/kelola_users', $data);
    }

    /**
     * Get users data for DataTables (AJAX)
     */    public function getUsersData()
    {
        $request = service('request');

        // DataTables parameters
        $draw = intval($request->getPost('draw'));
        $start = intval($request->getPost('start')) ?: 0;
        $length = intval($request->getPost('length')) ?: 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $roleFilter = $request->getPost('role_filter') ?? '';        // Base query - Get all users with their roles
        $builder = $this->userMythModel->select('users.*, 
            GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as roles,
            GROUP_CONCAT(DISTINCT auth_groups.id ORDER BY auth_groups.id SEPARATOR ",") as group_ids')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
            ->groupBy('users.id');

        // Apply role filter
        if (!empty($roleFilter)) {
            $builder->having('roles LIKE', '%' . $roleFilter . '%');
        }

        // Apply search
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('users.username', $searchValue)
                ->orLike('users.email', $searchValue)
                ->orLike('users.nama_lengkap', $searchValue)
                ->orLike('auth_groups.name', $searchValue)
                ->groupEnd();
        }

        // Get total records
        $totalRecords = $this->userMythModel->countAll();
        $filteredRecords = $builder->countAllResults(false);

        // Get data with limit
        $users = $builder->orderBy('users.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResult();

        // Prepare data for DataTables
        $data = [];
        $no = $start + 1;

        foreach ($users as $user) {
            $statusBadge = $user->active
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-danger">Nonaktif</span>';            // Get user roles as array
            $userRoles = !empty($user->roles) ? explode(', ', $user->roles) : [];

            // Create role badges
            $roleBadges = [];
            if (empty($userRoles)) {
                $roleBadges[] = '<span class="badge badge-secondary">No Role</span>';
            } else {
                foreach ($userRoles as $role) {
                    switch (trim($role)) {
                        case 'Admin':
                            $roleBadges[] = '<span class="badge badge-primary">Admin</span>';
                            break;
                        case 'Asesor':
                            $roleBadges[] = '<span class="badge badge-info">Asesor</span>';
                            break;
                        case 'Asesi':
                            $roleBadges[] = '<span class="badge badge-warning">Asesi</span>';
                            break;
                        default:
                            $roleBadges[] = '<span class="badge badge-secondary">' . htmlspecialchars($role) . '</span>';
                    }
                }
            }
            $roleBadgeHtml = implode(' ', $roleBadges);

            $actions = '
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-info view-user" data-id="' . $user->id . '" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning edit-user" data-id="' . $user->id . '" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm ' . ($user->active ? 'btn-secondary' : 'btn-success') . ' toggle-status" 
                            data-id="' . $user->id . '" data-status="' . $user->active . '" 
                            title="' . ($user->active ? 'Nonaktifkan' : 'Aktifkan') . '">
                        <i class="fas fa-' . ($user->active ? 'toggle-off' : 'toggle-on') . '"></i>
                    </button>';

            // Don't allow deleting own account
            if ($user->id != user()->id) {
                $actions .= '
                    <button class="btn btn-sm btn-danger delete-user" data-id="' . $user->id . '" 
                            data-name="' . htmlspecialchars($user->nama_lengkap ?: $user->username) . '" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>';
            }

            $actions .= '</div>';
            $data[] = [
                $no++,
                $user->username ?: '-',
                $user->nama_lengkap ?: '-',
                $user->email,
                $roleBadgeHtml,
                $statusBadge,
                $user->created_at ? date('d/m/Y', strtotime($user->created_at)) : '-',
                $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Get user statistics (AJAX)
     */
    public function getStats()
    {
        $stats = [
            'total' => $this->userMythModel->countAll(),
            'admin' => $this->getUserCountByGroup('Admin'),
            'asesor' => $this->getUserCountByGroup('Asesor'),
            'asesi' => $this->getUserCountByGroup('Asesi'),
            'active' => $this->userMythModel->where('active', 1)->countAllResults(),
            'inactive' => $this->userMythModel->where('active', 0)->countAllResults()
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Get user by ID (AJAX)
     */    public function getUser(int $id): ResponseInterface
    {
        try {
            $user = $this->userMythModel->select('users.*, 
                GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as roles,
                GROUP_CONCAT(DISTINCT auth_groups.id ORDER BY auth_groups.id SEPARATOR ",") as group_ids')
                ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
                ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
                ->groupBy('users.id')
                ->find($id);

            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            // Convert roles string to array for easier handling
            $user->roles_array = !empty($user->roles) ? explode(', ', $user->roles) : [];
            $user->group_ids_array = !empty($user->group_ids) ? explode(',', $user->group_ids) : [];

            $stats = $this->userMythModel->getUserStatistics();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $user,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get user error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Get user details by ID (alias for getUser method)
     */
    public function getUserDetails(int $id): ResponseInterface
    {
        return $this->getUser($id);
    }

    /**
     * Get archived (soft deleted) user details by ID
     */    public function getUserArchivedDetails(int $id): ResponseInterface
    {
        try {
            $user = $this->userMythModel->onlyDeleted()
                ->select('users.*, 
                    GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as roles,
                    GROUP_CONCAT(DISTINCT auth_groups.id ORDER BY auth_groups.id SEPARATOR ",") as group_ids')
                ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
                ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
                ->groupBy('users.id')
                ->find($id);

            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User terarsip tidak ditemukan'
                ]);
            }

            // Convert roles string to array for easier handling
            $user->roles_array = !empty($user->roles) ? explode(', ', $user->roles) : [];
            $user->group_ids_array = !empty($user->group_ids) ? explode(',', $user->group_ids) : [];

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'roles' => $user->roles ?? 'No Role',
                    'roles_array' => $user->roles_array,
                    'group_ids_array' => $user->group_ids_array,
                    'active' => $user->active,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get archived user error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Create new user (AJAX)
     */
    public function createUser()
    {
        try {
            // Validation rules
            $rules = [
                'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]',
                'nama_lengkap' => 'required|min_length[3]|max_length[100]',
                'role' => 'required|in_list[Admin,Asesor,Asesi]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Prepare user data
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userMythModel->insert($userData);

            if ($userId) {
                // Assign role
                $roleName = $this->request->getPost('role');
                $group = $this->groupModel->where('name', $roleName)->first();

                if ($group) {
                    $this->groupUserModel->insert([
                        'user_id' => $userId,
                        'group_id' => $group->id
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil ditambahkan'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan user'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'User creation error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Toggle user status (AJAX)
     */
    public function toggleStatus(int $id): ResponseInterface
    {
        try {
            $user = $this->userMythModel->find($id);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            // Don't allow deactivating own account
            $currentUser = user();
            if ($id == $currentUser->id) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak dapat menonaktifkan akun sendiri'
                ]);
            }

            $newStatus = $user->active ? 0 : 1;

            if ($this->userMythModel->update($id, ['active' => $newStatus])) {
                $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
                log_message('info', "User ID {$id} {$statusText} by admin ID {$currentUser->id}");

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Status user berhasil diubah',
                    'active' => $newStatus
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengubah status user'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'User status toggle error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Update user (AJAX)
     */
    public function updateUser()
    {
        $id = $this->request->getPost('id');
        $user = $this->userMythModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        try {
            // Validation rules
            $rules = [
                'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
                'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
                'nama_lengkap' => 'required|min_length[3]|max_length[100]',
                'role' => 'required|in_list[Admin,Asesor,Asesi]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Prepare update data
            $updateData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password if provided
            $password = $this->request->getPost('password');
            if (!empty($password)) {
                $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($this->userMythModel->update($id, $updateData)) {
                // Update role
                $roleName = $this->request->getPost('role');
                $group = $this->groupModel->where('name', $roleName)->first();

                if ($group) {
                    // Remove existing role assignment
                    $this->groupUserModel->where('user_id', $id)->delete();

                    // Add new role assignment
                    $this->groupUserModel->insert([
                        'user_id' => $id,
                        'group_id' => $group->id
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil diupdate'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate user'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'User update error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupdate user'
            ]);
        }
    }

    /**
     * Delete user (AJAX) - Using Soft Delete
     */
    public function deleteUser($id)
    {
        $user = $this->userMythModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        // Don't allow deleting own account
        if ($id == user()->id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak dapat menghapus akun sendiri']);
        }

        // Soft delete user (CodeIgniter will set deleted_at automatically)
        if ($this->userMythModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'User berhasil dihapus (soft delete)'
            ]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus user']);
        }
    }

    /**
     * Show deleted users page
     */
    public function deletedUsers()
    {
        $data = [
            'siteTitle' => 'Users Terhapus',
            'totalDeletedUsers' => $this->userMythModel->onlyDeleted()->countAllResults(),
        ];

        return view('admin/deleted_users', $data);
    }

    /**
     * Restore deleted user
     */
    public function restoreUser($id)
    {
        try {
            // Find user in deleted records
            $user = $this->userMythModel->onlyDeleted()->find($id);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'User tidak ditemukan dalam daftar terhapus'
                ]);
            }

            // Restore user by setting deleted_at to null
            if ($this->userMythModel->restoreUser($id)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User berhasil direstore'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal merestore user'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error restoring user: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Terjadi kesalahan saat merestore user'
            ]);
        }
    }

    /**
     * Get deleted users data for DataTables (AJAX)
     */
    public function getDeletedUsersData()
    {
        if (!$this->request->isAJAX()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        try {
            $request = service('request');

            $draw = intval($request->getPost('draw'));
            $start = intval($request->getPost('start')) ?: 0;
            $length = intval($request->getPost('length')) ?: 10;
            $search = $request->getPost('search')['value'] ?? '';
            $orderColumn = intval($request->getPost('order')[0]['column']) ?: 0;
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

            // Column mapping
            $columns = ['id', 'username', 'nama_lengkap', 'email', 'role', 'deleted_at'];
            $orderBy = $columns[$orderColumn] ?? 'deleted_at';

            $result = $this->userMythModel->getDeletedUsersForDataTable($length, $start, $search, $orderBy, $orderDir);
            $data = [];
            foreach ($result['data'] as $user) {
                $data[] = [
                    'id' => $user->id,
                    'username' => $user->username ?? '-',
                    'nama_lengkap' => $user->nama_lengkap ?? '-',
                    'email' => $user->email,
                    'roles' => $user->role ?? 'No Role',
                    'deleted_at' => $user->deleted_at,
                    'action' => '<div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success" onclick="restoreUser(' . $user->id . ')" title="Restore">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="permanentlyDeleteUser(' . $user->id . ')" title="Hapus Permanen">
                            <i class="fas fa-trash-alt"></i> Hapus Permanen
                        </button>
                    </div>'
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $result['total_records'],
                'recordsFiltered' => $result['total_filtered'],
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get deleted users data error: ' . $e->getMessage());

            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get deleted users statistics (AJAX)
     */
    public function getDeletedStats()
    {
        if (!$this->request->isAJAX()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        try {
            $stats = $this->userMythModel->getUserStatistics();

            return $this->response->setJSON([
                'total_deleted' => $stats['deleted_users'],
                'deleted_admin' => $this->userMythModel->onlyDeleted()
                    ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
                    ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
                    ->where('auth_groups.name', 'Admin')
                    ->countAllResults(),
                'deleted_asesor' => $this->userMythModel->onlyDeleted()
                    ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
                    ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
                    ->where('auth_groups.name', 'Asesor')
                    ->countAllResults(),
                'deleted_asesi' => $this->userMythModel->onlyDeleted()
                    ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
                    ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
                    ->where('auth_groups.name', 'Asesi')
                    ->countAllResults(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get deleted users stats error: ' . $e->getMessage());

            return $this->response->setJSON([
                'total_deleted' => 0,
                'deleted_admin' => 0,
                'deleted_asesor' => 0,
                'deleted_asesi' => 0,
                'error' => 'Error loading stats'
            ]);
        }
    }

    /**
     * Helper method to get user count by group
     */
    private function getUserCountByGroup($groupName)
    {
        return $this->userMythModel->select('users.id')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
            ->where('auth_groups.name', $groupName)
            ->countAllResults();
    }
    /**
     * Permanently delete user
     */
    public function permanentlyDeleteUser($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid request'
            ]);
        }

        try {
            // Get ID from parameter or POST data
            $userId = $id ?: $this->request->getPost('id');

            if (!$userId) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'ID pengguna tidak ditemukan'
                ]);
            }

            // Don't allow permanently deleting own account
            if ($userId == user()->id) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri secara permanen'
                ]);
            }

            // Find user in deleted records only
            $user = $this->userMythModel->onlyDeleted()->find($userId);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'User tidak ditemukan dalam daftar terhapus'
                ]);
            }

            // Start transaction
            $this->db->transStart();

            // Delete group assignments first
            $this->groupUserModel->where('user_id', $userId)->delete();

            // Permanently delete user (force delete)
            $this->userMythModel->where('id', $userId)->delete(null, true);

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menghapus user secara permanen'
                ]);
            }

            log_message('info', "User ID {$userId} permanently deleted by admin ID " . user()->id);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'User berhasil dihapus secara permanen'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error permanently deleting user: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menghapus user secara permanen: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Batch action for deleted users (restore/permanently delete multiple users)
     */
    public function batchAction()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid request'
            ]);
        }

        try {
            $action = $this->request->getPost('action');
            $userIds = $this->request->getPost('user_ids');

            if (!$action || !$userIds || !is_array($userIds)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Parameter tidak lengkap'
                ]);
            }

            // Remove current user ID from the list to prevent self-action
            $currentUserId = user()->id;
            $userIds = array_filter($userIds, function ($id) use ($currentUserId) {
                return $id != $currentUserId;
            });

            if (empty($userIds)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Tidak ada user yang valid untuk diproses'
                ]);
            }

            $this->db->transStart();
            $processed = 0;
            $errors = [];

            foreach ($userIds as $userId) {
                try {
                    if ($action === 'restore') {
                        // Find user in deleted records
                        $user = $this->userMythModel->onlyDeleted()->find($userId);
                        if ($user && $this->userMythModel->restoreUser($userId)) {
                            $processed++;
                        } else {
                            $errors[] = "Gagal restore user ID: {$userId}";
                        }
                    } else if ($action === 'permanent_delete') {
                        // Find user in deleted records
                        $user = $this->userMythModel->onlyDeleted()->find($userId);
                        if ($user) {
                            // Delete group assignments
                            $this->groupUserModel->where('user_id', $userId)->delete();
                            // Permanently delete
                            $this->userMythModel->where('id', $userId)->delete(null, true);
                            $processed++;
                        } else {
                            $errors[] = "User ID {$userId} tidak ditemukan dalam daftar terhapus";
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing user ID {$userId}: " . $e->getMessage();
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Transaksi batch gagal'
                ]);
            }

            $message = "{$processed} user berhasil diproses";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            log_message('info', "Batch action '{$action}' performed on " . count($userIds) . " users by admin ID " . user()->id);

            return $this->response->setJSON([
                'status' => true,
                'message' => $message,
                'processed' => $processed,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in batch action: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Terjadi kesalahan dalam batch action: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all available groups/roles (AJAX)
     */
    public function getAvailableRoles()
    {
        if (!$this->request->isAJAX()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        try {
            $groups = $this->groupModel->findAll();

            $roles = [];
            foreach ($groups as $group) {
                $roles[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description ?? ''
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get available roles error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil daftar roles'
            ]);
        }
    }
}
