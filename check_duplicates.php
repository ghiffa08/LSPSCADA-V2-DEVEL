<?php

// Bootstrap CodeIgniter
require_once 'vendor/autoload.php';

// Get the application instance
$app = \Config\Services::codeigniter();
$app->initialize();

// Set the environment
$_SERVER['CI_ENVIRONMENT'] = 'development';

// Bootstrap the application
$app->run();

// Now we can use the services
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

echo "=== DUPLICATE GROUP ASSIGNMENTS ===\n";
if (empty($duplicates)) {
    echo "No duplicate group assignments found.\n";
} else {
    foreach ($duplicates as $duplicate) {
        echo "User ID: {$duplicate['user_id']}, Email: {$duplicate['email']}, Group: {$duplicate['group_name']}, Count: {$duplicate['count']}\n";
    }
}

// Check user ID 1 specifically
echo "\n=== USER ID 1 GROUP ASSIGNMENTS ===\n";
$userGroups = $db->table('auth_groups_users')
    ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
    ->where('auth_groups_users.user_id', 1)
    ->select('auth_groups.name, auth_groups_users.id as assignment_id')
    ->get()
    ->getResultArray();

foreach ($userGroups as $group) {
    echo "Group: {$group['name']}, Assignment ID: {$group['assignment_id']}\n";
}

// Test the GroupUserModel method
echo "\n=== GROUPUSERMODEL::GETROLESBYUSERID(1) ===\n";
$groupUserModel = new \App\Models\GroupUserModel();
$roles = $groupUserModel->getRolesByUserId(1);
echo "Roles returned: " . json_encode($roles) . "\n";
echo "Count: " . count($roles) . "\n";
