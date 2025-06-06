<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestRoleCommand extends BaseCommand
{
    protected $group       = 'test';
    protected $name        = 'test:role';
    protected $description = 'Test role detection for users';

    public function run(array $params)
    {
        // Test user 4 (asesi1@example.com)
        $userId = 4;

        try {
            $db = \Config\Database::connect();

            // Test the GroupUserModel directly
            $groupUserModel = new \App\Models\GroupUserModel();
            $roles = $groupUserModel->getRolesByUserId($userId);

            CLI::write("User ID: $userId");
            CLI::write("Roles from GroupUserModel: " . json_encode($roles));

            // Test the User entity
            $userModel = new \App\Models\UserMythModel();
            $user = $userModel->find($userId);

            if ($user) {
                CLI::write("User found: " . $user->email);
                CLI::write("User class: " . get_class($user));

                if ($user instanceof \App\Entities\User) {
                    CLI::write("isAdmin(): " . ($user->isAdmin() ? 'true' : 'false'));
                    CLI::write("isAsesor(): " . ($user->isAsesor() ? 'true' : 'false'));
                    CLI::write("isAsesi(): " . ($user->isAsesi() ? 'true' : 'false'));
                } else {
                    CLI::write("User is not an instance of App\\Entities\\User");
                }
            } else {
                CLI::write("User not found");
            }

            // Check auth_groups_users table directly
            $result = $db->table('auth_groups_users')
                ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
                ->where('auth_groups_users.user_id', $userId)
                ->select('auth_groups.name, auth_groups_users.group_id, auth_groups_users.user_id')
                ->get()->getResultArray();

            CLI::write("Direct DB query result: " . json_encode($result));
        } catch (\Exception $e) {
            CLI::write("Error: " . $e->getMessage(), 'red');
            CLI::write("Stack trace: " . $e->getTraceAsString(), 'red');
        }
    }
}
