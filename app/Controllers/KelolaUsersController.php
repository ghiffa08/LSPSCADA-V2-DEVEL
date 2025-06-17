<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

class KelolaUsersController extends BaseController
{
    protected $userMythModel;
    protected $groupModel;
    protected $groupUserModel;
    protected $db;
    protected $cache;

    // Cache keys for optimization
    private const CACHE_ROLES_KEY = 'available_roles';
    private const CACHE_STATS_KEY = 'user_statistics';
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct()
    {
        helper(['auth', 'security']);

        // Initialize models
        $this->userMythModel = new UserMythModel();
        $this->groupModel = new GroupModel();
        $this->groupUserModel = new GroupUserModel();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();

        // Security check - Admin only
        if (!in_groups(['Admin'])) {
            log_message('warning', 'Unauthorized access attempt to user management by user ID: ' . (user()->id ?? 'anonymous'));
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
     * Get users data for DataTables (AJAX) - Optimized for production
     */
    public function getUsersData()
    {
        // Security check
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $request = service('request');

            // Validate and sanitize DataTables parameters
            $draw = intval($request->getPost('draw'));
            $start = max(0, intval($request->getPost('start')));
            $length = min(100, max(10, intval($request->getPost('length')))); // Limit max records
            $searchValue = trim($request->getPost('search')['value'] ?? '');
            $roleFilter = trim($request->getPost('role_filter') ?? '');

            // Validate search length to prevent performance issues
            if (strlen($searchValue) > 0 && strlen($searchValue) < 2) {
                $searchValue = ''; // Ignore too short search terms
            }

            // Build optimized query with proper indexing hints
            $builder = $this->userMythModel->select('users.id, users.username, users.nama_lengkap, 
                users.email, users.active, users.created_at, 
                GROUP_CONCAT(DISTINCT auth_groups.name ORDER BY auth_groups.name SEPARATOR ", ") as roles,
                GROUP_CONCAT(DISTINCT auth_groups.id ORDER BY auth_groups.id SEPARATOR ",") as group_ids')
                ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
                ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
                ->groupBy('users.id');

            // Apply role filter with optimization
            if (!empty($roleFilter)) {
                $builder->join('auth_groups_users agu2', 'users.id = agu2.user_id', 'inner')
                    ->join('auth_groups ag2', 'agu2.group_id = ag2.id', 'inner')
                    ->where('ag2.name', $roleFilter);
            }

            // Apply search with full-text search optimization
            if (!empty($searchValue)) {
                $searchValue = $this->db->escapeLikeString($searchValue);
                $builder->groupStart()
                    ->like('users.username', $searchValue)
                    ->orLike('users.email', $searchValue)
                    ->orLike('users.nama_lengkap', $searchValue)
                    ->groupEnd();
            }

            // Get total and filtered records efficiently
            $totalRecords = $this->getCachedUserCount();

            // Clone builder for filtered count
            $filteredBuilder = clone $builder;
            $filteredRecords = $filteredBuilder->countAllResults(false);

            // Get data with optimized ordering
            $users = $builder->orderBy('users.created_at', 'DESC')
                ->limit($length, $start)
                ->get()
                ->getResult();

            // Prepare data efficiently
            $data = $this->formatUsersForDataTable($users, $start);

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getUsersData: ' . $e->getMessage());

            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Server error occurred'
            ]);
        }
    }

    /**
     * Get cached user count for performance
     */
    private function getCachedUserCount(): int
    {
        $cacheKey = 'total_users_count';
        $totalUsers = $this->cache->get($cacheKey);

        if ($totalUsers === null) {
            $totalUsers = $this->userMythModel->countAll();
            $this->cache->save($cacheKey, $totalUsers, 60); // Cache for 1 minute
        }

        return $totalUsers;
    }

    /**
     * Format users data for DataTable efficiently
     */
    private function formatUsersForDataTable(array $users, int $start): array
    {
        $data = [];
        $no = $start + 1;
        $currentUser = user();

        foreach ($users as $user) {
            $statusBadge = $user->active
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-danger">Nonaktif</span>';

            // Get user roles as array and create badges
            $userRoles = !empty($user->roles) ? explode(', ', $user->roles) : [];
            $roleBadges = $this->generateRoleBadges($userRoles);

            // Generate action buttons with security check
            $actions = $this->generateActionButtons($user, $currentUser);

            $data[] = [
                $no++,
                esc($user->username ?: '-'),
                esc($user->nama_lengkap ?: '-'),
                esc($user->email),
                $roleBadges,
                $statusBadge,
                $user->created_at ? date('d/m/Y', strtotime($user->created_at)) : '-',
                $actions
            ];
        }

        return $data;
    }

    /**
     * Generate role badges HTML
     */
    private function generateRoleBadges(array $userRoles): string
    {
        if (empty($userRoles)) {
            return '<span class="badge badge-secondary">No Role</span>';
        }

        $badges = [];
        foreach ($userRoles as $role) {
            $role = trim($role);
            $badgeClass = match ($role) {
                'Admin' => 'badge-primary',
                'Asesor' => 'badge-info',
                'Asesi' => 'badge-warning',
                default => 'badge-secondary'
            };
            $badges[] = '<span class="badge ' . $badgeClass . '">' . esc($role) . '</span>';
        }

        return implode(' ', $badges);
    }

    /**
     * Generate action buttons with security checks
     */
    private function generateActionButtons(object $user, object $currentUser): string
    {
        $actions = '<div class="btn-group" role="group">';

        // View button
        $actions .= '<button class="btn btn-sm btn-info view-user" data-id="' . $user->id . '" title="Lihat Detail">
            <i class="fas fa-eye"></i>
        </button>';

        // Edit button
        $actions .= '<button class="btn btn-sm btn-warning edit-user" data-id="' . $user->id . '" title="Edit">
            <i class="fas fa-edit"></i>
        </button>';

        // Toggle status button
        $actions .= '<button class="btn btn-sm ' . ($user->active ? 'btn-secondary' : 'btn-success') . ' toggle-status" 
                data-id="' . $user->id . '" data-status="' . $user->active . '" 
                title="' . ($user->active ? 'Nonaktifkan' : 'Aktifkan') . '">
            <i class="fas fa-' . ($user->active ? 'toggle-off' : 'toggle-on') . '"></i>
        </button>';

        // Delete button (with security check)
        if ($user->id != $currentUser->id) {
            $actions .= '<button class="btn btn-sm btn-danger delete-user" data-id="' . $user->id . '" 
                    data-name="' . esc($user->nama_lengkap ?: $user->username) . '" title="Hapus">
                <i class="fas fa-trash"></i>
            </button>';
        }

        $actions .= '</div>';
        return $actions;
    }
    /**
     * Get user statistics (AJAX) - Optimized with caching
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            // Try to get from cache first
            $stats = $this->cache->get(self::CACHE_STATS_KEY);

            if ($stats === null) {
                // Calculate stats with optimized queries
                $stats = [
                    'total' => $this->userMythModel->countAll(),
                    'admin' => $this->getUserCountByGroupOptimized('Admin'),
                    'asesor' => $this->getUserCountByGroupOptimized('Asesor'),
                    'asesi' => $this->getUserCountByGroupOptimized('Asesi'),
                    'active' => $this->userMythModel->where('active', 1)->countAllResults(),
                    'inactive' => $this->userMythModel->where('active', 0)->countAllResults(),
                    'deleted' => $this->userMythModel->onlyDeleted()->countAllResults()
                ];

                // Cache for 5 minutes
                $this->cache->save(self::CACHE_STATS_KEY, $stats, self::CACHE_TTL);
            }

            return $this->response->setJSON($stats);
        } catch (\Exception $e) {
            log_message('error', 'Error getting user stats: ' . $e->getMessage());

            return $this->response->setJSON([
                'total' => 0,
                'admin' => 0,
                'asesor' => 0,
                'asesi' => 0,
                'active' => 0,
                'inactive' => 0,
                'deleted' => 0
            ]);
        }
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
     */    /**
     * Create new user (AJAX) - Optimized with rate limiting and security
     */
    public function createUser()
    {
        // Security checks
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        // Rate limiting - max 5 user creations per minute per admin
        $rateLimitKey = 'user_create_' . user()->id;
        $attempts = $this->cache->get($rateLimitKey) ?? 0;

        if ($attempts >= 5) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan. Tunggu 1 menit.'
            ]);
        }

        try {
            // Enhanced validation rules
            $rules = [
                'username' => [
                    'label' => 'Username',
                    'rules' => 'required|min_length[3]|max_length[30]|alpha_numeric_punct|is_unique[users.username]'
                ],
                'email' => [
                    'label' => 'Email',
                    'rules' => 'required|valid_email|max_length[255]|is_unique[users.email]'
                ],
                'password' => [
                    'label' => 'Password',
                    'rules' => 'required|min_length[8]|max_length[255]'
                ],
                'nama_lengkap' => [
                    'label' => 'Nama Lengkap',
                    'rules' => 'required|min_length[3]|max_length[100]|alpha_numeric_space'
                ],
                'role' => [
                    'label' => 'Role',
                    'rules' => 'required|in_list[Admin,Asesor,Asesi]'
                ]
            ];

            if (!$this->validateData($this->request->getPost(), $rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak valid',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            // Start database transaction
            $this->db->transStart();

            // Sanitize and prepare user data
            $userData = [
                'username' => trim($this->request->getPost('username')),
                'email' => trim(strtolower($this->request->getPost('email'))),
                'nama_lengkap' => trim($this->request->getPost('nama_lengkap')),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_ARGON2ID),
                'active' => 1,
                'force_pass_reset' => 1, // Force password reset on first login
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userMythModel->insert($userData);

            if ($userId) {
                // Assign role with validation
                $roleName = $this->request->getPost('role');
                $group = $this->groupModel->where('name', $roleName)->first();

                if ($group) {
                    $this->groupUserModel->insert([
                        'user_id' => $userId,
                        'group_id' => $group->id
                    ]);
                }

                // Complete transaction
                $this->db->transComplete();

                if ($this->db->transStatus() === false) {
                    throw new \Exception('Database transaction failed');
                }

                // Clear relevant caches
                $this->clearUserCaches();

                // Log successful creation
                log_message('info', "User created: {$userData['username']} (ID: {$userId}) by admin: " . user()->username);

                // Increment rate limiting counter
                $this->cache->save($rateLimitKey, $attempts + 1, 60);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil ditambahkan',
                    'data' => ['id' => $userId]
                ]);
            } else {
                throw new \Exception('Failed to create user');
            }
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->transRollback();

            log_message('error', 'User creation error: ' . $e->getMessage() . ' by admin: ' . user()->username);

            // Increment rate limiting counter even on error to prevent abuse
            $this->cache->save($rateLimitKey, $attempts + 1, 60);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clear user-related caches
     */
    private function clearUserCaches(): void
    {
        $this->cache->delete(self::CACHE_STATS_KEY);
        $this->cache->delete('total_users_count');
        $this->cache->delete('user_count_group_Admin');
        $this->cache->delete('user_count_group_Asesor');
        $this->cache->delete('user_count_group_Asesi');

        // Clear all role-related caches
        $this->cache->delete(self::CACHE_ROLES_KEY);
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
     */    /**
     * Delete user (AJAX) - Optimized soft delete with security checks
     */
    public function deleteUser($id)
    {
        // Security checks
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        // Validate ID
        if (!is_numeric($id) || $id <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID user tidak valid'
            ]);
        }

        try {
            // Rate limiting for delete operations
            $rateLimitKey = 'user_delete_' . user()->id;
            $attempts = $this->cache->get($rateLimitKey) ?? 0;

            if ($attempts >= 10) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terlalu banyak percobaan hapus. Tunggu 1 menit.'
                ]);
            }

            $user = $this->userMythModel->find($id);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            // Security check - Don't allow deleting own account
            $currentUser = user();
            if ($id == $currentUser->id) {
                log_message('warning', "Admin {$currentUser->username} attempted to delete own account");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus akun sendiri'
                ]);
            }

            // Additional security - Don't allow deleting super admin if not super admin
            $userRoles = $this->getUserRoles($id);
            if (in_array('Super Admin', $userRoles) && !in_groups(['Super Admin'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak memiliki izin untuk menghapus Super Admin'
                ]);
            }

            // Start transaction for data integrity
            $this->db->transStart();

            // Soft delete user (sets deleted_at timestamp)
            $deleted = $this->userMythModel->delete($id);

            if ($deleted) {
                // Log the action
                log_message('info', "User soft deleted: {$user->username} (ID: {$id}) by admin: {$currentUser->username}");

                // Clear caches
                $this->clearUserCaches();

                $this->db->transComplete();

                if ($this->db->transStatus() === false) {
                    throw new \Exception('Database transaction failed');
                }

                // Increment rate limiting counter
                $this->cache->save($rateLimitKey, $attempts + 1, 60);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil dipindahkan ke arsip'
                ]);
            } else {
                throw new \Exception('Gagal melakukan soft delete');
            }
        } catch (\Exception $e) {
            $this->db->transRollback();

            log_message('error', 'User deletion error: ' . $e->getMessage() . ' by admin: ' . user()->username);

            // Increment rate limiting counter even on error
            $this->cache->save($rateLimitKey, $attempts + 1, 60);

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user roles for security checks
     */
    private function getUserRoles(int $userId): array
    {
        $result = $this->userMythModel
            ->select('auth_groups.name')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
            ->where('users.id', $userId)
            ->get()
            ->getResultArray();

        return array_column($result, 'name');
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
     * Optimized helper method to get user count by group with caching
     */
    private function getUserCountByGroupOptimized(string $groupName): int
    {
        $cacheKey = "user_count_group_{$groupName}";
        $count = $this->cache->get($cacheKey);

        if ($count === null) {
            $count = $this->userMythModel
                ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
                ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
                ->where('auth_groups.name', $groupName)
                ->countAllResults();

            $this->cache->save($cacheKey, $count, 300); // Cache for 5 minutes
        }

        return $count;
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
     */    /**
     * Get all available groups/roles (AJAX) - Optimized with caching
     */
    public function getAvailableRoles()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            // Try to get from cache first
            $roles = $this->cache->get(self::CACHE_ROLES_KEY);

            if ($roles === null) {
                $groups = $this->groupModel->select('id, name, description')
                    ->orderBy('name', 'ASC')
                    ->findAll();

                $roles = [];
                foreach ($groups as $group) {
                    // Only include active roles for security
                    if (in_array($group->name, ['Admin', 'Asesor', 'Asesi'])) {
                        $roles[] = [
                            'id' => (int)$group->id,
                            'name' => esc($group->name),
                            'description' => esc($group->description ?? ''),
                            'icon' => $this->getRoleIcon($group->name)
                        ];
                    }
                }

                // Cache for 15 minutes
                $this->cache->save(self::CACHE_ROLES_KEY, $roles, 900);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $roles,
                'count' => count($roles)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get available roles error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil daftar roles',
                'data' => []
            ]);
        }
    }

    /**
     * Get icon for role
     */
    private function getRoleIcon(string $roleName): string
    {
        return match ($roleName) {
            'Admin' => 'fas fa-user-shield',
            'Asesor' => 'fas fa-user-tie',
            'Asesi' => 'fas fa-user-graduate',
            default => 'fas fa-user'
        };
    }
}
