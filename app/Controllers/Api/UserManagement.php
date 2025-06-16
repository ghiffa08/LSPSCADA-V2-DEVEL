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
            $deleted = $this->model->softDeleteUser($id);

            if ($deleted) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'User deleted successfully'
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
}
