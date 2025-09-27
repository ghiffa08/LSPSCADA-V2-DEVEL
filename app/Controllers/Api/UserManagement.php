<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use Myth\Auth\Models\UserModel;
use App\Models\AsesorModel;
use App\Models\GroupsModel;

use App\Models\AsesorInstansiModel;
use App\Models\InstansiModel;

class UserManagement extends BaseController
{
    use ResponseTrait;

    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new UserModel(); // Model dari Myth:Auth
        $this->db = \Config\Database::connect();
    }

    /**
     * Menyediakan data untuk DataTable (diperbaiki dengan metode 2-query).
     */
    public function getDataTable(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound();
        }

        $request = service('request');

        // Mengambil parameter DataTables
        $draw = intval($request->getPost('draw'));
        $start = intval($request->getPost('start'));
        $length = intval($request->getPost('length'));
        $searchValue = $this->db->escapeLikeString(trim($request->getPost('search')['value'] ?? ''));
        $roleFilter = trim($request->getPost('role_filter') ?? '');

        // Query 1: Mengambil data PENGGUNA utama
        $userQuery = $this->model
            ->select('id, username, nama_lengkap, email, active, created_at')
            ->where('deleted_at', null);

        // Menghitung total record
        $totalRecords = $userQuery->countAllResults(false);

        // Menerapkan filter role jika ada
        if (!empty($roleFilter)) {
            $userQuery->whereIn('id', function ($subQuery) use ($roleFilter) {
                $subQuery->select('user_id')->from('auth_groups_users gu')
                    ->join('auth_groups g', 'g.id = gu.group_id')
                    ->where('g.name', $roleFilter);
            });
        }

        // Menerapkan filter pencarian
        if (!empty($searchValue)) {
            $userQuery->groupStart()
                ->like('username', $searchValue)
                ->orLike('nama_lengkap', $searchValue)
                ->orLike('email', $searchValue)
                ->groupEnd();
        }

        $filteredRecords = $userQuery->countAllResults(false);

        // Mengambil data pengguna untuk halaman saat ini
        $users = $userQuery->orderBy('created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultObject();

        $userIds = array_column($users, 'id');
        $userRoles = [];

        // Query 2: Mengambil ROLE untuk pengguna yang ditampilkan saja
        if (!empty($userIds)) {
            $groupModel = new GroupsModel();
            $rolesData = $groupModel
                ->select('auth_groups_users.user_id, auth_groups.name')
                ->join('auth_groups_users', 'auth_groups_users.group_id = auth_groups.id', 'inner')
                ->whereIn('auth_groups_users.user_id', $userIds)
                ->findAll();

            // Petakan role ke setiap user ID
            foreach ($rolesData as $role) {
                if (!isset($userRoles[$role['user_id']])) {
                    $userRoles[$role['user_id']] = [];
                }
                $userRoles[$role['user_id']][] = $role['name'];
            }
        }

        // Menggabungkan data pengguna dengan data role
        $data = [];
        foreach ($users as $user) {
            $user->roles = isset($userRoles[$user->id]) ? implode(', ', $userRoles[$user->id]) : null;
            $data[] = (array)$user;
        }

        return $this->respond([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Mengambil statistik jumlah pengguna berdasarkan nama role.
     */
    public function getStatistics(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db = \Config\Database::connect();

        // Query untuk menghitung jumlah user per role dalam satu kali jalan
        $roleCountsQuery = $db->table('auth_groups_users gu')
            ->join('auth_groups g', 'g.id = gu.group_id', 'left')
            ->select('g.name, COUNT(gu.user_id) as total')
            ->groupBy('g.name')
            ->get();

        $roleCounts = [];
        foreach ($roleCountsQuery->getResultArray() as $row) {
            // Menggunakan strtolower untuk kunci yang konsisten
            $roleCounts[strtolower($row['name'])] = (int) $row['total'];
        }

        // Siapkan data statistik dengan nilai default 0
        $stats = [
            'admin'   => $roleCounts['admin'] ?? 0,
            'asesor'  => $roleCounts['asesor'] ?? 0,
            'asesi'   => $roleCounts['asesi'] ?? 0,
            'total'   => $this->model->where('deleted_at', null)->countAllResults(),
            'deleted' => $this->model->onlyDeleted()->countAllResults()
        ];

        return $this->respond(['status' => true, 'data' => $stats]);
    }

    /**
     * Mengambil detail user berdasarkan ID.
     */
    public function getById($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        // --- PERBAIKAN DI SINI ---
        // Menggunakan GroupModel untuk mendapatkan role/grup pengguna
        $groupModel = model(\Myth\Auth\Models\GroupModel::class);
        $groups = $groupModel->getGroupsForUser((int)$id);

        // Ubah array of objects menjadi array of strings (nama grup)
        $userGroups = array_column($groups, 'name');

        $data = $user->toArray();
        $data['groups'] = $userGroups; // Tambahkan nama grup ke data respons

        // Cek apakah 'Asesor' ada di dalam array grup yang sudah kita dapatkan
        if (in_array('Asesor', $userGroups)) {
            $asesorModel = new AsesorModel();
            $data['asesor_data'] = $asesorModel->where('id_user', $id)->first();

            if (!empty($data['asesor_data'])) {
                $asesorInstansiModel = new AsesorInstansiModel();
                $instansiLink = $asesorInstansiModel->find($data['asesor_data']['id_asesor']);
                $data['instansi_id'] = $instansiLink['instansi_id'] ?? null;
            }
        }

        return $this->respond(['status' => true, 'data' => $data]);
    }

    /**
     * Membuat user baru (Admin atau Asesor).
     * Diperbaiki untuk memastikan semua field wajib terisi.
     */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $users = new UserModel();

        // Aturan validasi
        $rules = [
            'username'     => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'        => 'required|valid_email|is_unique[users.email]',
            'password'     => 'required|min_length[8]',
            'nama_lengkap' => 'required|min_length[3]',
            'instansi_id'  => 'permit_empty|is_natural_no_zero|is_not_unique[instansi.id]',
        ];

        // Validasi tambahan khusus untuk Asesor
        if ($this->request->getPost('role') === 'Asesor') {
            $rules['skema_id'] = 'required|integer';
        }

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->listErrors());
        }

        // --- PERBAIKAN UTAMA DI SINI ---
        // Buat user entity dan isi datanya secara manual untuk memastikan
        $user = new \Myth\Auth\Entities\User();
        $user->username = $this->request->getPost('username');
        $user->email = $this->request->getPost('email');
        $user->password = $this->request->getPost('password');
        $user->nama_lengkap = $this->request->getPost('nama_lengkap');
        $user->activate();

        $role = $this->request->getPost('role');

        if ($users->withGroup($role)->save($user)) {
            $userId = $users->getInsertID();

            // Jika role adalah Asesor, simpan juga datanya ke tabel asesor
            if ($role === 'Asesor') {
                $asesorModel = new AsesorModel();
                $asesorModel->save([
                    'id_user' => $userId,
                    'nomor_registrasi' => $this->request->getPost('nomor_registrasi'),
                    'id_skema' => $this->request->getPost('skema_id')
                ]);

                $asesorId = $asesorModel->getInsertID();

                $instansiId = $this->request->getPost('instansi_id');
                if ($asesorId && !empty($instansiId)) {
                    $asesorInstansiModel = new AsesorInstansiModel();
                    $asesorInstansiModel->insert([
                        'asesor_id'   => $asesorId,
                        'instansi_id' => $instansiId,
                    ]);
                }
            }


            return $this->respondCreated(['status' => true, 'message' => "User {$role} berhasil dibuat."]);
        }

        return $this->fail($users->errors());
    }

    /**
     * Mengupdate data user, termasuk role dan skema asesor.
     */
    public function update(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Ambil ID dari data POST, bukan dari parameter URL
        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->fail('ID User diperlukan untuk proses update.', 400);
        }

        $users = new UserModel();
        if (!$users->find($id)) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        $postData = $this->request->getPost();

        // Aturan validasi yang sudah diperbaiki
        // is_unique[users.email,id,{$id}] akan mengabaikan record dengan ID ini
        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'email'        => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'         => 'required|in_list[Admin,Asesor,Asesi]',
            'instansi_id'  => 'permit_empty|is_natural_no_zero|is_not_unique[instansi.id]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->listErrors());
        }

        // 1. Update data dasar di tabel 'users'
        $userData = [
            'nama_lengkap' => $postData['nama_lengkap'],
            'email'        => $postData['email']
        ];
        $users->update($id, $userData);

        // 2. Update group/role
        $groups = model(\Myth\Auth\Models\GroupModel::class);
        $groups->removeUserFromAllGroups((int)$id);
        $group = $groups->where('name', $postData['role'])->first();
        if ($group) {
            $groups->addUserToGroup((int)$id, $group->id);
        }

        $asesorModel = new AsesorModel();
        $asesorInstansiModel = new AsesorInstansiModel();
        $asesorData = $asesorModel->where('id_user', $id)->first();

        if ($postData['role'] === 'Asesor') {
            $skemaId = $postData['skema_id'] ?? null;
            $instansiId = $postData['instansi_id'] ?? null;
            $asesorId = null;

            // Update atau buat data di tabel 'asesor'
            if ($skemaId) {
                if ($asesorData) {
                    $asesorModel->update($asesorData['id_asesor'], ['id_skema' => $skemaId]);
                    $asesorId = $asesorData['id_asesor'];
                } else {
                    $asesorModel->insert(['id_user' => $id, 'id_skema' => $skemaId]);
                    $asesorId = $asesorModel->getInsertID();
                }
            }

            // "Upsert" logic untuk tabel 'asesor_instansi'
            if ($asesorId) {
                $existingLink = $asesorInstansiModel->find($asesorId);

                if (!empty($instansiId)) {
                    // Jika ada instansi baru, update atau insert
                    if ($existingLink) {
                        $asesorInstansiModel->update($asesorId, ['instansi_id' => $instansiId]);
                    } else {
                        $asesorInstansiModel->insert(['asesor_id' => $asesorId, 'instansi_id' => $instansiId]);
                    }
                } else {
                    // Jika instansi dikosongkan, hapus relasinya
                    if ($existingLink) {
                        $asesorInstansiModel->delete($asesorId);
                    }
                }
            }
        } else {
            // Jika role diubah dari Asesor ke lain, hapus relasi instansi
            if ($asesorData) {
                $asesorInstansiModel->delete($asesorData['id_asesor']);
            }
        }

        return $this->respondUpdated(['status' => true, 'message' => 'User berhasil diupdate.']);
    }

    /**
     * Menghapus user (Soft Delete).
     */
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($id == user()->id) {
            return $this->fail('Tidak dapat mengarsipkan akun sendiri.', 403);
        }
        if ($this->model->delete($id)) { // Ini sudah soft delete karena $useSoftDeletes = true di model
            return $this->respondDeleted(['status' => true, 'message' => 'User berhasil diarsipkan.']);
        }
        return $this->fail('Gagal mengarsipkan user.');
    }

    /**
     * Mengubah status aktif/nonaktif user.
     */
    public function toggleStatus($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($id == user()->id) {
            return $this->fail('Tidak dapat menonaktifkan akun sendiri.', 403);
        }
        $user = $this->model->find($id);
        if (!$user) return $this->failNotFound('User tidak ditemukan.');

        $user->active = !$user->active;
        $this->model->save($user);

        $statusText = $user->active ? 'diaktifkan' : 'dinonaktifkan';
        return $this->respond(['status' => true, 'message' => "User berhasil {$statusText}."]);
    }
}
