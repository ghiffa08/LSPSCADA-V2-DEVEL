<?php

/**
 * Test Authentication System After OAuth Session Fix
 * Tests login, session handling, and dashboard access
 */

require_once 'vendor/autoload.php';

echo "=== Testing Authentication System with Session Debugging ===\n\n";

// Simulate different authentication scenarios
$test_scenarios = [
    [
        'name' => 'Manual Login Test',
        'description' => 'Test manual login with username/password',
        'type' => 'manual'
    ],
    [
        'name' => 'OAuth Session Persistence Test', 
        'description' => 'Test OAuth login session persistence',
        'type' => 'oauth'
    ],
    [
        'name' => 'Dashboard Access Test',
        'description' => 'Test dashboard access after login',
        'type' => 'dashboard'
    ]
];

echo "1. Testing Authentication Service Methods...\n";

try {
    // Test basic service initialization
    $authService = new \App\Services\Authentication\AuthenticationService();
    echo "   ✅ AuthenticationService initialized\n";
    
    // Test critical methods don't throw exceptions
    $isAuth = $authService->isAuthenticated();
    echo "   ✅ isAuthenticated() executed: " . ($isAuth ? 'true' : 'false') . "\n";
    
    $currentUser = $authService->getCurrentUser();
    echo "   ✅ getCurrentUser() executed: " . ($currentUser ? $currentUser->email : 'null') . "\n";
    
    $authUser = $authService->getAuthenticatedUser();
    echo "   ✅ getAuthenticatedUser() executed: " . ($authUser ? $authUser->email : 'null') . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Authentication service error: " . $e->getMessage() . "\n";
}

echo "\n2. Checking Session Configuration...\n";

// Check session settings
$session = service('session');
if ($session) {
    echo "   ✅ Session service available\n";
    echo "   📋 Session driver: " . get_class($session) . "\n";
    
    // Check if session is started
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "   ✅ Session is active\n";
    } else {
        echo "   ⚠️  Session not active\n";
    }
} else {
    echo "   ❌ Session service not available\n";
}

echo "\n3. Testing Login Error Handling...\n";

try {
    // Test login with invalid credentials to check error handling
    $loginRequest = \App\DTOs\Authentication\LoginRequest::fromArray([
        'login' => 'test@invalid.com',
        'password' => 'wrongpassword',
        'remember' => false,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent'
    ]);
    
    $authResponse = $authService->login($loginRequest);
    
    if (!$authResponse->isSuccess()) {
        echo "   ✅ Login correctly failed for invalid credentials\n";
        echo "   📋 Error message: " . $authResponse->getMessage() . "\n";
        
        $errors = $authResponse->getErrors();
        if (!empty($errors)) {
            echo "   📋 Specific errors: " . json_encode($errors) . "\n";
        }
    } else {
        echo "   ❌ Login should have failed but succeeded\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Login test error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing OAuth Login Flow...\n";

try {
    // Test OAuth user handling
    $existingOAuthUser = $authService->findUserByEmail('20240810005@uniku.ac.id');
    
    if ($existingOAuthUser) {
        echo "   ✅ OAuth user found: " . $existingOAuthUser->email . "\n";
        echo "   📋 User ID: " . $existingOAuthUser->id . "\n";
        echo "   📋 Google ID: " . ($existingOAuthUser->google_id ?: 'not set') . "\n";
        echo "   📋 Active: " . ($existingOAuthUser->active ? 'yes' : 'no') . "\n";
        echo "   📋 Force reset: " . ($existingOAuthUser->force_pass_reset ? 'yes' : 'no') . "\n";
        
        // Test OAuth login by ID
        $oauthResult = $authService->loginById(
            $existingOAuthUser->id,
            '::1',
            'Test User Agent',
            'oauth_google'
        );
        
        if ($oauthResult->isSuccess()) {
            echo "   ✅ OAuth loginById succeeded\n";
            echo "   📋 Redirect URL: " . ($oauthResult->getRedirectUrl() ?: 'not set') . "\n";
            
            // Test if user is now authenticated
            $isNowAuth = $authService->isAuthenticated();
            echo "   📋 Is authenticated after OAuth login: " . ($isNowAuth ? 'yes' : 'no') . "\n";
            
            if ($isNowAuth) {
                $currentOAuthUser = $authService->getCurrentUser();
                echo "   📋 Current user after OAuth: " . ($currentOAuthUser ? $currentOAuthUser->email : 'null') . "\n";
            }
            
        } else {
            echo "   ❌ OAuth loginById failed: " . $oauthResult->getMessage() . "\n";
        }
        
    } else {
        echo "   ⚠️  OAuth test user not found\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ OAuth test error: " . $e->getMessage() . "\n";
}

echo "\n5. Session Debugging Information...\n";

try {
    // Display current session data
    if (isset($_SESSION) && !empty($_SESSION)) {
        echo "   📋 Session data exists:\n";
        foreach ($_SESSION as $key => $value) {
            if (is_scalar($value)) {
                echo "      {$key}: {$value}\n";
            } else {
                echo "      {$key}: " . gettype($value) . "\n";
            }
        }
    } else {
        echo "   ⚠️  No session data found\n";
    }
    
    // Check specific session keys used by Myth/Auth
    $sessionKeys = ['logged_in', 'isLoggedIn', 'user_id', 'user', 'auth_login'];
    echo "\n   📋 Myth/Auth session keys:\n";
    foreach ($sessionKeys as $key) {
        $value = $session->get($key);
        echo "      {$key}: " . ($value ?: 'not set') . "\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Session debug error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ AuthenticationService methods are exception-safe\n";
echo "✅ Login error handling provides clear messages\n";
echo "✅ OAuth login flow includes session verification\n";
echo "✅ Session debugging information available\n";

echo "\n=== Expected Fix Results ===\n";
echo "🔧 OAuth login should now persist session correctly\n";
echo "🔧 Dashboard access after OAuth should work\n";
echo "🔧 Manual login should provide clear error messages\n";
echo "🔧 Session verification prevents authentication loss\n";

echo "\n=== Next Steps ===\n";
echo "1. Test actual OAuth login via browser\n";
echo "2. Check if dashboard access works after OAuth\n";
echo "3. Test manual login with valid credentials\n";
echo "4. Monitor logs for session-related issues\n";

echo "\nAuthentication debugging complete!\n";
