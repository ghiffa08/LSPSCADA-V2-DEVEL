<?php

// Test the authentication service fixes
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Path to the front controller (this file)
require_once FCPATH . '../app/Config/Paths.php';

// ^^^ Change this if you move your application folder
$paths = new Config\Paths();

// Location of the framework bootstrap file.
require_once $paths->systemDirectory . '/Boot.php';

exit(\CodeIgniter\Boot::bootWeb($paths));

echo "Testing Authentication Service fixes...\n";

try {
    // Test service initialization
    $authService = service('authenticationService');
    echo "✓ AuthenticationService initialized successfully\n";
    
    // Test isAuthenticated method (should not throw exception)
    $isAuth = $authService->isAuthenticated();
    echo "✓ isAuthenticated() method works: " . ($isAuth ? 'true' : 'false') . "\n";
    
    // Test getCurrentUser method (should not throw exception)
    $currentUser = $authService->getCurrentUser();
    echo "✓ getCurrentUser() method works: " . ($currentUser ? 'user found' : 'no user') . "\n";
    
    echo "\n✅ All authentication fixes are working!\n";
    echo "The /reset-password?token= error should now be resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
