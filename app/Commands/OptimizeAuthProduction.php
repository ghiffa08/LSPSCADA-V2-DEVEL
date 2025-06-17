<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class OptimizeAuthProduction extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:optimize-production';
    protected $description = 'Optimize authentication system for production deployment';
    protected $usage       = 'auth:optimize-production [options]';
    protected $arguments   = [];
    protected $options     = [
        '--apply' => 'Actually apply the optimizations (use with caution)',
        '--backup' => 'Create backup of modified files',
    ];

    public function run(array $params)
    {
        CLI::write('🔧 PRODUCTION AUTHENTICATION OPTIMIZATION', 'yellow');
        CLI::write('==========================================', 'yellow');

        $applyChanges = array_key_exists('apply', $params) || CLI::getOption('apply');
        $createBackup = array_key_exists('backup', $params) || CLI::getOption('backup');

        if ($applyChanges) {
            CLI::write('⚠️  APPLYING CHANGES MODE - Files will be modified!', 'red');
            if (!CLI::prompt('Are you sure you want to continue?', ['y', 'n']) === 'y') {
                CLI::write('Operation cancelled.', 'yellow');
                return;
            }
        } else {
            CLI::write('🔍 ANALYSIS MODE - No files will be modified', 'green');
        }

        $optimizations = [];
        $critical = [];

        // 1. Optimize Auth Configuration
        CLI::write("\n1. 🔐 Auth Configuration", 'blue');
        $this->optimizeAuthConfig($applyChanges, $createBackup, $optimizations, $critical);

        // 2. Optimize Session Configuration  
        CLI::write("\n2. 🍪 Session Configuration", 'blue');
        $this->optimizeSessionConfig($applyChanges, $createBackup, $optimizations, $critical);

        // 3. Optimize Database Configuration
        CLI::write("\n3. 🗄️  Database Configuration", 'blue');
        $this->optimizeDatabaseConfig($applyChanges, $createBackup, $optimizations, $critical);

        // 4. Clean Debug Files
        CLI::write("\n4. 🧹 Debug Files Cleanup", 'blue');
        $this->cleanDebugFiles($applyChanges, $optimizations, $critical);

        // 5. Optimize Logging
        CLI::write("\n5. 📝 Logging Optimization", 'blue');
        $this->optimizeLogging($applyChanges, $createBackup, $optimizations, $critical);

        // Display Summary
        $this->displaySummary($optimizations, $critical, $applyChanges);
    }

    private function optimizeAuthConfig(bool $apply, bool $backup, array &$optimizations, array &$critical): void
    {
        $authConfigPath = APPPATH . 'Config/Auth.php';

        if (!file_exists($authConfigPath)) {
            $critical[] = 'Auth.php configuration file not found';
            CLI::write('   ❌ Auth.php not found', 'red');
            return;
        }

        $content = file_get_contents($authConfigPath);
        $modified = false;

        // Production security settings
        $settings = [
            'allowRegistration' => false,
            'requireActivation' => true,
            'sessionLength' => 7200, // 2 hours
            'minimumPasswordLength' => 8,
        ];

        foreach ($settings as $setting => $value) {
            $pattern = "/public \\\$" . preg_quote($setting) . "\s*=\s*[^;]+;/";
            $replacement = "public \$" . $setting . " = " . ($value === true ? 'true' : ($value === false ? 'false' : $value)) . ";";

            if (!preg_match($pattern, $content)) {
                $optimizations[] = "Auth: Set $setting for production security";
                CLI::write("   📝 Will optimize: $setting", 'yellow');

                if ($apply) {
                    // Apply the change
                    $content = preg_replace($pattern, $replacement, $content);
                    $modified = true;
                }
            } else {
                CLI::write("   ✓ $setting already configured", 'green');
            }
        }

        if ($modified && $apply) {
            if ($backup) {
                copy($authConfigPath, $authConfigPath . '.backup.' . date('Y-m-d-H-i-s'));
                CLI::write('   💾 Backup created', 'cyan');
            }

            file_put_contents($authConfigPath, $content);
            CLI::write('   ✅ Auth.php optimized', 'green');
        }
    }

    private function optimizeSessionConfig(bool $apply, bool $backup, array &$optimizations, array &$critical): void
    {
        $sessionConfigPath = APPPATH . 'Config/Session.php';

        if (!file_exists($sessionConfigPath)) {
            $critical[] = 'Session.php configuration file not found';
            CLI::write('   ❌ Session.php not found', 'red');
            return;
        }

        $content = file_get_contents($sessionConfigPath);
        $recommendations = [
            'cookieSecure' => 'Enable secure cookies (HTTPS)',
            'cookieHTTPOnly' => 'Enable HTTP-only cookies',
            'cookieSameSite' => 'Set SameSite for CSRF protection',
        ];

        foreach ($recommendations as $setting => $description) {
            if (strpos($content, $setting) !== false) {
                CLI::write("   ✓ $setting found", 'green');
            } else {
                $optimizations[] = "Session: $description";
                CLI::write("   📝 Recommendation: $description", 'yellow');
            }
        }
    }

    private function optimizeDatabaseConfig(bool $apply, bool $backup, array &$optimizations, array &$critical): void
    {
        $dbConfigPath = APPPATH . 'Config/Database.php';

        if (!file_exists($dbConfigPath)) {
            $critical[] = 'Database.php configuration file not found';
            CLI::write('   ❌ Database.php not found', 'red');
            return;
        }

        $content = file_get_contents($dbConfigPath);

        // Check for debug mode
        if (strpos($content, "'DBDebug' => true") !== false) {
            $optimizations[] = 'Database: Disable DBDebug for production';
            CLI::write('   📝 Should disable DBDebug for production', 'yellow');
        } else {
            CLI::write('   ✓ DBDebug configuration OK', 'green');
        }

        // Check for localhost
        if (strpos($content, "'hostname' => 'localhost'") !== false) {
            $optimizations[] = 'Database: Update hostname for production server';
            CLI::write('   📝 Update hostname for production', 'yellow');
        } else {
            CLI::write('   ✓ Database hostname configuration OK', 'green');
        }
    }

    private function cleanDebugFiles(bool $apply, array &$optimizations, array &$critical): void
    {
        $debugFiles = [
            'test_auth_debug.php',
            'test_oauth_debug.php',
            'debug_dashboard.php',
            'check_duplicates.php',
            'phpinfo.php',
            'test.php',
            'test_auth_comprehensive.php',
        ];

        $foundFiles = [];
        foreach ($debugFiles as $file) {
            if (file_exists(ROOTPATH . $file)) {
                $foundFiles[] = $file;
            }
        }

        if (!empty($foundFiles)) {
            foreach ($foundFiles as $file) {
                CLI::write("   📝 Found debug file: $file", 'yellow');
                if ($apply) {
                    if (unlink(ROOTPATH . $file)) {
                        CLI::write("   🗑️  Removed: $file", 'green');
                    } else {
                        CLI::write("   ❌ Failed to remove: $file", 'red');
                    }
                }
            }

            if (!$apply) {
                $optimizations[] = 'Cleanup: Remove ' . count($foundFiles) . ' debug files';
            }
        } else {
            CLI::write('   ✓ No debug files found', 'green');
        }
    }

    private function optimizeLogging(bool $apply, bool $backup, array &$optimizations, array &$critical): void
    {
        $authServicePath = APPPATH . 'Services/Authentication/AuthenticationService.php';

        if (!file_exists($authServicePath)) {
            $critical[] = 'AuthenticationService not found';
            CLI::write('   ❌ AuthenticationService not found', 'red');
            return;
        }

        $content = file_get_contents($authServicePath);

        // Count debug logs
        preg_match_all('/log_message\s*\(\s*[\'"]debug[\'"]/', $content, $debugLogs);
        $debugCount = count($debugLogs[0]);

        if ($debugCount > 10) {
            $optimizations[] = "Logging: Reduce debug logging ($debugCount debug logs found)";
            CLI::write("   📝 Consider reducing debug logging ($debugCount found)", 'yellow');
        } else {
            CLI::write("   ✓ Debug logging level acceptable ($debugCount)", 'green');
        }

        // Check for error handling
        if (strpos($content, 'try {') !== false && strpos($content, 'catch') !== false) {
            CLI::write('   ✓ Exception handling present', 'green');
        } else {
            $critical[] = 'Missing exception handling in AuthenticationService';
            CLI::write('   ❌ Missing exception handling', 'red');
        }
    }

    private function displaySummary(array $optimizations, array $critical, bool $applied): void
    {
        CLI::write("\n" . str_repeat('=', 50), 'white');
        CLI::write('📊 OPTIMIZATION SUMMARY', 'cyan');
        CLI::write(str_repeat('=', 50), 'white');

        if (!empty($critical)) {
            CLI::write("\n❌ CRITICAL ISSUES:", 'red');
            foreach ($critical as $i => $issue) {
                CLI::write("   " . ($i + 1) . ". $issue", 'red');
            }
        }

        if (!empty($optimizations)) {
            $status = $applied ? 'APPLIED' : 'RECOMMENDED';
            CLI::write("\n💡 OPTIMIZATIONS $status:", 'yellow');
            foreach ($optimizations as $i => $optimization) {
                CLI::write("   " . ($i + 1) . ". $optimization", 'yellow');
            }
        }

        if (empty($critical) && empty($optimizations)) {
            CLI::write("\n🎉 EXCELLENT!", 'green');
            CLI::write("Your authentication system is production-ready!", 'green');
        }

        CLI::write("\n🔒 PRODUCTION DEPLOYMENT CHECKLIST:", 'cyan');
        $checklist = [
            'Update database credentials for production',
            'Enable HTTPS/SSL certificates',
            'Set proper file permissions (644/755)',
            'Configure environment variables (.env)',
            'Set up log rotation and monitoring',
            'Test OAuth in production environment',
            'Verify email functionality',
            'Test authentication flows',
            'Set up rate limiting',
            'Configure security headers'
        ];

        foreach ($checklist as $i => $item) {
            CLI::write("   □ $item", 'white');
        }

        CLI::write("\n🚀 Ready for production deployment!", 'green');
    }
}
