<?php

require_once 'vendor/autoload.php';

// Define FCPATH
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

try {
    // Load CodeIgniter
    $paths = new \Config\Paths();
    require_once SYSTEMPATH . 'bootstrap.php';
    $app = \Config\Services::codeigniter();
    $app->initialize();

    echo "=== TESTING OAUTH LOGIN FUNCTIONALITY ===\n";

    // Test OAuth data (simulating Google OAuth response)
    $testOAuthData = [
        'email' => 'test.oauth@gmail.com',
        'name' => 'OAuth Test User',
        'id' => 'google_' . uniqid(),
        'role' => 'asesi'
    ];

    echo "Test OAuth Data:\n";
    echo "  - Email: {$testOAuthData['email']}\n";
    echo "  - Name: {$testOAuthData['name']}\n";
    echo "  - Google ID: {$testOAuthData['id']}\n";
    echo "  - Role: {$testOAuthData['role']}\n\n";

    // Initialize AuthenticationService
    $authService = new \App\Services\Authentication\AuthenticationService();

    echo "=== TESTING OAUTH LOGIN (NEW USER) ===\n";

    // First, check if user already exists
    $userModel = model(\Myth\Auth\Models\UserModel::class);
    $existingUser = $userModel->where('email', $testOAuthData['email'])->first();

    if ($existingUser) {
        echo "User already exists, deleting for clean test...\n";
        $userModel->delete($existingUser->id, true); // Hard delete
        // Also delete from groups
        $groupUserModel = model(\App\Models\GroupUserModel::class);
        $groupUserModel->where('user_id', $existingUser->id)->delete();
    }

    // Try OAuth login (should auto-register)
    $authResponse = $authService->loginWithOAuth($testOAuthData, 'google');

    echo "OAuth Login Result:\n";
    echo "  - Success: " . ($authResponse->isSuccess() ? "✅ YES" : "❌ NO") . "\n";
    echo "  - Message: " . $authResponse->getMessage() . "\n";

    if (!$authResponse->isSuccess()) {
        echo "  - Errors: " . json_encode($authResponse->getErrors()) . "\n";
    } else {
        echo "  - User ID: " . $authResponse->user->id . "\n";
        echo "  - User Email: " . $authResponse->user->email . "\n";
        echo "  - User Name: " . $authResponse->user->nama_lengkap . "\n";
        echo "  - Google ID: " . ($authResponse->user->google_id ?? 'not set') . "\n";
        echo "  - Active: " . ($authResponse->user->active ? 'YES' : 'NO') . "\n";
        echo "  - Force Pass Reset: " . ($authResponse->user->force_pass_reset ? 'YES' : 'NO') . "\n";
        echo "  - Redirect URL: " . $authResponse->getRedirectUrl() . "\n";
    }

    echo "\n=== TESTING OAUTH LOGIN (EXISTING USER) ===\n";

    // Try OAuth login again (should login existing user)
    $authResponse2 = $authService->loginWithOAuth($testOAuthData, 'google');

    echo "Second OAuth Login Result:\n";
    echo "  - Success: " . ($authResponse2->isSuccess() ? "✅ YES" : "❌ NO") . "\n";
    echo "  - Message: " . $authResponse2->getMessage() . "\n";

    if ($authResponse2->isSuccess()) {
        echo "  - User ID: " . $authResponse2->user->id . "\n";
        echo "  - User Email: " . $authResponse2->user->email . "\n";
        echo "  - Google ID: " . ($authResponse2->user->google_id ?? 'not set') . "\n";
    }

    echo "\n=== CHECKING DATABASE ===\n";

    // Check database directly
    $db = \Config\Database::connect();
    $query = $db->query("SELECT id, email, username, nama_lengkap, google_id, active, force_pass_reset FROM users WHERE email = ?", [$testOAuthData['email']]);
    $user = $query->getRow();

    if ($user) {
        echo "User in database:\n";
        echo "  - ID: {$user->id}\n";
        echo "  - Email: {$user->email}\n";
        echo "  - Username: {$user->username}\n";
        echo "  - Name: {$user->nama_lengkap}\n";
        echo "  - Google ID: " . ($user->google_id ?? 'NULL') . "\n";
        echo "  - Active: " . ($user->active ? 'YES' : 'NO') . "\n";
        echo "  - Force Pass Reset: " . ($user->force_pass_reset ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ User not found in database\n";
    }

    echo "\n=== TEST COMPLETED ===\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
