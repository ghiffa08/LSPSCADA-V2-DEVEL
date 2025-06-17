<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;
use CodeIgniter\HTTP\ResponseInterface;

class KelolaUsersController extends BaseController
{
    protected $userModel;
    protected $groupModel;
    protected $groupUserModel;

    public function __construct()
    {
        helper('auth');

        $this->userModel = new UserMythModel();
        $this->groupModel = new GroupModel();
        $this->groupUserModel = new GroupUserModel();

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
     */
    public function getUsersData()
    {
        $request = service('request');

        // DataTables parameters
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?: 0;
        $length = $request->getPost('length') ?: 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $roleFilter = $request->getPost('role_filter') ?? '';

        // Base query
        $builder = $this->userModel->select('users.*, auth_groups.name as role, auth_groups.id as group_id')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left');

        // Apply role filter
        if (!empty($roleFilter)) {
            $builder->where('auth_groups.name', $roleFilter);
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
        $totalRecords = $this->userModel->countAll();
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
                : '<span class="badge badge-danger">Nonaktif</span>';

            // Role badge with appropriate colors
            switch ($user->role) {
                case 'Admin':
                    $roleBadge = '<span class="badge badge-primary">Admin</span>';
                    break;
                case 'Asesor':
                    $roleBadge = '<span class="badge badge-info">Asesor</span>';
                    break;
                case 'Asesi':
                    $roleBadge = '<span class="badge badge-warning">Asesi</span>';
                    break;
                default:
                    $roleBadge = '<span class="badge badge-secondary">No Role</span>';
            }

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
                $roleBadge,
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
            'total' => $this->userModel->countAll(),
            'admin' => $this->getUserCountByGroup('Admin'),
            'asesor' => $this->getUserCountByGroup('Asesor'),
            'asesi' => $this->getUserCountByGroup('Asesi'),
            'active' => $this->userModel->where('active', 1)->countAllResults(),
            'inactive' => $this->userModel->where('active', 0)->countAllResults()
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Create new user (AJAX)
     */
    public function createUser()
    {
        $validation = service('validation');

        $rules = [
            'username' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'nama_lengkap' => 'required|min_length[3]',
            'password' => 'required|min_length[8]',
            'role' => 'required|in_list[Admin,Asesor,Asesi]'
        ];

        if (!$validation->withRequest($this->request)->setRules($rules)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Create user data
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->insert($userData);

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
            $user = $this->userModel->find($id);
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

            if ($this->userModel->update($id, ['active' => $newStatus])) {
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
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        $validation = service('validation');

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'nama_lengkap' => 'required|min_length[3]',
            'role' => 'required|in_list[Admin,Asesor,Asesi]'
        ];

        // Add password validation if password is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[8]';
        }

        if (!$validation->withRequest($this->request)->setRules($rules)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Update user data
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password if provided
            if ($this->request->getPost('password')) {
                $userData['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($id, $userData)) {
                // Update role
                $roleName = $this->request->getPost('role');
                $group = $this->groupModel->where('name', $roleName)->first();

                if ($group) {
                    // Delete existing role assignments
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
     * Delete user (AJAX)
     */
    public function deleteUser($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        // Don't allow deleting own account
        if ($id == user()->id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak dapat menghapus akun sendiri']);
        }

        // Delete user and their group assignments
        $this->groupUserModel->where('user_id', $id)->delete();
        $this->userModel->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User berhasil dihapus'
        ]);
    }

    /**
     * Helper method to get user count by group
     */
    private function getUserCountByGroup($groupName)
    {
        return $this->userModel->select('users.id')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id')
            ->where('auth_groups.name', $groupName)
            ->countAllResults();
    }
}
