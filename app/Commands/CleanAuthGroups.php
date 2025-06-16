<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanAuthGroups extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:clean-auth-groups';
    protected $description = 'Remove duplicate entries from auth_groups table';
    protected $usage       = 'auth:clean-auth-groups';

    public function run(array $params)
    {
        CLI::write('=== CLEANING AUTH_GROUPS TABLE DUPLICATES ===', 'yellow');

        $db = \Config\Database::connect();

        // Get all unique groups (keep the first occurrence of each)
        $uniqueGroups = $db->query("
            SELECT MIN(id) as keep_id, name, description
            FROM auth_groups 
            GROUP BY id, name, description
        ")->getResultArray();

        CLI::write('Found ' . count($uniqueGroups) . ' unique groups to keep', 'white');

        // Create a temporary table with unique data
        $db->query("CREATE TEMPORARY TABLE auth_groups_temp AS 
                   SELECT DISTINCT * FROM auth_groups");

        // Clear the original table
        $db->query("DELETE FROM auth_groups");
        CLI::write('Cleared auth_groups table', 'yellow');

        // Insert unique data back
        $db->query("INSERT INTO auth_groups SELECT * FROM auth_groups_temp");
        CLI::write('Restored unique data', 'yellow');

        // Drop temporary table
        $db->query("DROP TEMPORARY TABLE auth_groups_temp");

        // Verify cleanup
        CLI::write("\n=== VERIFICATION ===", 'yellow');
        $groupsAfter = $db->table('auth_groups')->get()->getResultArray();
        CLI::write('Groups after cleanup: ' . count($groupsAfter), 'white');

        foreach ($groupsAfter as $group) {
            CLI::write("ID: {$group['id']}, Name: {$group['name']}", 'white');
        }

        // Test the roles query again
        CLI::write("\n=== TESTING ROLES QUERY FOR USER 35 ===", 'yellow');
        $roles = $db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->where('auth_groups_users.user_id', 35)
            ->select('auth_groups.name')
            ->get()->getResultArray();

        $mapped = array_map(fn($row) => $row['name'], $roles);
        CLI::write("Roles result: " . json_encode($mapped), 'white');
        CLI::write("Count: " . count($mapped), 'white');

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
