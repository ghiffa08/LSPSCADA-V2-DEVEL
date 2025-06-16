<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter framework
$app = \Config\Services::codeigniter();
$app->initialize();

use App\DTOs\Authentication\RegisterRequest;
use App\Services\Authentication\AuthenticationService;
use CodeIgniter\Database\Config;

echo "=== FINAL OAUTH SCHEMA FIX TEST ===\n";
echo "Testing with ONLY fields that exist in users table\n\n";

// Test 1: Check database connection and users table structure
echo "1. Testing database connection and users table structure...\n";
try {
    $db = Config::connect();
    $fields = $db->getFieldNames('users');
    echo "   ✅ Connected to database\n";
    echo "   ✅ Users table fields: " . implode(', ', $fields) . "\n";

    // Verify required fields exist
    $requiredFields = ['id', 'email', 'username', 'password_hash', 'nama_lengkap', 'google_id', 'active', 'force_pass_reset'];
    $missingFields = array_diff($requiredFields, $fields);

    if (empty($missingFields)) {
        echo "   ✅ All required fields present\n";
    } else {
        echo "   ❌ Missing fields: " . implode(', ', $missingFields) . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test RegisterRequest with OAuth data
echo "\n2. Testing RegisterRequest with OAuth data...\n";
try {
    $testOAuthData = [
        'email' => 'test.oauth.' . uniqid() . '@gmail.com',
        'name' => 'Test OAuth User',
        'id' => 'google_' . uniqid()
    ];

    $registerData = [
        'email' => $testOAuthData['email'],
        'username' => 'oauth_test_' . uniqid(),
        'fullname' => $testOAuthData['name'],
        'password' => 'temp_oauth_password_123',
        'pass_confirm' => 'temp_oauth_password_123',
        'group' => 'asesi',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'additional_data' => [
            'google_id' => $testOAuthData['id']
        ]
    ];

    $registerRequest = RegisterRequest::fromArray($registerData);
    echo "   ✅ RegisterRequest created successfully\n";

    // Test validation
    $errors = $registerRequest->validate();
    if (empty($errors)) {
        echo "   ✅ Validation passed\n";
    } else {
        echo "   ❌ Validation errors: " . json_encode($errors) . "\n";
    }

    // Test toUserArray
    $userArray = $registerRequest->toUserArray();
    echo "   ✅ toUserArray() executed successfully\n";
    echo "   - Fields: " . implode(', ', array_keys($userArray)) . "\n";

    // Check if any invalid fields
    $invalidFields = array_diff(array_keys($userArray), $fields);
    if (empty($invalidFields)) {
        echo "   ✅ All fields in toUserArray() exist in database\n";
    } else {
        echo "   ❌ Invalid fields in toUserArray(): " . implode(', ', $invalidFields) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ RegisterRequest error: " . $e->getMessage() . "\n";
}

// Test 3: Test AuthenticationService OAuth login
echo "\n3. Testing AuthenticationService OAuth login...\n";
try {
    $authService = new AuthenticationService();

    $testOAuthData = [
        'email' => 'test.oauth2.' . uniqid() . '@gmail.com',
        'name' => 'Test OAuth User 2',
        'id' => 'google_' . uniqid(),
        'username' => 'oauth_test2_' . uniqid(),
        'role' => 'asesi'
    ];

    echo "   ✅ AuthenticationService created\n";
    echo "   ✅ Test OAuth data prepared\n";
    echo "   - Email: " . $testOAuthData['email'] . "\n";
    echo "   - Google ID: " . $testOAuthData['id'] . "\n";

    // This will test the full OAuth login flow
    $authResponse = $authService->loginWithOAuth($testOAuthData, 'google');

    if ($authResponse->isSuccess()) {
        echo "   ✅ OAuth login successful\n";
        echo "   - User ID: " . $authResponse->user->id . "\n";
        echo "   - Redirect URL: " . $authResponse->getRedirectUrl() . "\n";

        // Clean up - delete test user
        $db->table('users')->where('email', $testOAuthData['email'])->delete();
        echo "   ✅ Test user cleaned up\n";
    } else {
        echo "   ❌ OAuth login failed: " . $authResponse->getMessage() . "\n";
        echo "   - Errors: " . json_encode($authResponse->getErrors()) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ AuthenticationService error: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
}

// Test 4: Check for field references in code
echo "\n4. Checking for invalid field references...\n";
$codeFiles = [
    'app/Services/Authentication/AuthenticationService.php',
    'app/DTOs/Authentication/RegisterRequest.php'
];

$invalidFieldReferences = [
    'oauthProvider',
    'oauthId',
    'oauth_provider',
    'isOAuthUser',
    'no_hp'
];

$foundInvalidRefs = false;
foreach ($codeFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($invalidFieldReferences as $invalidField) {
            if (strpos($content, $invalidField) !== false) {
                echo "   ❌ Found invalid field reference '$invalidField' in $file\n";
                $foundInvalidRefs = true;
            }
        }
    }
}

if (!$foundInvalidRefs) {
    echo "   ✅ No invalid field references found\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "If all tests pass, the OAuth system should work correctly with your database schema.\n";
