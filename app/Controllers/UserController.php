<?php

namespace App\Controllers;

use Config\Database;
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

        return view('admin/profile', $data);
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
        $this->authUserModel->withGroup($this->request->getVar('role'))->save($data);

        session()->setFlashdata('pesan', 'User berhasil disimpan');
        return redirect()->to('/list-user');
    }

    public function updateUser()
    {
        $rules = [
            'username' => 'required',
            'fullname' => 'required',
            'no_telp'  => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->request->getVar('id');
        $user   = $this->authUserModel->find($userId);

        $data = [
            'username' => $this->request->getVar('username'),
            'fullname' => $this->request->getVar('fullname'),
            'no_telp'  => $this->request->getVar('no_telp'),
        ];

        $signatureDir = WRITEPATH . 'uploads/user/signatures/';
        if (!is_dir($signatureDir)) {
            mkdir($signatureDir, 0755, true);
        }

        $newSignatureName = null;
        $signatureData    = $this->request->getVar('tanda_tangan');

        $db = Database::connect();
        $db->transBegin();

        // Tangani jika tanda tangan berupa base64 dari canvas
        if (!empty($signatureData) && strpos($signatureData, 'data:image') === 0) {
            list(, $encodedData) = explode(',', $signatureData);
            $decodedData         = base64_decode($encodedData);
            $newSignatureName    = uniqid('sig_') . '.png';
            $savePath            = $signatureDir . $newSignatureName;

            if (!file_put_contents($savePath, $decodedData)) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan tanda tangan.');
            }
        } else {
            // Tangani jika tanda tangan berupa upload file
            $file = $this->request->getFile('tanda_tangan');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newSignatureName = $file->getRandomName();
                if (!$file->move($signatureDir, $newSignatureName)) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Gagal mengupload tanda tangan.');
                }
            }
        }

        // Jika ada tanda tangan baru, hapus tanda tangan lama
        if ($newSignatureName) {
            if (!empty($user['tanda_tangan'])) {
                $oldSignaturePath = $signatureDir . $user['tanda_tangan'];
                if (file_exists($oldSignaturePath)) {
                    unlink($oldSignaturePath);
                }
            }
            $data['tanda_tangan'] = $newSignatureName;
        }

        // Update ke database
        if ($this->authUserModel->update($userId, $data)) {
            $db->transCommit();
            session()->setFlashdata('pesan', 'Data berhasil diupdate.');
            return redirect()->to('profile/' . $userId);
        } else {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data.');
        }
    }




    public function delete_user()
    {
        $id = $this->request->getPost('id');
        $this->authUserModel->deleteUser($id);
        session()->setFlashdata('message', 'Group berhasil dihapus!');
        return redirect()->to('/groups-setting');
    }
}
