<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;

class AdminController extends BaseController
{
    protected $auth;

    /**
     * @var AuthConfig
     */
    protected $config;

    /**
     * @var Session
     */
    protected $session;

    protected $group_users;
    protected $usermodel;

    public function __construct()
    {
        // Most services in this controller require
        // the session to be started - so fire it up!
        $this->session = service('session');

        $this->config = config('Auth');
        $this->auth   = service('authentication');

        $this->group_users = new \App\Models\GroupUserModel();
        $this->usermodel = new \App\Models\UserMythModel();
    }

    public function index()
    {

        $data = [
            'siteTitle' => "Kelola Admin",
            'listAdmin' => $this->group_users->getAdmin(),
        ];

        return view('dashboard/kelola_admin', $data);
    }
    public function store()
    {
        $users = model(UserModel::class);

        // Validate basics first since some password rules rely on these fields
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
            'email'    =>  [
                'label' => "Email",
                'rules' =>  'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_email' => 'Format {field} harus valid',
                    'is_unique' => 'Kolom {field} sudah terdaftar. Silakan pilih email lain.',
                ],
            ],
            'nama_lengkap'  => [
                'label' => "Nama Lengkap",
                'rules' =>  'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addAdminModal');

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validate passwords since they can only be validated properly here
        $rules = [
            'password'   => [
                'label'  => "Password",
                'rules'  => 'required|strong_password',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'strong_password' => 'Password harus mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu karakter khusus.',
                ],
            ],
            'pass_confirm' => [
                'label'  => "Password",
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'matches' => 'Konfirmasi password harus sama dengan password yang dimasukkan sebelumnya.'
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'addAdminModal');

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Save the user
        $allowedPostFields = array_merge(['password'], $this->config->validFields, $this->config->personalFields);
        $user              = new User($this->request->getPost($allowedPostFields));

        $this->config->requireActivation === null ? $user->activate() : $user->generateActivateHash();

        $users = $users->withGroup('Admin');

        if (!$users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        if ($this->config->requireActivation !== null) {
            $activator = service('activator');
            $sent      = $activator->send($user);

            if (!$sent) {
                return redirect()->back()->withInput()->with('error', $activator->error() ?? lang('Auth.unknownError'));
            }

            // Success!
            session()->setFlashdata('pesan', 'Admin "' . $this->request->getVar('nama_lengkap') . '" Berhasil ditambahkan, Silahkan Cek Email untuk validasi akun!');
            return redirect()->to('/admin');
        }

        // Success!
        session()->setFlashdata('pesan', 'Admin "' . $this->request->getVar('nama_lengkap') . '" Berhasil ditambahkan, Silahkan Cek Email untuk validasi akun!');
        return redirect()->to('/admin');
    }
    public function update()
    {
        $rules = [
            'edit_email' => [
                'label' => "Email",
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                    'valid_email' => 'Format {field} harus valid',
                ],
            ],
            'edit_username' => [
                'label' => "Username",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
            'edit_nama_lengkap' => [
                'label' => "Nama Lengkap",
                'rules' => 'required',
                'errors' => [
                    'required' => 'Kolom {field} harus diisi.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('warning', 'Periksa kembali, terdapat beberapa kesalahan yang perlu diperbaiki.');
            session()->setFlashdata('modal_id', 'editAdminModal-' . $this->request->getVar('edit_id'));

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'email' => $this->request->getVar('edit_email'),
            'username' => $this->request->getVar('edit_username'),
            'nama_lengkap' => $this->request->getVar('edit_nama_lengkap'),
        ];

        // Handle password update if provided
        if ($this->request->getVar('edit_password')) {
            $data['password_hash'] = password_hash($this->request->getVar('edit_password'), PASSWORD_DEFAULT);
        }

        // Lakukan simpan user dengan data di atas
        $this->usermodel->update($this->request->getVar('edit_id'), $data);

        session()->setFlashdata('pesan', 'Admin "' . $this->request->getVar('edit_nama_lengkap') . '" berhasil diupdate.');
        return redirect()->to('/admin');
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->usermodel->deleteUser($id);
        session()->setFlashdata('pesan', 'Admin berhasil dihapus!');
        return redirect()->to('/admin');
    }

    public function dashboard()
    {
        // Dashboard utama untuk admin
        $userEntity = user();
        if (!($userEntity instanceof \App\Entities\User)) {
            $userEntity = new \App\Entities\User((array)$userEntity);
        }
        if (!method_exists($userEntity, 'isAdmin') || !$userEntity->isAdmin()) {
            return redirect()->to(site_url('/dashboard'));
        }

        $data = [
            'siteTitle' => 'Dashboard Admin',
            'user' => $userEntity,
            'totalUsers' => $this->usermodel->countAll(),
        ];
        return view('admin/dashboard', $data);
    }
}
