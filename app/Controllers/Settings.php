<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends BaseController
{

    protected $db, $builder;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    public function index()
    {
        $data = [

            'siteTitle' => "Settings",

        ];

        return view('dashboard/settings/settings', $data);
    }

    public function groups_setting()
    {

        $data = [
            'siteTitle' => "Groups Setting",
            'listGroups' => $this->groups->findAll(),
            // 'listGroupUsers' => $this->group_users->getGroupUsers(),
        ];

        return view('dashboard/settings/setting_groups', $data);
        // dd($data);
    }

    public function store_group()
    {

        $data = [
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description')
        ];

        if (!$this->validate($this->groups->getValidationRules())) {
            session()->setFlashdata('error', $this->validator->listErrors());
            return redirect()->back();
        }
        $this->groups->save($data);
        session()->setFlashdata('pesan', 'Group berhasil ditambahkan!');
        return redirect()->to('/groups-setting');
    }


    public function update_group()
    {

        $data = [
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description')
        ];

        $this->groups->update($this->request->getVar('id'), $data);
        session()->setFlashdata('pesan', 'Group berhasil diupdate!');
        return redirect()->to('/groups-setting');
    }

    public function delete_group()
    {
        $id = $this->request->getPost('id');
        $this->groups->deleteGroup($id);
        session()->setFlashdata('pesan', 'Group berhasil dihapus!');
        return redirect()->to('/groups-setting');
    }

    public function update_group_user()
    {

        $data = [
            'group_id' => $this->request->getVar('groupid')
        ];

        $this->group_users->update($this->request->getVar('id'), $data);
        session()->setFlashdata('pesan', 'Group berhasil diupdate!');
        return redirect()->to('/groups-setting');
    }

    public function simpan()
    {
        $validasi  = \Config\Services::validation();
        $aturan = [
            'name' => [
                'label' => 'Nama Group',
                'rules' => 'required|is_unique[auth_groups.name]',
                'errors' => [
                    'required' => '{field} harus diisi',
                    'is_unique' => 'Nama group sudah digunakan, pilih nama group lain.'
                ]
            ],
            'description' => [
                'label' => 'Deskripsi Group',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} harus diisi',
                ]
            ],
        ];

        $validasi->setRules($aturan);
        if ($validasi->withRequest($this->request)->run()) {
            $name = $this->request->getPost('name');
            $description = $this->request->getPost('description');

            $data = [
                'name' => $name,
                'description' => $description,

            ];

            $this->groups->save($data);

            $hasil['sukses'] = "Berhasil memasukkan data";
            $hasil['error'] = true;
        } else {
            $hasil['sukses'] = false;
            $hasil['error'] = $validasi->listErrors();
        }


        return json_encode($hasil);
    }
}
