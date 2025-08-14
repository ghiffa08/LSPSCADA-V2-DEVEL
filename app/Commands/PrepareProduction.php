<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PrepareProduction extends BaseCommand
{
    protected $group = 'auth';
    protected $name = 'auth:prepare-production';
    protected $description = 'Prepare application for production deployment';

    public function run(array $params)
    {
        CLI::write('=== LSP SCADA Production Preparation ===', 'yellow');
        CLI::newLine();

        // Check current environment
        CLI::write('Current Environment: ' . ENVIRONMENT, 'cyan');
        if (ENVIRONMENT === 'production') {
            CLI::write('✅ Already in production mode', 'green');
        } else {
            CLI::write('⚠️ Not in production mode - ensure to update .env', 'yellow');
        }
        CLI::newLine();

        // Check critical configs
        $this->checkConfigurations();

        // Check file permissions
        $this->checkPermissions();

        // Check database connection
        $this->checkDatabase();

        // Show recommendations
        $this->showRecommendations();

        CLI::newLine();
        CLI::write('✅ Production preparation check completed!', 'green');
    }
    private function checkConfigurations()
    {
        CLI::write('🔧 Checking Configurations...', 'yellow');

        try {
            // Check Auth config
            $authConfig = config('Auth');
            CLI::write('✅ Auth config loaded successfully', 'green');

            // Check Session config  
            $sessionConfig = config('Session');
            CLI::write('✅ Session config loaded successfully', 'green');
        } catch (\Exception $e) {
            CLI::write('❌ Config loading error: ' . $e->getMessage(), 'red');
        }

        CLI::newLine();
    }

    private function checkPermissions()
    {
        CLI::write('📁 Checking File Permissions...', 'yellow');

        $writablePaths = [
            'writable/cache',
            'writable/logs',
            'writable/session',
            'writable/uploads'
        ];

        foreach ($writablePaths as $path) {
            if (is_writable($path)) {
                CLI::write("✅ $path - writable", 'green');
            } else {
                CLI::write("❌ $path - not writable", 'red');
            }
        }

        CLI::newLine();
    }

    private function checkDatabase()
    {
        CLI::write('🗃️ Checking Database Connection...', 'yellow');

        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            CLI::write('✅ Database connection successful', 'green');

            // Check critical tables
            $tables = ['users', 'auth_groups', 'auth_groups_users'];
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    CLI::write("✅ Table '$table' exists", 'green');
                } else {
                    CLI::write("❌ Table '$table' missing", 'red');
                }
            }
        } catch (\Exception $e) {
            CLI::write('❌ Database connection failed: ' . $e->getMessage(), 'red');
        }

        CLI::newLine();
    }

    private function showRecommendations()
    {
        CLI::write('📋 Production Deployment Recommendations:', 'yellow');
        CLI::newLine();

        $recommendations = [
            '1. Copy .env.production to .env and update credentials',
            '2. Set CI_ENVIRONMENT=production in .env',
            '3. Update app.baseURL to your production domain',
            '4. Configure SSL certificate for HTTPS',
            '5. Set proper file permissions (755 dirs, 644 files)',
            '6. Configure web server (Apache/Nginx)',
            '7. Set up database backups',
            '8. Configure log rotation',
            '9. Test OAuth flow with production Google credentials',
            '10. Remove debug/test files from production server'
        ];

        foreach ($recommendations as $rec) {
            CLI::write($rec, 'cyan');
        }
    }
}
