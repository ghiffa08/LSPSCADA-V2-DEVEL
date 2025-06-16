<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserManagementModel;

class DeletedUsersController extends BaseController
{
    protected $userManagementModel;

    public function __construct()
    {
        $this->userManagementModel = new UserManagementModel();
    }
    /**
     * Display archived users page
     */
    public function index()
    {
        return view('admin/deleted_users', [
            'siteTitle' => 'Arsip Pengguna'
        ]);
    }

    /**
     * Get deleted users data for DataTable
     */
    public function getDataTable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $requestData = $this->request->getPost();

            $limit = intval($requestData['length'] ?? 10);
            $start = intval($requestData['start'] ?? 0);
            $search = $requestData['search']['value'] ?? '';

            // Get column for ordering
            $orderColumnIndex = intval($requestData['order'][0]['column'] ?? 0);
            $orderDir = $requestData['order'][0]['dir'] ?? 'asc';

            $columns = [
                0 => null, // Row number
                1 => 'users.username',
                2 => 'users.nama_lengkap',
                3 => 'users.email',
                4 => 'roles',
                5 => 'users.deleted_at'
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'users.deleted_at';

            // Get deleted users data
            $builder = $this->userManagementModel->onlyDeleted()
                ->select('users.id, users.username, users.nama_lengkap, users.email, 
                          users.active, users.created_at, users.deleted_at,
                          GROUP_CONCAT(auth_groups.name SEPARATOR ", ") as roles')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
                ->groupBy('users.id');

            // Count total records
            $total = $builder->countAllResults(false);

            // Apply search
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('users.username', $search)
                    ->orLike('users.nama_lengkap', $search)
                    ->orLike('users.email', $search)
                    ->groupEnd();
            }

            // Count filtered records
            $filtered = $builder->countAllResults(false);

            // Apply ordering and pagination
            if ($orderColumn) {
                $builder->orderBy($orderColumn, $orderDir);
            } else {
                $builder->orderBy('users.deleted_at', 'DESC');
            }

            $data = $builder->limit($limit, $start)->get()->getResultArray();

            // Transform data for display
            foreach ($data as &$row) {
                $row['roles'] = $row['roles'] ?: 'No Role';
            }

            return $this->response->setJSON([
                'draw' => intval($requestData['draw'] ?? 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Error fetching data: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Restore deleted user
     */
    public function restore()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $userId = $this->request->getPost('id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User ID tidak valid'
            ]);
        }

        try {
            $result = $this->userManagementModel->restoreUser($userId);

            return $this->response->setJSON([
                'status' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error restoring user: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Permanently delete user
     */
    public function permanentDelete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $userId = $this->request->getPost('id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User ID tidak valid'
            ]);
        }

        try {
            $result = $this->userManagementModel->permanentDelete($userId);

            return $this->response->setJSON([
                'status' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error permanently deleting user: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Bulk restore multiple deleted users
     */
    public function bulkRestore()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $userIds = $this->request->getPost('ids');

        if (!$userIds || !is_array($userIds) || empty($userIds)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Tidak ada pengguna yang dipilih'
            ]);
        }

        try {
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($userIds as $userId) {
                if (!empty($userId)) {
                    $result = $this->userManagementModel->restoreUser($userId);
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failCount++;
                        $errors[] = "User ID {$userId}: " . $result['message'];
                    }
                }
            }

            $message = "";
            if ($successCount > 0) {
                $message .= "Berhasil memulihkan {$successCount} pengguna. ";
            }
            if ($failCount > 0) {
                $message .= "Gagal memulihkan {$failCount} pengguna.";
                if (!empty($errors)) {
                    $message .= " Detail: " . implode('; ', array_slice($errors, 0, 3));
                    if (count($errors) > 3) {
                        $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                    }
                }
            }

            return $this->response->setJSON([
                'status' => $successCount > 0,
                'message' => $message ?: 'Tidak ada pengguna yang berhasil dipulihkan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error bulk restoring users: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Bulk permanent delete multiple users
     */
    public function bulkPermanentDelete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $userIds = $this->request->getPost('ids');

        if (!$userIds || !is_array($userIds) || empty($userIds)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Tidak ada pengguna yang dipilih'
            ]);
        }

        try {
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($userIds as $userId) {
                if (!empty($userId)) {
                    $result = $this->userManagementModel->permanentDelete($userId);
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failCount++;
                        $errors[] = "User ID {$userId}: " . $result['message'];
                    }
                }
            }

            $message = "";
            if ($successCount > 0) {
                $message .= "Berhasil menghapus permanen {$successCount} pengguna. ";
            }
            if ($failCount > 0) {
                $message .= "Gagal menghapus permanen {$failCount} pengguna.";
                if (!empty($errors)) {
                    $message .= " Detail: " . implode('; ', array_slice($errors, 0, 3));
                    if (count($errors) > 3) {
                        $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                    }
                }
            }

            return $this->response->setJSON([
                'status' => $successCount > 0,
                'message' => $message ?: 'Tidak ada pengguna yang berhasil dihapus permanen'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error bulk deleting users: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get statistics for deleted users
     */
    public function getStatistics()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $deletedUsers = $this->userManagementModel->getDeletedUsers();

            $statistics = [
                'total_archived' => count($deletedUsers),
                'restorable' => count($deletedUsers), // All deleted users are restorable
                'needs_attention' => 0 // For future use - users deleted for a long time
            ];

            // Calculate users that might need attention (deleted more than 30 days ago)
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            foreach ($deletedUsers as $user) {
                if (isset($user['deleted_at']) && $user['deleted_at'] <= $thirtyDaysAgo) {
                    $statistics['needs_attention']++;
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error getting statistics: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get user details for viewing in modal
     */
    public function getUserDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $userId = $this->request->getGet('id');

        if (!$userId) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User ID tidak valid'
            ]);
        }

        try {
            // Get deleted user details
            $userDetails = $this->userManagementModel->getDeletedUserDetails($userId);

            if (!$userDetails) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'data' => $userDetails
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error getting user details: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
