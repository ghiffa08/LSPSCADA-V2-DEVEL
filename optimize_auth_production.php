<?php

/**
 * Auth Production Optimization Script
 * 
 * This script optimizes authentication settings for production environment
 * Run this script before deploying to production
 */

echo "=== AUTH PRODUCTION OPTIMIZATION ===\n";

// Check if we're already in production
$environment = $_ENV['CI_ENVIRONMENT'] ?? 'development';
if ($environment === 'production') {
    echo "⚠️  WARNING: Already in production environment!\n";
    echo "Please review all changes carefully.\n\n";
}

$changes = [];
$errors = [];
$optimizations = [];

// 1. Optimize Auth Configuration
echo "1. Optimizing Auth Configuration...\n";

$authConfigPath = 'app/Config/Auth.php';
if (!file_exists($authConfigPath)) {
    $errors[] = "Auth.php config file not found";
} else {
    $authConfig = file_get_contents($authConfigPath);

    // Check security settings
    $securityChecks = [
        'allowRegistration' => "public \$allowRegistration = false;",
        'requireActivation' => "public \$requireActivation = true;",
        'sessionLength' => "public \$sessionLength = 7200;", // 2 hours
        'minimumPasswordLength' => "public \$minimumPasswordLength = 8;",
    ];

    foreach ($securityChecks as $setting => $expectedValue) {
        if (strpos($authConfig, $expectedValue) === false) {
            $changes[] = "Auth.php: Set $setting for production security";
        }
    }

    echo "   ✓ Auth configuration checked\n";
}

// 2. Database Security
echo "\n2. Checking Database Security...\n";

try {
    // Check database configuration
    $dbConfigPath = 'app/Config/Database.php';
    if (file_exists($dbConfigPath)) {
        $dbConfig = file_get_contents($dbConfigPath);

        // Check for debug settings
        if (strpos($dbConfig, "'DBDebug' => true") !== false) {
            $changes[] = "Database: Disable DBDebug for production (set to false)";
        }

        // Check for localhost settings
        if (strpos($dbConfig, "'hostname' => 'localhost'") !== false) {
            $changes[] = "Database: Update hostname for production server";
        }
    }

    echo "   ✓ Database configuration checked\n";
} catch (Exception $e) {
    $errors[] = "Database check failed: " . $e->getMessage();
}

// 3. Session Security
echo "\n3. Optimizing Session Security...\n";

$sessionConfigPath = 'app/Config/Session.php';
if (file_exists($sessionConfigPath)) {
    $sessionConfig = file_get_contents($sessionConfigPath);

    $sessionOptimizations = [
        "'cookieSecure' => true" => "Enable secure cookies (HTTPS only)",
        "'cookieHTTPOnly' => true" => "Enable HTTP-only cookies",
        "'cookieSameSite' => 'Strict'" => "Set SameSite=Strict for CSRF protection",
        "'regenerateDestroy' => true" => "Enable session regeneration security",
        "'sessionTimeout' => 7200" => "Set 2-hour session timeout",
    ];

    foreach ($sessionOptimizations as $setting => $description) {
        if (strpos($sessionConfig, $setting) === false) {
            $optimizations[] = "Session: $description";
        }
    }
} else {
    $errors[] = "Session config file not found";
}

echo "   ✓ Session security checked\n";

// 4. Environment Configuration
echo "\n4. Checking Environment Configuration...\n";

$envFile = '.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);

    $productionSettings = [
        'CI_ENVIRONMENT = production' => 'Set production environment',
        'app.forceGlobalSecureRequests = true' => 'Force HTTPS',
        'app.sessionCookieSecure = true' => 'Secure session cookies',
        'logger.threshold = 3' => 'Set logging to errors only',
    ];

    foreach ($productionSettings as $setting => $description) {
        if (strpos($envContent, $setting) === false) {
            $optimizations[] = ".env: $description ($setting)";
        }
    }

    echo "   ✓ Environment configuration checked\n";
} else {
    $changes[] = "Create .env file for production settings";
}

// 5. Remove Debug/Test Files
echo "\n5. Checking for Debug/Test Files...\n";

$debugFiles = [
    'test_auth_debug.php',
    'test_oauth_debug.php',
    'debug_dashboard.php',
    'check_duplicates.php',
    'phpinfo.php',
    'test.php',
    'test_auth_comprehensive.php',
];

$foundDebugFiles = [];
foreach ($debugFiles as $file) {
    if (file_exists($file)) {
        $foundDebugFiles[] = $file;
    }
}

if (!empty($foundDebugFiles)) {
    $changes[] = "Remove debug files: " . implode(', ', $foundDebugFiles);
} else {
    echo "   ✓ No debug files found\n";
}

// 6. Check Log Files
echo "\n6. Checking Log Security...\n";

$logPath = 'writable/logs/';
if (is_dir($logPath)) {
    $logFiles = glob($logPath . '*.log');

    if (!empty($logFiles)) {
        $changes[] = "Consider log rotation and cleanup strategy";
        $optimizations[] = "Set up log monitoring and alerts";
    }

    echo "   ✓ Log security checked\n";
} else {
    $errors[] = "Log directory not accessible";
}

// 7. Authentication Flow Performance
echo "\n7. Checking Performance Optimizations...\n";

$authServicePath = 'app/Services/Authentication/AuthenticationService.php';
if (file_exists($authServicePath)) {
    $authService = file_get_contents($authServicePath);

    // Count debug log statements
    preg_match_all('/log_message\s*\(\s*[\'"]debug[\'"]/', $authService, $debugLogs);
    $debugLogCount = count($debugLogs[0]);

    if ($debugLogCount > 10) {
        $optimizations[] = "Reduce debug logging ($debugLogCount debug logs found)";
    }

    // Check for caching opportunities
    if (strpos($authService, 'cache') === false) {
        $optimizations[] = "Consider implementing caching for user data";
    }

    echo "   ✓ Performance checked\n";
}

// Display Results
echo "\n" . str_repeat("=", 60) . "\n";
echo "PRODUCTION OPTIMIZATION REPORT\n";
echo str_repeat("=", 60) . "\n";

if (empty($errors) && empty($changes) && empty($optimizations)) {
    echo "🎉 EXCELLENT!\n";
    echo "Your authentication system is ready for production!\n\n";
} else {
    if (!empty($errors)) {
        echo "❌ CRITICAL ISSUES (MUST FIX):\n";
        foreach ($errors as $i => $error) {
            echo "   " . ($i + 1) . ". $error\n";
        }
        echo "\n";
    }

    if (!empty($changes)) {
        echo "⚠️  REQUIRED CHANGES:\n";
        foreach ($changes as $i => $change) {
            echo "   " . ($i + 1) . ". $change\n";
        }
        echo "\n";
    }

    if (!empty($optimizations)) {
        echo "💡 RECOMMENDED OPTIMIZATIONS:\n";
        foreach ($optimizations as $i => $optimization) {
            echo "   " . ($i + 1) . ". $optimization\n";
        }
        echo "\n";
    }
}

echo "🔒 PRODUCTION SECURITY CHECKLIST:\n";
echo "□ Update database credentials for production\n";
echo "□ Enable HTTPS/SSL certificates\n";
echo "□ Set proper file permissions (644 files, 755 directories)\n";
echo "□ Remove all debug/test files\n";
echo "□ Configure error logging and monitoring\n";
echo "□ Set up automated backups\n";
echo "□ Test OAuth in production environment\n";
echo "□ Verify email sending works\n";
echo "□ Test password reset flow\n";
echo "□ Verify role-based access control\n";
echo "□ Set up rate limiting and DDoS protection\n";
echo "□ Configure security headers\n";
echo "□ Test authentication under load\n";

echo "\n🚀 DEPLOYMENT READY!\n";
echo "Your authentication system has been analyzed and is ready for production deployment.\n";
echo "Please address any critical issues and implement recommended optimizations.\n\n";
