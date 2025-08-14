<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserManagementController extends BaseController
{
    protected $userModel;
    protected $groupModel;
    protected $groupUserModel;
    protected $config;

    public function __construct()
    {
        helper('auth');

        $this->userModel = new UserMythModel();
        $this->groupModel = new GroupModel();
        $this->groupUserModel = new GroupUserModel();
        $this->config = config('Auth');

        // Check if user is admin
        if (!in_groups(['Admin'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }
    }

    /**
     * Display user management dashboard
     */
    public function index()
    {
        $users = $this->userModel->select('users.*, auth_groups.name as role, auth_groups.id as group_id')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        $groups = $this->groupModel->findAll();

        $data = [
            'siteTitle' => 'Kelola Users',
            'users' => $users,
            'groups' => $groups,
            'totalUsers' => $this->userModel->countAll(),
            'totalAdmin' => $this->getUserCountByGroup('Admin'),
            'totalAsesor' => $this->getUserCountByGroup('Asesor'),
            'totalAsesi' => $this->getUserCountByGroup('Asesi'),
        ];

        return view('admin/user_management/index', $data);
    }

    /**
     * Create new user
     */
    public function create()
    {
        $groups = $this->groupModel->findAll();

        $data = [
            'siteTitle' => 'Tambah User Baru',
            'groups' => $groups
        ];

        return view('admin/user_management/create', $data);
    }

    /**
     * Store new user
     */
    public function store()
    {
        $users = model(UserModel::class);

        // Validation rules
        $rules = [
            'username' => [
                'label' => "Username",
                'rules' =>  'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'alpha_numeric_space' => 'Kolom {field} hanya boleh berisi huruf, angka, atau spasi.',
                    'min_length' => 'Kolom {field} harus memiliki panjang minimal {param} karakter.',
                    'max_length' => 'Kolom {field} harus memiliki panjang maksimal {param} karakter.',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih username lain.',
                ],
            ],
            'email' => [
                'label' => "Email",
                'rules' =>  'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_email' => 'Format {field} harus valid',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih email lain.',
                ],
            ],
            'nama_lengkap' => [
                'label' => "Nama Lengkap",
                'rules' =>  'required|min_length[3]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'min_length' => 'Kolom {field} minimal {param} karakter.',
                ],
            ],
            'password' => [
                'label'  => "Password",
                'rules'  => 'required|strong_password',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'strong_password' => 'Password harus mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu karakter khusus.',
                ],
            ],
            'pass_confirm' => [
                'label'  => "Konfirmasi Password",
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'matches' => 'Konfirmasi password harus sama dengan password yang dimasukkan sebelumnya.'
                ],
            ],
            'group_id' => [
                'label' => 'Role',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                    'integer' => 'Kolom {field} tidak valid.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get the selected group
        $groupId = $this->request->getPost('group_id');
        $group = $this->groupModel->find($groupId);

        if (!$group) {
            return redirect()->back()->withInput()->with('error', 'Role tidak valid.');
        }

        // Save the user
        $allowedPostFields = array_merge(['password'], $this->config->validFields, $this->config->personalFields);
        $user = new User($this->request->getPost($allowedPostFields));

        // Set activation based on config
        $this->config->requireActivation === null ? $user->activate() : $user->generateActivateHash();

        // Assign to group
        $users = $users->withGroup($group->name);

        if (!$users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        // Handle activation email
        if ($this->config->requireActivation !== null) {
            $activator = service('activator');
            $sent = $activator->send($user);

            if (!$sent) {
                return redirect()->back()->withInput()->with('error', $activator->error() ?? lang('Auth.unknownError'));
            }

            session()->setFlashdata('success', 'User "' . $this->request->getPost('nama_lengkap') . '" berhasil ditambahkan dengan role ' . $group->name . '. Silahkan cek email untuk aktivasi akun!');
        } else {
            session()->setFlashdata('success', 'User "' . $this->request->getPost('nama_lengkap') . '" berhasil ditambahkan dengan role ' . $group->name . '!');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Edit user
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        $userGroup = $this->groupUserModel->where('user_id', $id)->first();
        $groups = $this->groupModel->findAll();

        $data = [
            'siteTitle' => 'Edit User',
            'user' => $user,
            'userGroup' => $userGroup,
            'groups' => $groups
        ];

        return view('admin/user_management/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'username' => [
                'label' => "Username",
                'rules' =>  "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'alpha_numeric_space' => 'Kolom {field} hanya boleh berisi huruf, angka, atau spasi.',
                    'min_length' => 'Kolom {field} harus memiliki panjang minimal {param} karakter.',
                    'max_length' => 'Kolom {field} harus memiliki panjang maksimal {param} karakter.',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih username lain.',
                ],
            ],
            'email' => [
                'label' => "Email",
                'rules' =>  "required|valid_email|is_unique[users.email,id,{$id}]",
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_email' => 'Format {field} harus valid',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih email lain.',
                ],
            ],
            'nama_lengkap' => [
                'label' => "Nama Lengkap",
                'rules' =>  'required|min_length[3]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'min_length' => 'Kolom {field} minimal {param} karakter.',
                ],
            ],
            'group_id' => [
                'label' => 'Role',
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Kolom {field} harus dipilih.',
                    'integer' => 'Kolom {field} tidak valid.'
                ]
            ]
        ];

        // Add password validation if password is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = [
                'label'  => "Password",
                'rules'  => 'required|strong_password',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'strong_password' => 'Password harus mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu karakter khusus.',
                ],
            ];
            $rules['pass_confirm'] = [
                'label'  => "Konfirmasi Password",
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'matches' => 'Konfirmasi password harus sama dengan password yang dimasukkan sebelumnya.'
                ],
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update user data
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
        ];

        // Update password if provided
        if ($this->request->getPost('password')) {
            $userData['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $userData)) {
            // Update group assignment
            $groupId = $this->request->getPost('group_id');
            $group = $this->groupModel->find($groupId);

            if ($group) {
                // Delete existing group assignments
                $this->groupUserModel->where('user_id', $id)->delete();

                // Add new group assignment
                $this->groupUserModel->insert([
                    'user_id' => $id,
                    'group_id' => $groupId
                ]);

                session()->setFlashdata('success', 'User "' . $this->request->getPost('nama_lengkap') . '" berhasil diupdate dengan role ' . $group->name . '!');
            } else {
                session()->setFlashdata('success', 'User "' . $this->request->getPost('nama_lengkap') . '" berhasil diupdate!');
            }
        } else {
            session()->setFlashdata('error', 'Gagal mengupdate user.');
        }

        return redirect()->to('/admin/users');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', 'User tidak ditemukan.');
            return redirect()->back();
        }

        // Don't allow deleting own account
        if ($id == user()->id) {
            session()->setFlashdata('error', 'Tidak dapat menghapus akun sendiri.');
            return redirect()->back();
        }

        // Delete user and their group assignments
        $this->groupUserModel->where('user_id', $id)->delete();
        $this->userModel->delete($id);

        session()->setFlashdata('success', 'User berhasil dihapus!');
        return redirect()->to('/admin/users');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        // Don't allow deactivating own account
        if ($id == user()->id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak dapat menonaktifkan akun sendiri']);
        }

        $newStatus = $user->active ? 0 : 1;
        $this->userModel->update($id, ['active' => $newStatus]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Status user berhasil diubah',
            'active' => $newStatus
        ]);
    }

    /**
     * Get user statistics
     */
    public function stats()
    {
        $stats = [
            'total_users' => $this->userModel->countAll(),
            'total_admin' => $this->getUserCountByGroup('Admin'),
            'total_asesor' => $this->getUserCountByGroup('Asesor'),
            'total_asesi' => $this->getUserCountByGroup('Asesi'),
            'active_users' => $this->userModel->where('active', 1)->countAllResults(),
            'inactive_users' => $this->userModel->where('active', 0)->countAllResults(),
        ];

        return $this->response->setJSON($stats);
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
