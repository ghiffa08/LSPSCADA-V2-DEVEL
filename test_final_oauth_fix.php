<?php

/**
 * Final OAuth Fix Test
 * 
 * This script tests the final OAuth fixes to ensure:
 * 1. No invalid fields are being inserted to database tables
 * 2. OAuth login works without database errors
 * 3. All field references match actual table schema
 */

require_once __DIR__ . '/vendor/autoload.php';

use CodeIgniter\Config\Services;
use CodeIgniter\Database\Config;

$db = null;

try {
    // Load database configuration
    $db = \Config\Database::connect();

    echo "=== FINAL OAUTH FIX VERIFICATION ===\n\n";

    // 1. Check table structures
    echo "1. CHECKING TABLE STRUCTURES:\n";

    $tables = ['users', 'asesi', 'asesor'];
    $tableFields = [];

    foreach ($tables as $table) {
        $fields = $db->getFieldNames($table);
        $tableFields[$table] = $fields;
        echo "   - {$table} table fields (" . count($fields) . "): " . implode(', ', $fields) . "\n";
    }

    echo "\n";

    // 2. Check for problematic field references in code files
    echo "2. CHECKING FOR PROBLEMATIC FIELD REFERENCES:\n";

    $filesToCheck = [
        'app/Services/Authentication/AuthenticationService.php',
        'app/DTOs/Authentication/RegisterRequest.php'
    ];

    $problematicFields = ['isOAuthUser', 'google_id', 'oauthId'];
    $foundProblems = false;

    foreach ($filesToCheck as $file) {
        $fullPath = __DIR__ . '/' . $file;
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);

            foreach ($problematicFields as $field) {
                if (strpos($content, $field) !== false) {
                    echo "   ❌ Found '{$field}' in {$file}\n";
                    $foundProblems = true;
                }
            }
        }
    }

    if (!$foundProblems) {
        echo "   ✅ No problematic field references found\n";
    }

    echo "\n";

    // 3. Test OAuth user creation simulation
    echo "3. TESTING OAUTH USER CREATION SIMULATION:\n";

    // Simulate what happens during OAuth registration
    $testOAuthData = [
        'username' => 'testuser_oauth',
        'email' => 'test@example.com',
        'password' => 'hashed_oauth_password',
        'nama_lengkap' => 'Test OAuth User',
        'active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'oauth_provider' => 'google'
    ];

    // Check if all fields exist in users table
    $userFields = $tableFields['users'];
    $invalidUserFields = [];

    foreach ($testOAuthData as $field => $value) {
        if (!in_array($field, $userFields)) {
            $invalidUserFields[] = $field;
        }
    }

    if (empty($invalidUserFields)) {
        echo "   ✅ All OAuth user fields are valid for users table\n";
    } else {
        echo "   ❌ Invalid fields for users table: " . implode(', ', $invalidUserFields) . "\n";
    }

    // Test asesi data creation
    $testAsesiData = [
        'id_user' => 1, // This will be the actual user ID
        'nik' => '',
        'tempat_lahir' => '',
        'tanggal_lahir' => null,
        'jenis_kelamin' => null,
        'pendidikan_terakhir' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Add no_hp only if it exists
    if (in_array('no_hp', $tableFields['asesi'])) {
        $testAsesiData['no_hp'] = null;
    }

    $asesiFields = $tableFields['asesi'];
    $invalidAsesiFields = [];

    foreach ($testAsesiData as $field => $value) {
        if (!in_array($field, $asesiFields)) {
            $invalidAsesiFields[] = $field;
        }
    }

    if (empty($invalidAsesiFields)) {
        echo "   ✅ All asesi fields are valid for asesi table\n";
    } else {
        echo "   ❌ Invalid fields for asesi table: " . implode(', ', $invalidAsesiFields) . "\n";
    }

    echo "\n";

    // 4. Check existing OAuth users for issues
    echo "4. CHECKING EXISTING OAUTH USERS:\n";

    $oauthUsers = $db->table('users')
        ->where('oauth_provider IS NOT NULL')
        ->where('oauth_provider !=', '')
        ->get()
        ->getResultArray();

    echo "   - Found " . count($oauthUsers) . " OAuth users\n";

    $usersWithForceReset = $db->table('users')
        ->where('oauth_provider IS NOT NULL')
        ->where('oauth_provider !=', '')
        ->where('force_pass_reset', 1)
        ->countAllResults();

    if ($usersWithForceReset > 0) {
        echo "   ❌ {$usersWithForceReset} OAuth users still have force_pass_reset = 1\n";
        echo "   Run fix_oauth_users.php to fix this\n";
    } else {
        echo "   ✅ No OAuth users have force_pass_reset issues\n";
    }

    echo "\n";

    // 5. Final recommendations
    echo "5. FINAL RECOMMENDATIONS:\n";

    if (!$foundProblems && empty($invalidUserFields) && empty($invalidAsesiFields) && $usersWithForceReset == 0) {
        echo "   ✅ All checks passed! OAuth system should work correctly.\n";
        echo "   ✅ Test OAuth login in your browser now.\n";
    } else {
        echo "   ❌ Some issues found. Please address them before testing.\n";

        if ($foundProblems) {
            echo "   - Remove problematic field references from code\n";
        }
        if (!empty($invalidUserFields)) {
            echo "   - Fix invalid user fields: " . implode(', ', $invalidUserFields) . "\n";
        }
        if (!empty($invalidAsesiFields)) {
            echo "   - Fix invalid asesi fields: " . implode(', ', $invalidAsesiFields) . "\n";
        }
        if ($usersWithForceReset > 0) {
            echo "   - Run fix_oauth_users.php to fix existing OAuth users\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} finally {
    if ($db) {
        $db->close();
    }
}

echo "\n=== TEST COMPLETED ===\n";
