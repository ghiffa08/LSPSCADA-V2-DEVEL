<?php

// Simple test script to check role detection
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$pathsPath = FCPATH . '../app/Config/Paths.php';
$paths = require realpath($pathsPath) ?: $pathsPath;
$app = new \CodeIgniter\CodeIgniter($paths);
$app->initialize();

// Test user 4 (asesi1@example.com)
$userId = 4;

try {
    $db = \Config\Database::connect();

    // Test the GroupUserModel directly
    $groupUserModel = new \App\Models\GroupUserModel();
    $roles = $groupUserModel->getRolesByUserId($userId);

    echo "User ID: $userId\n";
    echo "Roles from GroupUserModel: " . json_encode($roles) . "\n";

    // Test the User entity
    $userModel = new \App\Models\UserMythModel();
    $user = $userModel->find($userId);

    if ($user) {
        echo "User found: " . $user->email . "\n";
        echo "User class: " . get_class($user) . "\n";

        if ($user instanceof \App\Entities\User) {
            echo "isAdmin(): " . ($user->isAdmin() ? 'true' : 'false') . "\n";
            echo "isAsesor(): " . ($user->isAsesor() ? 'true' : 'false') . "\n";
            echo "isAsesi(): " . ($user->isAsesi() ? 'true' : 'false') . "\n";
        } else {
            echo "User is not an instance of App\\Entities\\User\n";
        }
    } else {
        echo "User not found\n";
    }

    // Check auth_groups_users table directly
    $result = $db->table('auth_groups_users')
        ->join('auth_groups', 'auth_groups.id=auth_groups_users.group_id', 'left')
        ->where('auth_groups_users.user_id', $userId)
        ->select('auth_groups.name, auth_groups_users.group_id, auth_groups_users.user_id')
        ->get()->getResultArray();

    echo "Direct DB query result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
