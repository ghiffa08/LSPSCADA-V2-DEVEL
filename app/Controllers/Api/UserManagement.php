<?php

namespace App\Controllers\Api;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use App\Models\UserManagementModel;

class UserManagement extends DataTableController
{
    public function __construct()
    {
        parent::__construct();

        $this->model = new UserManagementModel();

        // Define custom column mapping for ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'users.username',
            2 => 'users.nama_lengkap',
            3 => 'users.email',
            4 => 'roles', // GROUP_CONCAT field
            5 => 'users.active',
            6 => 'users.created_at',
            7 => null // No ordering for action column
        ];
    }
    /**
     * Get users by role
     */
    public function getUsersByRole($role = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $users = $this->model->getUsersByRole($role);

            return $this->response->setJSON([
                'status' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching users: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $stats = $this->model->getUserStatistics();

            return $this->response->setJSON([
                'status' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user statistics including deleted users
     */
    public function getUserStatisticsWithDeleted(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $stats = $this->model->getUserStatisticsWithDeleted();

            return $this->response->setJSON([
                'status' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user details by ID
     */
    public function getUserById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        // Get ID from URL parameter or query parameter
        if (!$id) {
            $id = $this->request->getGet('id');
        }

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            $user = $this->model->getUserById($id);
            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'User not found'
                ]);
            }
            return $this->response->setJSON([
                'status' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get user by ID (POST method alias)
     */
    public function getUserByIdPost(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            $user = $this->model->getUserById($id);

            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'User not found'
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update user status (activate/deactivate)
     */
    public function updateUserStatus(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        if (!$id || !isset($status)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID and status are required'
            ]);
        }

        try {
            $updated = $this->model->update($id, [
                'active' => (int)$status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User status updated successfully'
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Failed to update user status'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error updating user status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update user profile
     */
    public function updateUserProfile(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $data = $this->request->getPost();
        $id = $data['id'] ?? null;

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            // Prepare data for update
            $updateData = [
                'nama_lengkap' => $data['nama_lengkap'] ?? null,
                'email' => $data['email'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Remove empty values
            $updateData = array_filter($updateData, function ($value) {
                return $value !== null && $value !== '';
            });

            $updated = $this->model->update($id, $updateData);

            if ($updated) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User profile updated successfully'
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Failed to update user profile'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error updating user profile: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update user profile (alias method)
     */
    public function updateProfile(): ResponseInterface
    {
        return $this->updateUserProfile();
    }

    /**
     * Update user status (alias method)
     */
    public function updateStatus(): ResponseInterface
    {
        return $this->updateUserStatus();
    }

    /**
     * Override getDataTable to handle role filter
     */
    public function getDataTable(): ResponseInterface
    {
        // Verify AJAX request
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses ditolak');
        }

        // Get standard DataTable parameters
        $draw = $this->request->getVar('draw');
        $limit = (int)$this->request->getVar('length');
        $start = (int)$this->request->getVar('start');
        $search = $this->request->getVar('search')['value'] ?? '';

        // Get role filter parameter
        $roleFilter = $this->request->getVar('role_filter');

        // Extract ordering information
        $order = $this->request->getVar('order')[0] ?? null;
        $orderColumn = null;
        $orderDir = 'asc';

        if ($order) {
            $columnIndex = $order['column'];
            $orderDir = $order['dir'];
            $orderColumn = $this->columnMap[$columnIndex] ?? null;
        }

        // Get filtered data using model's method
        $result = $this->model->getDataTableWithRoleFilter($limit, $start, $search, $orderColumn, $orderDir, $roleFilter);

        // Format and return response
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['data']
        ]);
    }
    /**
     * Soft delete user
     */
    public function softDeleteUser(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            // Don't allow deleting own account
            if ($id == user()->id) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri'
                ]);
            }

            $deleted = $this->model->softDeleteUser($id);

            if ($deleted) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User berhasil diarsipkan'
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Failed to delete user'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Restore soft deleted user
     */
    public function restoreUser(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            $restored = $this->model->restoreUser($id);

            if ($restored) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User restored successfully'
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Failed to restore user'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error restoring user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create admin user
     */
    public function createAdminUser(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $data = $this->request->getPost();

        // Validate required fields
        $required = ['username', 'email', 'nama_lengkap', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => "Field $field is required"
                ]);
            }
        }

        try {
            $result = $this->model->createAdminUser($data);

            if ($result['success']) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => $result['message'],
                    'user_id' => $result['user_id']
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors']
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Create asesor user
     */
    public function createAsesorUser(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $data = $this->request->getPost();

        // Debug log
        log_message('debug', 'Create Asesor User - Raw POST data: ' . json_encode($data));

        // Validate required fields (changed from skema_ids to skema_id)
        $required = ['username', 'email', 'nama_lengkap', 'password', 'skema_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                log_message('error', "Create Asesor User - Missing required field: {$field}");
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => "Field $field is required"
                ]);
            }
        }

        // Validate skema_id is a valid integer
        if (!filter_var($data['skema_id'], FILTER_VALIDATE_INT)) {
            log_message('error', "Create Asesor User - Invalid skema_id: " . $data['skema_id']);
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => "Invalid skema ID"
            ]);
        }

        log_message('debug', 'Create Asesor User - Processed skema_id: ' . $data['skema_id']);

        try {
            $result = $this->model->createAsesorUser($data);

            if ($result['success']) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => $result['message'],
                    'user_id' => $result['user_id']
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors']
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Create Asesor User - Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error creating asesor user: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $roles = $this->model->getAvailableRoles();

            return $this->response->setJSON([
                'status' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching roles: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Change user role
     */
    public function changeUserRole(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $userId = $this->request->getPost('user_id');
        $newRole = $this->request->getPost('new_role');

        if (!$userId || !$newRole) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID and new role are required'
            ]);
        }

        try {
            $changed = $this->model->changeUserRole($userId, $newRole);

            if ($changed) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User role changed successfully'
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Failed to change user role'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error changing user role: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validation helper for user data
     */
    protected function validateUserData(array $data, ?int $userId = null): array
    {
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => $userId
                    ? "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$userId}]"
                    : 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'alpha_numeric_space' => '{field} hanya boleh berisi huruf, angka, dan spasi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'max_length' => '{field} maksimal {param} karakter.',
                    'is_unique' => '{field} sudah digunakan.'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => $userId
                    ? "required|valid_email|is_unique[users.email,id,{$userId}]"
                    : 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'valid_email' => '{field} harus berformat valid.',
                    'is_unique' => '{field} sudah digunakan.'
                ]
            ],
            'nama_lengkap' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'max_length' => '{field} maksimal {param} karakter.'
                ]
            ]
        ];

        if (isset($data['password']) && !empty($data['password'])) {
            $rules['password'] = [
                'label' => 'Password',
                'rules' => 'required|min_length[8]|strong_password',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'strong_password' => '{field} harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus.'
                ]
            ];

            $rules['pass_confirm'] = [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'matches' => '{field} harus sama dengan password.'
                ]
            ];
        }

        return $rules;
    }

    /**
     * Log user activity
     */
    protected function logUserActivity(string $action, int $userId, array $details = []): void
    {
        $currentUser = service('AuthenticationService')->getCurrentUser();
        $adminId = $currentUser ? $currentUser->id : 'system';

        $logData = [
            'action' => $action,
            'target_user_id' => $userId,
            'admin_id' => $adminId,
            'details' => json_encode($details),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        log_message('info', "User Management: {$action} - User ID: {$userId} by Admin ID: {$adminId}");
    }

    /**
     * Enhanced error response
     */
    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400): ResponseInterface
    {
        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('c')
        ]);
    }

    /**
     * Enhanced success response
     */
    protected function successResponse(string $message, array $data = []): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }

    /**
     * Update user with role management
     */    public function updateUser(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $data = $this->request->getPost();
        $id = $data['id'] ?? null;
        $role = $data['role'] ?? null;

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            // Prepare user data for update
            $updateData = [
                'nama_lengkap' => $data['nama_lengkap'] ?? null,
                'email' => $data['email'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Remove empty values
            $updateData = array_filter($updateData, function ($value) {
                return $value !== null && $value !== '';
            });

            // Update user profile
            $userUpdated = $this->model->update($id, $updateData);            // Handle role update if provided
            $roleUpdated = true;
            $roleMessage = '';

            if ($role && $userUpdated) {
                try {
                    // Normalize role name (capitalize first letter)
                    $normalizedRole = ucfirst(strtolower($role));

                    // Load auth models
                    $authGroupsModel = model('GroupModel');
                    $db = \Config\Database::connect();

                    // Remove user from all existing groups
                    $db->table('auth_groups_users')->where('user_id', $id)->delete();                    // Add user to new role group
                    $groupData = $authGroupsModel->where('name', $normalizedRole)->first();
                    if (!$groupData) {
                        // Try original case
                        $groupData = $authGroupsModel->where('name', $role)->first();
                    }
                    if (!$groupData) {
                        // Try lowercase
                        $groupData = $authGroupsModel->where('LOWER(name)', strtolower($role))->first();
                    }

                    if ($groupData) {
                        $db->table('auth_groups_users')->insert([
                            'group_id' => $groupData->id,
                            'user_id' => $id
                        ]);
                        $roleMessage = ' dengan role ' . $normalizedRole;
                    } else {
                        $roleUpdated = false;
                        $roleMessage = ' (Role tidak valid atau tidak ditemukan)';
                    }
                } catch (\Exception $roleError) {
                    $roleUpdated = false;
                    $roleMessage = ' (Gagal mengubah role)';
                }
            }

            if ($userUpdated && $roleUpdated) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User berhasil diperbarui' . $roleMessage
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Gagal memperbarui user' . $roleMessage
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Get asesor details by user ID
     */
    public function getAsesorByUserId(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $userId = $this->request->getGet('user_id');

        if (!$userId) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'User ID is required'
            ]);
        }

        try {
            $asesorModel = new \App\Models\AsesorModel();
            $asesor = $asesorModel->getByUserIdWithUser($userId);

            if ($asesor) {
                return $this->response->setJSON([
                    'status' => true,
                    'data' => $asesor
                ]);
            } else {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'Asesor not found'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching asesor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all asesor with user data
     */
    public function getAllAsesor(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $asesorModel = new \App\Models\AsesorModel();
            $activeOnly = $this->request->getGet('active_only') === 'true';
            $asesorList = $asesorModel->getAllWithUser($activeOnly);

            return $this->response->setJSON([
                'status' => true,
                'data' => $asesorList
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching asesor list: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update asesor data
     */
    public function updateAsesor(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $data = $this->request->getPost();
        $asesorId = $data['id_asesor'] ?? null;

        if (!$asesorId) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'Asesor ID is required'
            ]);
        }

        try {
            $asesorModel = new \App\Models\AsesorModel();

            $updateData = [
                'nomor_registrasi' => $data['nomor_registrasi'] ?? null
            ];

            // Remove empty values
            $updateData = array_filter($updateData, function ($value) {
                return $value !== null && $value !== '';
            });

            $hasUpdates = !empty($updateData);
            $skemaUpdated = false;

            // Update asesor basic data if we have any
            if ($hasUpdates) {
                $updated = $asesorModel->update($asesorId, $updateData);
                if (!$updated) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => false,
                        'message' => 'Failed to update asesor data',
                        'errors' => $asesorModel->errors()
                    ]);
                }
            }

            // Handle skema assignment (single skema)
            if (isset($data['skema_id'])) {
                $skemaId = $data['skema_id'];

                // Validate skema_id
                if (!empty($skemaId) && !filter_var($skemaId, FILTER_VALIDATE_INT)) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => false,
                        'message' => 'Invalid skema ID'
                    ]);
                }

                $skemaUpdated = $asesorModel->updateAsesorSkema($asesorId, $skemaId);
                if (!$skemaUpdated) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => false,
                        'message' => 'Failed to update asesor skema assignment'
                    ]);
                }
            }

            if (!$hasUpdates && !$skemaUpdated) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'No data to update'
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Asesor data updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error updating asesor: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Get asesor statistics
     */
    public function getAsesorStatistics(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $asesorModel = new \App\Models\AsesorModel();
            $statistics = [
                'total_asesor' => count($asesorModel->getAllWithUser()),
                'active_asesor' => count($asesorModel->getAllWithUser(true)),
                'by_bidang_kompetensi' => $asesorModel->getCountByBidangKompetensi()
            ];

            return $this->response->setJSON([
                'status' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching asesor statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get active skemas for asesor form
     */
    public function getActiveSkemas(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        try {
            $skemaModel = new \App\Models\SkemaModel();
            $skemas = $skemaModel->getActiveSchemes();

            return $this->response->setJSON([
                'status' => true,
                'data' => $skemas
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Error fetching skemas: ' . $e->getMessage()
            ]);
        }
    }
}
