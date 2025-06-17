<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMythModel;
use App\Services\Authentication\AuthenticationService;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    protected $userModel;
    protected $authService;
    protected $validation;

    public function __construct()
    {
        helper(['auth', 'form']);

        $this->userModel = new UserMythModel();
        $this->authService = service('AuthenticationService');
        $this->validation = service('validation');
    }

    /**
     * Display user profile
     */
    public function index(): string
    {
        $currentUser = $this->authService->getCurrentUser();

        if (!$currentUser) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $data = [
            'siteTitle' => 'Profile',
            'user' => $currentUser,
            'pageTitle' => 'Profile Saya',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Profile', 'url' => '', 'active' => true]
            ]
        ];

        return view('profile/index', $data);
    }

    /**
     * Update user profile
     */
    public function update(): ResponseInterface
    {
        $currentUser = $this->authService->getCurrentUser();

        if (!$currentUser) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        $userId = $currentUser->id;

        // Validation rules
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$userId}]",
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
                'rules' => "required|valid_email|is_unique[users.email,id,{$userId}]",
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

        if (!$this->validation->withRequest($this->request)->setRules($rules)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $this->validation->getErrors()
            ]);
        }

        try {
            $updateData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->userModel->update($userId, $updateData)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Profile berhasil diupdate!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate profile'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Change password
     */
    public function changePassword(): ResponseInterface
    {
        $currentUser = $this->authService->getCurrentUser();

        if (!$currentUser) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        // Validation rules
        $rules = [
            'current_password' => [
                'label' => 'Password Saat Ini',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} harus diisi.'
                ]
            ],
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[8]|strong_password',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'min_length' => '{field} minimal {param} karakter.',
                    'strong_password' => '{field} harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus.'
                ]
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[new_password]',
                'errors' => [
                    'required' => '{field} harus diisi.',
                    'matches' => '{field} harus sama dengan password baru.'
                ]
            ]
        ];

        if (!$this->validation->withRequest($this->request)->setRules($rules)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $this->validation->getErrors()
            ]);
        }

        try {
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!password_verify($currentPassword, $currentUser->password_hash)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password saat ini tidak benar'
                ]);
            }

            // Update password
            $updateData = [
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->userModel->update($currentUser->id, $updateData)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Password berhasil diubah!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengubah password'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Password change error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(): ResponseInterface
    {
        $currentUser = $this->authService->getCurrentUser();

        if (!$currentUser) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        $validationRule = [
            'avatar' => [
                'label' => 'Avatar',
                'rules' => 'uploaded[avatar]|max_size[avatar,2048]|is_image[avatar]|mime_in[avatar,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Pilih file {field}.',
                    'max_size' => 'Ukuran {field} maksimal 2MB.',
                    'is_image' => '{field} harus berupa gambar.',
                    'mime_in' => '{field} harus berformat JPG, JPEG, atau PNG.'
                ]
            ]
        ];

        if (!$this->validation->withRequest($this->request)->setRules($validationRule)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $this->validation->getErrors()
            ]);
        }

        try {
            $avatarFile = $this->request->getFile('avatar');

            if ($avatarFile->isValid() && !$avatarFile->hasMoved()) {
                $newName = $avatarFile->getRandomName();
                $uploadPath = WRITEPATH . 'uploads/avatars/';

                // Create directory if not exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                if ($avatarFile->move($uploadPath, $newName)) {
                    // Delete old avatar if exists
                    if ($currentUser->avatar && file_exists($uploadPath . $currentUser->avatar)) {
                        unlink($uploadPath . $currentUser->avatar);
                    }

                    // Update user avatar
                    $updateData = [
                        'avatar' => $newName,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($this->userModel->update($currentUser->id, $updateData)) {
                        return $this->response->setJSON([
                            'status' => 'success',
                            'message' => 'Avatar berhasil diupload!',
                            'avatar_url' => base_url('writable/uploads/avatars/' . $newName)
                        ]);
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupload avatar'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Avatar upload error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ]);
        }
    }
}
