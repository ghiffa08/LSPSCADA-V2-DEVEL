<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class InspectGroupsTable extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:inspect-groups';
    protected $description = 'Inspect the auth_groups_users table structure and data';
    protected $usage       = 'auth:inspect-groups';

    public function run(array $params)
    {
        CLI::write('=== INSPECTING AUTH_GROUPS_USERS TABLE ===', 'yellow');

        $db = \Config\Database::connect();

        // Show table structure
        CLI::write("\n=== TABLE STRUCTURE ===", 'yellow');
        $fields = $db->getFieldData('auth_groups_users');
        foreach ($fields as $field) {
            CLI::write("Field: {$field->name}, Type: {$field->type}, Primary: " . ($field->primary_key ? 'Yes' : 'No'), 'white');
        }

        // Show raw data for user 35 (the one in the log)
        CLI::write("\n=== RAW DATA FOR USER 35 ===", 'yellow');
        $rawData = $db->table('auth_groups_users')
            ->where('user_id', 35)
            ->get()
            ->getResultArray();

        CLI::write("Raw entries count: " . count($rawData), 'white');
        foreach ($rawData as $entry) {
            CLI::write(json_encode($entry), 'white');
        }

        // Show with join
        CLI::write("\n=== JOINED DATA FOR USER 35 ===", 'yellow');
        $joinedData = $db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups_users.user_id', 35)
            ->select('auth_groups_users.*, auth_groups.name as group_name')
            ->get()
            ->getResultArray();

        foreach ($joinedData as $entry) {
            CLI::write(json_encode($entry), 'white');
        }

        // Test the exact query from getRolesByUserId
        CLI::write("\n=== EXACT GETROLESBYUSERID QUERY FOR USER 35 ===", 'yellow');
        $result = $db->table('auth_groups_users')
            ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
            ->where('auth_groups_users.user_id', 35)
            ->select('auth_groups.name')
            ->get()->getResultArray();

        CLI::write("Query result:", 'white');
        foreach ($result as $row) {
            CLI::write(json_encode($row), 'white');
        }

        $mapped = array_map(fn($row) => $row['name'], $result);
        CLI::write("Mapped result: " . json_encode($mapped), 'white');

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
