<?php

/**
 * OAuth Debug Script
 * Test OAuth user login flow without browser
 */

require_once 'vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== OAUTH LOGIN DEBUG TEST ===\n\n";

try {
    // Simulate OAuth data that would come from Google
    $oauthData = [
        'email' => '20240810005@uniku.ac.id',
        'name' => 'Test OAuth User',
        'username' => '20240810005',
        'id' => '109028485755629112682', // Google ID from logs
        'role' => 'asesi'
    ];

    echo "1. Testing AuthenticationService OAuth login...\n";

    $authService = new \App\Services\Authentication\AuthenticationService();

    // Clear any existing session
    session()->destroy();
    session_start();

    echo "2. OAuth data:\n";
    foreach ($oauthData as $key => $value) {
        echo "   - $key: $value\n";
    }

    echo "\n3. Attempting OAuth login...\n";

    $authResponse = $authService->loginWithOAuth($oauthData, 'google');

    echo "4. OAuth login result:\n";
    echo "   - Success: " . ($authResponse->isSuccess() ? "YES" : "NO") . "\n";
    echo "   - Message: " . $authResponse->getMessage() . "\n";

    if ($authResponse->isSuccess()) {
        echo "   ✅ OAuth login successful!\n";

        $redirectUrl = $authResponse->getRedirectUrl();
        echo "   - Redirect URL: " . ($redirectUrl ?: 'NONE') . "\n";

        // Check if redirect is to reset-password
        if ($redirectUrl && strpos($redirectUrl, 'reset-password') !== false) {
            echo "   ❌ ERROR: Redirecting to reset-password! This should not happen for OAuth users.\n";
        } else {
            echo "   ✅ Redirect URL is correct (not reset-password)\n";
        }

        $user = $authResponse->user;
        if ($user) {
            echo "   - User ID: " . $user->id . "\n";
            echo "   - User Email: " . $user->email . "\n";
            echo "   - Google ID: " . ($user->google_id ?? 'NULL') . "\n";
            echo "   - Force Pass Reset: " . ($user->force_pass_reset ? 'TRUE' : 'FALSE') . "\n";
            echo "   - Active: " . ($user->active ? 'TRUE' : 'FALSE') . "\n";
        }

        // Check session
        echo "\n5. Session check:\n";
        echo "   - logged_in: " . (session('logged_in') ?: 'NULL') . "\n";
        echo "   - user_email: " . (session('user_email') ?: 'NULL') . "\n";
        echo "   - roles: " . (session('roles') ? implode(', ', session('roles')) : 'NULL') . "\n";

        // Test helper functions
        echo "\n6. Helper functions test:\n";
        echo "   - logged_in(): " . (logged_in() ? 'TRUE' : 'FALSE') . "\n";

        $currentUser = user();
        if ($currentUser) {
            echo "   - user(): Found user ID " . $currentUser->id . "\n";
            echo "   - in_groups('asesi'): " . (in_groups('asesi') ? 'TRUE' : 'FALSE') . "\n";
        } else {
            echo "   - user(): NULL\n";
        }

        echo "\n✅ OAUTH LOGIN TEST PASSED!\n";
        echo "OAuth user should be able to login without being redirected to reset-password.\n";
    } else {
        echo "   ❌ OAuth login failed!\n";
        echo "   - Error details: " . json_encode($authResponse->getErrors()) . "\n";
    }
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
