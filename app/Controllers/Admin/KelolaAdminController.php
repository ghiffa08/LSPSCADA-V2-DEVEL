<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserManagementModel;

class KelolaAdminController extends BaseController
{
    protected $userManagementModel;

    public function __construct()
    {
        $this->userManagementModel = new UserManagementModel();
    }

    /**
     * Display admin users page
     */
    public function index()
    {
        return view('admin/kelola_admin', [
            'title' => 'Kelola Admin'
        ]);
    }

    /**
     * Get admin users data for DataTable
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
                4 => 'users.active',
                5 => 'users.created_at'
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'users.nama_lengkap';

            // Get admin users data
            $builder = $this->userManagementModel
                ->select('users.id, users.username, users.nama_lengkap, users.email, 
                          users.active, users.created_at')
                ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'inner')
                ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'inner')
                ->where('auth_groups.name', 'Admin')
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
                $builder->orderBy('users.nama_lengkap', 'ASC');
            }

            $data = $builder->limit($limit, $start)->get()->getResultArray();

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
     * Create new admin user
     */
    public function createAdmin()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'password' => $this->request->getPost('password')
        ];

        // Validate data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[8]'
        ]);

        if (!$validation->run($userData)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $result = $this->userManagementModel->createAdminUser($userData);

            return $this->response->setJSON([
                'status' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
