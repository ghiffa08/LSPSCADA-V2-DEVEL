<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanDuplicateGroups extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:clean-duplicates';
    protected $description = 'Remove duplicate group assignments from auth_groups_users table';
    protected $usage       = 'auth:clean-duplicates';

    public function run(array $params)
    {
        CLI::write('=== CLEANING DUPLICATE GROUP ASSIGNMENTS ===', 'yellow');

        $db = \Config\Database::connect();

        // Find all duplicate entries
        $duplicates = $db->query("
            SELECT user_id, group_id, MIN(id) as keep_id
            FROM auth_groups_users 
            GROUP BY user_id, group_id 
            HAVING COUNT(*) > 1
        ")->getResultArray();

        if (empty($duplicates)) {
            CLI::write('No duplicates found.', 'green');
            return;
        }

        CLI::write('Found ' . count($duplicates) . ' duplicate group assignments', 'yellow');

        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            $userId = $duplicate['user_id'];
            $groupId = $duplicate['group_id'];
            $keepId = $duplicate['keep_id'];

            // Delete all entries except the one with the minimum ID
            $deleted = $db->table('auth_groups_users')
                ->where('user_id', $userId)
                ->where('group_id', $groupId)
                ->where('id !=', $keepId)
                ->delete();

            $totalDeleted += $db->affectedRows();

            CLI::write("Cleaned user_id: {$userId}, group_id: {$groupId} (kept ID: {$keepId})", 'white');
        }

        CLI::write("\nTotal duplicate entries removed: {$totalDeleted}", 'green');

        // Verify cleanup
        CLI::write("\n=== VERIFICATION ===", 'yellow');
        $remainingDuplicates = $db->query("
            SELECT user_id, group_id, COUNT(*) as count
            FROM auth_groups_users 
            GROUP BY user_id, group_id 
            HAVING COUNT(*) > 1
        ")->getResultArray();

        if (empty($remainingDuplicates)) {
            CLI::write('✓ All duplicates successfully removed!', 'green');
        } else {
            CLI::write('✗ Some duplicates remain:', 'red');
            foreach ($remainingDuplicates as $remaining) {
                CLI::write("User ID: {$remaining['user_id']}, Group ID: {$remaining['group_id']}, Count: {$remaining['count']}", 'red');
            }
        }

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
