<?php

/**
 * Quick Setup Script for Authentication Testing
 * 
 * Script ini membantu setup cepat untuk testing authentication system
 */

echo "=== Quick Setup for Authentication Testing ===\n\n";

// Check if we're in the right directory
if (!file_exists('app/Config/Routes.php')) {
    echo "❌ Error: Please run this script from the LSPSCADA-V2-DEVEL root directory\n";
    exit(1);
}

echo "1. Checking file permissions...\n";

// Check critical files
$criticalFiles = [
    'app/Config/Routes.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/AuthControllerFixed.php',
    'app/Services/Authentication/AuthenticationService.php',
    'app/Controllers/OAuthController.php',
    '.env'
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file missing\n";
    }
}

echo "\n2. Checking .env configuration...\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');

    // Check OAuth settings
    $oauthSettings = [
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
        'GOOGLE_REDIRECT_URI'
    ];

    foreach ($oauthSettings as $setting) {
        if (strpos($envContent, $setting) !== false) {
            echo "   ✅ $setting found in .env\n";
        } else {
            echo "   ⚠️  $setting missing in .env\n";
        }
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "\n3. Sample .env OAuth configuration:\n";
echo "   Add these lines to your .env file:\n";
echo "   GOOGLE_CLIENT_ID=your_google_client_id_here\n";
echo "   GOOGLE_CLIENT_SECRET=your_google_client_secret_here\n";
echo "   GOOGLE_REDIRECT_URI=http://localhost/LSPSCADA-V2-DEVEL/OAuth/proses\n\n";

echo "4. Routes.php update needed:\n";
echo "   Replace AuthController references with AuthControllerFixed in app/Config/Routes.php\n";
echo "   Example:\n";
echo "   OLD: \$routes->get('login', 'AuthController::login', ['as' => 'login']);\n";
echo "   NEW: \$routes->get('login', 'AuthControllerFixed::login', ['as' => 'login']);\n\n";

echo "5. Database requirements:\n";
echo "   Make sure these tables exist with proper structure:\n";
echo "   - users (with google_id column for OAuth)\n";
echo "   - auth_groups\n";
echo "   - auth_groups_users\n";
echo "   - auth_logins\n";
echo "   - auth_tokens\n\n";

echo "6. Testing URLs:\n";
echo "   Manual Registration: http://localhost/LSPSCADA-V2-DEVEL/register\n";
echo "   Manual Login: http://localhost/LSPSCADA-V2-DEVEL/login\n";
echo "   Google OAuth: http://localhost/LSPSCADA-V2-DEVEL/auth/google\n";
echo "   Dashboard Router: http://localhost/LSPSCADA-V2-DEVEL/dashboard\n\n";

echo "7. Quick test commands:\n";
echo "   Check if AuthenticationService works:\n";
echo "   php -r \"require 'vendor/autoload.php'; echo 'AuthenticationService class exists: ' . (class_exists('App\\\\Services\\\\Authentication\\\\AuthenticationService') ? 'Yes' : 'No') . PHP_EOL;\"\n\n";

echo "=== Setup Complete ===\n";
echo "You can now proceed with manual testing using the URLs above.\n";
echo "Check MANUAL_TESTING_GUIDE.md for detailed testing steps.\n";
