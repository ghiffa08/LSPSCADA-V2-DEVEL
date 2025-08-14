<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\GroupUserModel;

class CheckDuplicates extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:check-duplicates';
    protected $description = 'Check for duplicate group assignments and session issues';
    protected $usage       = 'auth:check-duplicates';

    public function run(array $params)
    {
        CLI::write('=== CHECKING FOR DUPLICATE GROUP ASSIGNMENTS ===', 'yellow');

        $db = \Config\Database::connect();

        // Check for duplicate group assignments
        $query = $db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->join('users', 'users.id = auth_groups_users.user_id')
            ->select('users.id as user_id, users.email, auth_groups.name as group_name, COUNT(*) as count')
            ->groupBy('users.id, auth_groups.name')
            ->having('COUNT(*) > 1')
            ->get();

        $duplicates = $query->getResultArray();

        if (empty($duplicates)) {
            CLI::write('No duplicate group assignments found.', 'green');
        } else {
            CLI::write('Found duplicate group assignments:', 'red');
            foreach ($duplicates as $duplicate) {
                CLI::write("User ID: {$duplicate['user_id']}, Email: {$duplicate['email']}, Group: {$duplicate['group_name']}, Count: {$duplicate['count']}", 'red');
            }
        }

        // Check user ID 1 specifically
        CLI::write("\n=== USER ID 1 GROUP ASSIGNMENTS ===", 'yellow');
        $userGroups = $db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups_users.user_id', 1)
            ->select('auth_groups.name, auth_groups_users.id as assignment_id')
            ->get()
            ->getResultArray();

        foreach ($userGroups as $group) {
            CLI::write("Group: {$group['name']}, Assignment ID: {$group['assignment_id']}", 'white');
        }

        // Test the GroupUserModel method
        CLI::write("\n=== GROUPUSERMODEL::GETROLESBYUSERID(1) ===", 'yellow');
        $groupUserModel = new GroupUserModel();
        $roles = $groupUserModel->getRolesByUserId(1);
        CLI::write("Roles returned: " . json_encode($roles), 'white');
        CLI::write("Count: " . count($roles), 'white');

        // Check for unique roles
        $uniqueRoles = array_unique($roles);
        CLI::write("Unique roles: " . json_encode($uniqueRoles), 'white');
        CLI::write("Unique count: " . count($uniqueRoles), 'white');

        if (count($roles) !== count($uniqueRoles)) {
            CLI::write("WARNING: Duplicate roles detected in array!", 'red');
        }

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
