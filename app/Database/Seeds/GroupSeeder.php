<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Myth\Auth\Models\PermissionModel;
use Myth\Auth\Models\GroupModel;

class GroupSeeder extends Seeder
{
    public function run()
    {
        $groups = new GroupModel();

        $groups->insert([
            'name' => 'LSP',
            'description' => 'Admin LSP Scada',
        ]);

        $groups->insert([
            'name' => 'Asesor',
            'description' => 'Asesor LSP Scada',
        ]);

        $groups->insert([
            'name' => 'Peserta',
            'description' => 'Peserta Sertifikasi LSP Scada',
        ]);

        $permissions = new PermissionModel();
        $LSP = $permissions->findAll();
        foreach ($LSP as $permissions) {
            $groups->addPermissionToGroup($permissions->id, $groups->getInsertID());
        }

        $Asesor = $permissions->where('name', 'user-module')->findAll;
        foreach ($Asesor as $permissions) {
            $groups->addPermissionToGroup($permissions->id, $groups->getInsertID());
        }

        $Peserta = $permissions->where('name', 'user-module')->findAll;
        foreach ($Peserta as $permissions) {
            $groups->addPermissionToGroup($permissions->id, $groups->getInsertID());
        }
    }
}
