<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
$pathsPath = realpath(__DIR__ . '/app/Config/Paths.php');
$paths = new \Config\Paths();
require realpath(__DIR__ . '/system/bootstrap.php');

$app = \Config\Services::codeigniter();
$app->initialize();

use App\Services\Authentication\AuthenticationService;

echo "=== TESTING OAUTH LOGIN WITH CORRECT USER TABLE FIELDS ===\n";

try {
    // Test 1: Check database connection and verify user table fields
    echo "1. Checking user table structure...\n";
    $db = \Config\Database::connect();
    $userFields = $db->getFieldNames('users');
    echo "   Available user fields: " . implode(', ', $userFields) . "\n";

    // Test 2: Check model allowedFields
    echo "\n2. Checking UserMythModel allowedFields...\n";
    $userModel = new \App\Models\UserMythModel();
    $reflection = new ReflectionClass($userModel);
    $allowedFieldsProperty = $reflection->getProperty('allowedFields');
    $allowedFieldsProperty->setAccessible(true);
    $allowedFields = $allowedFieldsProperty->getValue($userModel);
    echo "   Model allowed fields: " . implode(', ', $allowedFields) . "\n";

    // Check for invalid fields in model
    $invalidModelFields = array_diff($allowedFields, $userFields);
    if (empty($invalidModelFields)) {
        echo "   ✅ All model fields are valid\n";
    } else {
        echo "   ❌ Invalid model fields: " . implode(', ', $invalidModelFields) . "\n";
    }

    // Test 3: Try OAuth login
    echo "\n3. Testing OAuth login...\n";
    $authService = new AuthenticationService();

    $testOAuthData = [
        'email' => 'test.oauth.final.' . uniqid() . '@gmail.com',
        'name' => 'Test OAuth User Final',
        'id' => 'google_' . uniqid(),
        'username' => 'oauth_test_final_' . uniqid(),
        'role' => 'asesi'
    ];

    echo "   Testing with email: " . $testOAuthData['email'] . "\n";

    $authResponse = $authService->loginWithOAuth($testOAuthData, 'google');

    if ($authResponse->isSuccess()) {
        echo "   ✅ OAuth login successful!\n";
        echo "   - User ID: " . $authResponse->user->id . "\n";
        echo "   - Redirect URL: " . $authResponse->getRedirectUrl() . "\n";

        // Clean up test user
        $db->table('users')->where('email', $testOAuthData['email'])->delete();
        echo "   ✅ Test user cleaned up\n";
    } else {
        echo "   ❌ OAuth login failed: " . $authResponse->getMessage() . "\n";
        if (!empty($authResponse->getErrors())) {
            echo "   - Errors: " . json_encode($authResponse->getErrors()) . "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";

    if (strpos($e->getMessage(), 'tanggal_lahir') !== false) {
        echo "\n   🚨 FOUND THE PROBLEM: Code is trying to use 'tanggal_lahir' field!\n";
        echo "   This field does not exist in users table.\n";
    }
}

echo "\n=== TEST COMPLETED ===\n";
