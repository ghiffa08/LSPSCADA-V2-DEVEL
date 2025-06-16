<?php

/**
 * Test Script untuk Auth System yang sudah diperbaiki
 * Menguji login manual, OAuth, session management, dan helper functions
 */

echo "=== AUTH SYSTEM DEBUG TEST ===\n\n";

// Test 1: Helper functions
echo "1. Testing helper functions...\n";

try {
    // Test logged_in function
    $loggedIn = logged_in();
    echo "   logged_in(): " . ($loggedIn ? 'true' : 'false') . "\n";

    // Test user function
    $user = user();
    echo "   user(): " . ($user ? 'User ID ' . $user->id : 'null') . "\n";

    // Test in_groups function
    $isAsesi = in_groups('asesi');
    echo "   in_groups('asesi'): " . ($isAsesi ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. Testing session data...\n";

try {
    $sessionData = session()->get();
    echo "   Session logged_in: " . (session('logged_in') ?: 'not set') . "\n";
    echo "   Session user_email: " . (session('user_email') ?: 'not set') . "\n";
    echo "   Session roles: " . (session('roles') ? implode(', ', session('roles')) : 'not set') . "\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Testing database connection...\n";

try {
    $db = \Config\Database::connect();
    $query = $db->query("SELECT COUNT(*) as total FROM users WHERE active = 1");
    $result = $query->getRow();
    echo "   Active users in database: " . $result->total . "\n";

    // Test auth tables
    $authGroups = $db->query("SELECT COUNT(*) as total FROM auth_groups")->getRow();
    echo "   Auth groups: " . $authGroups->total . "\n";

    $authGroupsUsers = $db->query("SELECT COUNT(*) as total FROM auth_groups_users")->getRow();
    echo "   User-group assignments: " . $authGroupsUsers->total . "\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Testing AuthenticationService...\n";

try {
    $authService = new \App\Services\Authentication\AuthenticationService();
    echo "   AuthenticationService created successfully\n";

    // Test getCurrentUser method if exists
    if (method_exists($authService, 'getCurrentUser')) {
        $currentUser = $authService->getCurrentUser();
        echo "   getCurrentUser(): " . ($currentUser ? 'User ID ' . $currentUser->id : 'null') . "\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n5. Testing file permissions and structure...\n";

$criticalFiles = [
    'app/Controllers/AuthController.php',
    'app/Controllers/OAuthController.php',
    'app/Services/Authentication/AuthenticationService.php',
    'app/Helpers/auth_helper.php',
    'app/Models/UserMythModel.php',
    'app/Models/GroupUserModel.php'
];

foreach ($criticalFiles as $file) {
    $exists = file_exists($file);
    $readable = $exists ? is_readable($file) : false;
    echo "   $file: " . ($exists ? ($readable ? 'OK' : 'NOT READABLE') : 'MISSING') . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "Jika ada error, perbaiki sebelum testing login/OAuth\n";
echo "Jika semua OK, coba login manual dan OAuth\n";
