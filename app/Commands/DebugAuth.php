<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\Authentication\AuthenticationService;

class DebugAuth extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:debug';
    protected $description = 'Debug authentication methods';
    protected $usage       = 'auth:debug';

    public function run(array $params)
    {
        CLI::write('=== AUTHENTICATION DEBUG ===', 'yellow');

        // Simulate session data from OAuth
        session()->set('logged_in', 36);
        session()->set('user_email', '20240810005@uniku.ac.id');
        session()->set('roles', ['Asesi']);

        CLI::write('Session set:', 'white');
        CLI::write(json_encode(session()->get()), 'white');

        // Test AuthenticationService methods
        $authService = new AuthenticationService();

        CLI::write("\n=== Testing isAuthenticated() ===", 'yellow');
        try {
            $isAuth = $authService->isAuthenticated();
            CLI::write('isAuthenticated(): ' . ($isAuth ? 'TRUE' : 'FALSE'), $isAuth ? 'green' : 'red');
        } catch (\Exception $e) {
            CLI::write('isAuthenticated() ERROR: ' . $e->getMessage(), 'red');
        }

        CLI::write("\n=== Testing getCurrentUser() ===", 'yellow');
        try {
            $user = $authService->getCurrentUser();
            if ($user) {
                CLI::write('getCurrentUser(): Found user ID ' . $user->id, 'green');
                CLI::write('User email: ' . ($user->email ?? 'N/A'), 'white');
                CLI::write('User class: ' . get_class($user), 'white');

                // Test role methods
                CLI::write("\n=== Testing User Role Methods ===", 'yellow');
                try {
                    $isAdmin = $user->isAdmin();
                    $isAsesor = $user->isAsesor();
                    $isAsesi = $user->isAsesi();

                    CLI::write('isAdmin(): ' . ($isAdmin ? 'TRUE' : 'FALSE'), 'white');
                    CLI::write('isAsesor(): ' . ($isAsesor ? 'TRUE' : 'FALSE'), 'white');
                    CLI::write('isAsesi(): ' . ($isAsesi ? 'TRUE' : 'FALSE'), 'white');
                } catch (\Exception $e) {
                    CLI::write('Role methods ERROR: ' . $e->getMessage(), 'red');
                }
            } else {
                CLI::write('getCurrentUser(): NULL', 'red');
            }
        } catch (\Exception $e) {
            CLI::write('getCurrentUser() ERROR: ' . $e->getMessage(), 'red');
        }

        // Test Myth/Auth directly
        CLI::write("\n=== Testing Myth/Auth Directly ===", 'yellow');
        try {
            $mythAuth = service('authentication');
            $mythCheck = $mythAuth->check();
            CLI::write('Myth/Auth check(): ' . ($mythCheck ? 'TRUE' : 'FALSE'), $mythCheck ? 'green' : 'red');

            if ($mythCheck) {
                $mythUser = $mythAuth->user();
                CLI::write('Myth/Auth user ID: ' . ($mythUser->id ?? 'N/A'), 'white');
                CLI::write('Myth/Auth user class: ' . get_class($mythUser), 'white');
            }
        } catch (\Exception $e) {
            CLI::write('Myth/Auth ERROR: ' . $e->getMessage(), 'red');
        }

        CLI::write("\n=== COMPLETE ===", 'green');
    }
}
