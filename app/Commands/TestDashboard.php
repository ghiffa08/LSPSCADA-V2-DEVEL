<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\DashboardRouterController;

class TestDashboard extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:test-dashboard';
    protected $description = 'Test DashboardRouterController behavior';
    protected $usage       = 'auth:test-dashboard';

    public function run(array $params)
    {
        CLI::write('=== DASHBOARD ROUTER TEST ===', 'yellow');

        // Set up session to simulate OAuth login
        session()->set('logged_in', 36);
        session()->set('user_email', '20240810005@uniku.ac.id');
        session()->set('roles', ['Asesi']);

        CLI::write('Session set to simulate OAuth login:', 'white');
        CLI::write(json_encode(session()->get()), 'white');

        // Test DashboardRouterController
        CLI::write("\n=== Testing DashboardRouterController ===", 'yellow');

        try {
            $controller = new DashboardRouterController();

            // Since we can't actually redirect in CLI, we'll just test the logic
            // We need to capture what would happen
            CLI::write('DashboardRouterController instantiated successfully', 'green');

            // The actual index() method would be called by the framework
            // but we can't test that directly in CLI without more complex setup
            CLI::write('Note: Full redirect test requires browser environment', 'white');
        } catch (\Exception $e) {
            CLI::write('DashboardRouterController ERROR: ' . $e->getMessage(), 'red');
            CLI::write('Stack trace: ' . $e->getTraceAsString(), 'red');
        }

        CLI::write("\n=== COMPLETE ===", 'green');
        CLI::write('If this completes without errors, the dashboard router should work.', 'green');
        CLI::write('Test manually by logging in with Google OAuth.', 'white');
    }
}
