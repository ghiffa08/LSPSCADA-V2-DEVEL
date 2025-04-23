<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{

    protected $db, $builder;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    public function index()
    {
        //
    }

    public function profile($idUser)
    {

        $syarat = [
            'id' => $idUser
        ];

        $this->builder->select('users.id as userid, username, email, fullname, name');
        $this->builder->join('auth_groups_users', 'auth_groups_users.user_id = users.id');
        $this->builder->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id');
        $this->builder->where('users.id', $idUser);
        $query = $this->builder->get();

        $data['siteTitle'] = 'Profil Pengguna';
        $data['user'] = $query->getRow();

        return view('dashboard/profile', $data);
    }

    public function simpanUser()
    {
        $rules = [
            'email' => 'required',
            'username' => 'required',
            'password' => 'required|min_length[8]|max_length[255]|matches[pass_confirm]',
            'pass_confirm' => 'required|min_length[8]|max_length[255]',
            // tambahkan aturan validasi lainnya sesuai kebutuhan
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'email' => $this->request->getVar('email'),
            'username' => $this->request->getVar('username'),
            'password_hash' => $this->request->getVar('password'),
        ];

        // Lakukan simpan user dengan data di atas
        $this->userMyth->withGroup($this->request->getVar('role'))->save($data);

        session()->setFlashdata('pesan', 'User berhasil disimpan');
        return redirect()->to('/list-user');
    }


    // Update Profile
    // public function updateUser()
    // {

    //     // $rules = [
    //     //     'username' => 'required|is_unique[users.username]',
    //     //     // tambahkan aturan validasi lainnya sesuai kebutuhan
    //     // ];

    //     $rules = [
    //         'username' => [
    //             'rules' => 'required|is_unique[users.username]',
    //             'errors' => [
    //                 'required' => 'Kolom username harus diisi.',
    //                 'is_unique' => 'Username sudah digunakan, silakan pilih username lain.'
    //                 // tambahkan pesan kesalahan untuk aturan validasi lainnya sesuai kebutuhan
    //             ],
    //         ],
    //         // tambahkan aturan validasi lainnya sesuai kebutuhan
    //     ];

    //     if (!$this->validate($rules)) {
    //         return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    //     }

    //     $data = [
    //         'id' => $this->request->getVar('id'),
    //         'email' => $this->request->getVar('email'),
    //         'username' => $this->request->getVar('username'),
    //         'fullname' => $this->request->getVar('fullname'),
    //         'no_telp' => $this->request->getVar('phone_number'),
    //         'user_image' => $this->request->getVar('user_image'),
    //     ];

    //     $this->user->update($this->request->getVar('id'), $data);
    //     session()->setFlashdata('pesan', 'User berhasil diupdate');
    //     return redirect()->to('profile/' . user()->id);
    //     // return redirect()->to('/');
    // }

    public function updateUser()
    {

        $rules = [
            'tanda_tangan' => [
                'label' => 'Tanda Tangan',
                'rules' => 'uploaded[tanda_tangan]|max_size[tanda_tangan,2048]|mime_in[tanda_tangan,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'uploaded' => 'Harus upload {field} *',
                    'max_size' => 'File maksimal 2MB *',
                    'mime_in' => 'File harus berupa gambar / foto'
                ],
            ],
            // tambahkan aturan validasi lainnya sesuai kebutuhan
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanda_tangan = $this->request->getFile('tanda_tangan');
        $nama_tanda_tangan = $tanda_tangan->getRandomName();
        $tanda_tangan->move('html/upload/tanda tangan', $nama_tanda_tangan);

        $data = [
            'username' => $this->request->getVar('username'),
            'fullname' => $this->request->getVar('fullname'),
            'no_telp' => $this->request->getVar('no_telp'),
            'user_image' => $this->request->getVar('user_image'),
            'tanda_tangan' => $nama_tanda_tangan,
        ];

        $this->usermodel->update($this->request->getVar('id'), $data);
        session()->setFlashdata('pesan', 'User berhasil diupdate');
        return redirect()->to('profile/' . user()->id);
    }


    public function delete_user()
    {
        $id = $this->request->getPost('id');
        $this->usermodel->deleteUser($id);
        session()->setFlashdata('message', 'Group berhasil dihapus!');
        return redirect()->to('/groups-setting');
    }
}
