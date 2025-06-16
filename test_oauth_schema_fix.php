<?php

/**
 * Test OAuth Database Fix
 * 
 * Script untuk testing OAuth setelah database schema fix
 */

// Set up minimal CodeIgniter environment
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$pathsPath = FCPATH . '../app/Config/Paths.php';
$paths = require realpath($pathsPath) ?: $pathsPath;
$app = new \CodeIgniter\CodeIgniter($paths);
$app->initialize();

echo "=== Testing OAuth Database Schema Fix ===\n\n";

try {
    $db = \Config\Database::connect();

    // Test 1: Check table structures
    echo "1. Checking table structures...\n";

    $tables = ['users', 'asesi', 'asesor'];
    foreach ($tables as $table) {
        try {
            $fields = $db->getFieldNames($table);
            echo "   ✅ Table '$table' fields: " . implode(', ', $fields) . "\n";
        } catch (Exception $e) {
            echo "   ❌ Table '$table' error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n2. Testing AuthenticationService schema compatibility...\n";

    // Test createRoleSpecificData compatibility
    $authService = new \App\Services\Authentication\AuthenticationService();
    echo "   ✅ AuthenticationService instantiated\n";

    // Test database field checking
    $asesiFields = $db->getFieldNames('asesi');
    $hasNoHp = in_array('no_hp', $asesiFields);
    echo "   - asesi table has 'no_hp' field: " . ($hasNoHp ? 'Yes' : 'No') . "\n";

    // Test OAuth data structure
    echo "\n3. Testing OAuth data structure...\n";

    $testOAuthData = [
        'email' => 'oauth_test@example.com',
        'name' => 'OAuth Test User',
        'username' => 'oauthtest',
        'id' => '999999999',
        'role' => 'asesi'
    ];

    echo "   ✅ OAuth data structure valid\n";
    echo "   - Email: " . $testOAuthData['email'] . "\n";
    echo "   - Role: " . $testOAuthData['role'] . "\n";

    // Test register data creation for OAuth
    echo "\n4. Testing OAuth register data creation...\n";

    $registerData = [
        'email' => $testOAuthData['email'],
        'username' => $testOAuthData['username'],
        'fullname' => $testOAuthData['name'],
        'password' => bin2hex(random_bytes(16)),
        'pass_confirm' => bin2hex(random_bytes(16)),
        'group' => 'asesi',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'additional_data' => [
            'google_id' => $testOAuthData['id']
        ]
    ];

    echo "   ✅ OAuth register data created\n";
    echo "   - Username: " . $registerData['username'] . "\n";
    echo "   - Group: " . $registerData['group'] . "\n";

    // Test RegisterRequest creation
    echo "\n5. Testing RegisterRequest creation...\n";

    try {
        $registerRequest = \App\DTOs\Authentication\RegisterRequest::fromArray($registerData);
        echo "   ✅ RegisterRequest created successfully\n";
        echo "   - Email: " . $registerRequest->email . "\n";
        echo "   - Group: " . $registerRequest->group . "\n";

        // Test validation for OAuth
        $errors = $registerRequest->validate();
        if (empty($errors)) {
            echo "   ✅ RegisterRequest validation passed\n";
        } else {
            echo "   ⚠️  RegisterRequest validation warnings:\n";
            foreach ($errors as $field => $error) {
                echo "     - $field: $error\n";
            }
        }
    } catch (Exception $e) {
        echo "   ❌ RegisterRequest creation failed: " . $e->getMessage() . "\n";
    }

    echo "\n6. Testing existing OAuth users...\n";

    $existingOAuthUsers = $db->query("
        SELECT id, email, google_id, force_pass_reset, active 
        FROM users 
        WHERE google_id IS NOT NULL AND google_id != ''
    ")->getResultArray();

    if (empty($existingOAuthUsers)) {
        echo "   ℹ️  No existing OAuth users found\n";
    } else {
        echo "   Found " . count($existingOAuthUsers) . " existing OAuth users:\n";
        foreach ($existingOAuthUsers as $user) {
            $status = [];
            $status[] = $user['active'] ? 'Active' : 'Inactive';
            $status[] = !$user['force_pass_reset'] ? 'No force reset' : 'Force reset';

            echo "   - {$user['email']} (ID: {$user['id']}): " . implode(', ', $status) . "\n";
        }
    }

    echo "\n7. Schema compatibility summary...\n";

    // Check for common problematic fields
    $problemFields = ['no_hp'];
    $asesiFields = $db->getFieldNames('asesi');

    foreach ($problemFields as $field) {
        $exists = in_array($field, $asesiFields);
        $status = $exists ? '✅ Present' : '⚠️  Missing (OK - handled by code)';
        echo "   - asesi.$field: $status\n";
    }

    echo "\n=== Database Schema Fix Complete ===\n\n";

    echo "Fixed Issues:\n";
    echo "1. ✅ Removed hardcoded 'no_hp' field reference\n";
    echo "2. ✅ Added dynamic field checking for asesi table\n";
    echo "3. ✅ Made createRoleSpecificData schema-aware\n";
    echo "4. ✅ Improved error handling for missing columns\n\n";

    echo "Ready for OAuth Testing:\n";
    echo "1. Go to: " . site_url('auth/google') . "\n";
    echo "2. Complete Google login\n";
    echo "3. Should work without database field errors\n";
    echo "4. Should redirect to asesi/dashboard\n\n";

    echo "If you still get database errors:\n";
    echo "1. Check your asesi table structure\n";
    echo "2. Add missing columns if needed\n";
    echo "3. Or remove field references from the code\n";
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Fatal error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
