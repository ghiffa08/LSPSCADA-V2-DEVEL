<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Models\GroupModel;
use Myth\Auth\Password;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = new UserModel;
        $groups = new GroupModel;

        $users->insert([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Password::hash('admin'),
            'active' => 1,
        ]);

        $groups->addUserToGroup($users->getInsertID(), 1);

        $users->insert([
            'username' => 'asesor',
            'email' => 'asesor@gmail.com',
            'password' => Password::hash('asesor'),
            'active' => 1,
        ]);

        $groups->addUserToGroup($users->getInsertID(), 2);

        $users->insert([
            'username' => 'peserta',
            'email' => 'peserta@gmail.com',
            'password' => Password::hash('peserta'),
            'active' => 1,
        ]);

        $groups->addUserToGroup($users->getInsertID(), 3);
    }
}
