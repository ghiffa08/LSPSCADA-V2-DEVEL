<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class InspectAuthGroups extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:inspect-auth-groups';
    protected $description = 'Inspect the auth_groups table';
    protected $usage       = 'auth:inspect-auth-groups';

    public function run(array $params)
    {
        CLI::write('=== INSPECTING AUTH_GROUPS TABLE ===', 'yellow');

        $db = \Config\Database::connect();

        // Show all groups
        CLI::write("\n=== ALL GROUPS ===", 'yellow');
        $groups = $db->table('auth_groups')->get()->getResultArray();
        foreach ($groups as $group) {
            CLI::write(json_encode($group), 'white');
        }

        // Check group_id 36 specifically
        CLI::write("\n=== GROUP ID 36 ===", 'yellow');
        $group36 = $db->table('auth_groups')->where('id', 36)->get()->getResultArray();
        CLI::write("Count: " . count($group36), 'white');
        foreach ($group36 as $group) {
            CLI::write(json_encode($group), 'white');
        }

        // Check if there are duplicate group names
        CLI::write("\n=== DUPLICATE GROUP NAMES ===", 'yellow');
        $duplicateNames = $db->query("
            SELECT name, COUNT(*) as count 
            FROM auth_groups 
            GROUP BY name 
            HAVING COUNT(*) > 1
        ")->getResultArray();

        if (empty($duplicateNames)) {
            CLI::write('No duplicate group names found.', 'green');
        } else {
            foreach ($duplicateNames as $dup) {
                CLI::write("Group name: {$dup['name']}, Count: {$dup['count']}", 'red');
            }
        }

        // Test a more specific query
        CLI::write("\n=== TESTING SPECIFIC JOIN ===", 'yellow');
        $testResult = $db->query("
            SELECT DISTINCT auth_groups.name 
            FROM auth_groups_users 
            LEFT JOIN auth_groups ON auth_groups.id = auth_groups_users.group_id 
            WHERE auth_groups_users.user_id = 35
        ")->getResultArray();

        CLI::write("DISTINCT query result:", 'white');
        foreach ($testResult as $row) {
            CLI::write(json_encode($row), 'white');
        }

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
