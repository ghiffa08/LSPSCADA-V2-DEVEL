<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestOAuth extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:test-oauth';
    protected $description = 'Test OAuth login flow';

    public function run(array $params)
    {
        CLI::write('=== OAUTH LOGIN DEBUG TEST ===', 'yellow');
        CLI::newLine();

        try {
            // Simulate OAuth data that would come from Google
            $oauthData = [
                'email' => '20240810005@uniku.ac.id',
                'name' => 'Test OAuth User',
                'username' => '20240810005',
                'id' => '109028485755629112682', // Google ID from logs
                'role' => 'asesi'
            ];

            CLI::write('1. Testing AuthenticationService OAuth login...', 'blue');
            $authService = new \App\Services\Authentication\AuthenticationService();

            // Start session if needed
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            CLI::write('2. OAuth data:', 'blue');
            foreach ($oauthData as $key => $value) {
                CLI::write("   - $key: $value", 'white');
            }

            CLI::newLine();
            CLI::write('3. Attempting OAuth login...', 'blue');

            $authResponse = $authService->loginWithOAuth($oauthData, 'google');

            CLI::write('4. OAuth login result:', 'blue');
            CLI::write(
                '   - Success: ' . ($authResponse->isSuccess() ? "YES" : "NO"),
                $authResponse->isSuccess() ? 'green' : 'red'
            );
            CLI::write('   - Message: ' . $authResponse->getMessage(), 'white');

            if ($authResponse->isSuccess()) {
                CLI::write('   ✅ OAuth login successful!', 'green');

                $redirectUrl = $authResponse->getRedirectUrl();
                CLI::write('   - Redirect URL: ' . ($redirectUrl ?: 'NONE'), 'white');

                // Check if redirect is to reset-password
                if ($redirectUrl && strpos($redirectUrl, 'reset-password') !== false) {
                    CLI::write('   ❌ ERROR: Redirecting to reset-password! This should not happen for OAuth users.', 'red');
                    return EXIT_ERROR;
                } else {
                    CLI::write('   ✅ Redirect URL is correct (not reset-password)', 'green');
                }

                $user = $authResponse->user;
                if ($user) {
                    CLI::write('   - User ID: ' . $user->id, 'white');
                    CLI::write('   - User Email: ' . $user->email, 'white');
                    CLI::write('   - Google ID: ' . ($user->google_id ?? 'NULL'), 'white');
                    CLI::write(
                        '   - Force Pass Reset: ' . ($user->force_pass_reset ? 'TRUE' : 'FALSE'),
                        $user->force_pass_reset ? 'red' : 'green'
                    );
                    CLI::write(
                        '   - Active: ' . ($user->active ? 'TRUE' : 'FALSE'),
                        $user->active ? 'green' : 'red'
                    );
                }

                // Check session
                CLI::newLine();
                CLI::write('5. Session check:', 'blue');
                CLI::write('   - logged_in: ' . (session('logged_in') ?: 'NULL'), 'white');
                CLI::write('   - user_email: ' . (session('user_email') ?: 'NULL'), 'white');
                CLI::write('   - roles: ' . (session('roles') ? implode(', ', session('roles')) : 'NULL'), 'white');

                // Test helper functions
                CLI::newLine();
                CLI::write('6. Helper functions test:', 'blue');

                // Load auth helper manually for CLI and check if functions exist
                $helperPath = APPPATH . 'Helpers/auth_helper.php';
                if (file_exists($helperPath)) {
                    require_once $helperPath;
                    CLI::write('   Auth helper loaded from: ' . $helperPath, 'white');
                } else {
                    CLI::write('   ERROR: Auth helper not found at: ' . $helperPath, 'red');
                }

                $loggedInStatus = false;
                $currentUser = null;
                $inAsesiGroup = false;

                // Test logged_in function
                if (function_exists('logged_in')) {
                    try {
                        $loggedInStatus = logged_in();
                        CLI::write(
                            '   - logged_in(): ' . ($loggedInStatus ? 'TRUE' : 'FALSE'),
                            $loggedInStatus ? 'green' : 'red'
                        );
                    } catch (\Exception $e) {
                        CLI::write('   - logged_in(): ERROR - ' . $e->getMessage(), 'red');
                    }
                } else {
                    CLI::write('   - logged_in(): Function not found', 'red');
                }
                // Test user function
                if (function_exists('user')) {
                    try {
                        $currentUser = user();
                        if ($currentUser) {
                            CLI::write('   - user(): Found user ID ' . $currentUser->id, 'green');

                            // Test in_groups function
                            if (function_exists('in_groups')) {
                                try {
                                    $inAsesiGroup = in_groups('asesi');
                                    CLI::write(
                                        '   - in_groups(\'asesi\'): ' . ($inAsesiGroup ? 'TRUE' : 'FALSE'),
                                        $inAsesiGroup ? 'green' : 'red'
                                    );
                                } catch (\Exception $e) {
                                    CLI::write('   - in_groups(): ERROR - ' . $e->getMessage(), 'red');
                                }
                            } else {
                                CLI::write('   - in_groups(): Function not found', 'red');
                            }
                        } else {
                            CLI::write('   - user(): NULL', 'red');
                        }
                    } catch (\Exception $e) {
                        CLI::write('   - user(): ERROR - ' . $e->getMessage(), 'red');
                    }
                } else {
                    CLI::write('   - user(): Function not found', 'red');
                }

                CLI::newLine();
                CLI::write('✅ OAUTH LOGIN TEST PASSED!', 'green');
                CLI::write('OAuth user should be able to login without being redirected to reset-password.', 'green');
            } else {
                CLI::write('   ❌ OAuth login failed!', 'red');
                CLI::write('   - Error details: ' . json_encode($authResponse->getErrors()), 'red');
                return EXIT_ERROR;
            }
        } catch (\Exception $e) {
            CLI::write("❌ CRITICAL ERROR: " . $e->getMessage(), 'red');
            CLI::write("Stack trace:", 'red');
            CLI::write($e->getTraceAsString(), 'red');
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
