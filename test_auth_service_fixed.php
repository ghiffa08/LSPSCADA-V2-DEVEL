<?php

// Test AuthenticationService after fixes
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$pathsPath = FCPATH . '../app/Config/Paths.php';
$paths = require realpath($pathsPath) ?: $pathsPath;
$app = new \CodeIgniter\CodeIgniter($paths);
$app->initialize();

echo "=== Testing AuthenticationService After Fixes ===\n\n";

try {
    // Test service initialization
    $authService = new \App\Services\Authentication\AuthenticationService();
    echo "✓ AuthenticationService initialized successfully\n";

    // Test OAuth data preparation
    $oauthData = [
        'email' => 'test.oauth@example.com',
        'name' => 'Test OAuth User',
        'username' => 'testoauth',
        'id' => 'oauth_test_123',
        'role' => 'asesi'
    ];

    echo "✓ OAuth data prepared\n";

    // Test user model
    $userModel = model(\Myth\Auth\Models\UserModel::class);
    $existingUser = $userModel->where('email', $oauthData['email'])->first();

    if ($existingUser) {
        echo "✓ Found existing OAuth test user (ID: {$existingUser->id})\n";

        // Test getCurrentUser method
        if ($authService->isAuthenticated()) {
            $currentUser = $authService->getCurrentUser();
            echo "✓ Current user retrieved: " . ($currentUser ? $currentUser->email : 'null') . "\n";
        } else {
            echo "! No user currently authenticated\n";
        }
    } else {
        echo "! No existing OAuth test user found\n";
    }

    // Test role checking
    $testUser = $userModel->find(4); // Test with user ID 4
    if ($testUser) {
        $userEntity = new \App\Entities\User((array)$testUser);
        echo "✓ Test user entity created for ID 4\n";
        echo "  - Email: {$userEntity->email}\n";
        echo "  - Is Admin: " . ($userEntity->isAdmin() ? 'Yes' : 'No') . "\n";
        echo "  - Is Asesor: " . ($userEntity->isAsesor() ? 'Yes' : 'No') . "\n";
        echo "  - Is Asesi: " . ($userEntity->isAsesi() ? 'Yes' : 'No') . "\n";

        // Test redirect URL determination
        $reflection = new ReflectionClass($authService);
        $method = $reflection->getMethod('determineRedirectUrl');
        $method->setAccessible(true);
        $redirectUrl = $method->invoke($authService, $userEntity);
        echo "  - Redirect URL: {$redirectUrl}\n";
    }

    echo "\n=== Test Complete ===\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
