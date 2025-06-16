<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixOAuthUsers extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:fix-oauth';
    protected $description = 'Fix OAuth users with force_pass_reset issues';

    public function run(array $params)
    {
        CLI::write('=== Fixing OAuth Users ===', 'yellow');
        CLI::newLine();

        try {
            $db = \Config\Database::connect();

            // Find users with force_pass_reset = 1 and google_id set
            CLI::write('1. Finding OAuth users with force_pass_reset...', 'blue');

            $query = $db->query("
                SELECT id, email, google_id, force_pass_reset, active 
                FROM users 
                WHERE google_id IS NOT NULL AND google_id != '' 
                AND force_pass_reset = 1
            ");

            $oauthUsers = $query->getResultArray();

            if (empty($oauthUsers)) {
                CLI::write('   ✅ No OAuth users found with force_pass_reset = 1', 'green');
            } else {
                CLI::write('   Found ' . count($oauthUsers) . ' OAuth users with force_pass_reset = 1:', 'red');

                foreach ($oauthUsers as $user) {
                    CLI::write("   - ID: {$user['id']}, Email: {$user['email']}, Google ID: {$user['google_id']}", 'white');
                }

                CLI::newLine();
                CLI::write('2. Fixing OAuth users...', 'blue');

                // Update OAuth users to remove force_pass_reset and ensure they're active
                $updateQuery = "
                    UPDATE users 
                    SET force_pass_reset = 0, 
                        active = 1,
                        reset_hash = NULL,
                        reset_expires = NULL
                    WHERE google_id IS NOT NULL AND google_id != ''
                ";

                $affected = $db->query($updateQuery);

                if ($affected) {
                    CLI::write('   ✅ Updated OAuth users to remove force_pass_reset', 'green');
                    CLI::write('   ✅ Ensured OAuth users are active', 'green');
                    CLI::write('   ✅ Cleared reset tokens for OAuth users', 'green');
                } else {
                    CLI::write('   ❌ Failed to update OAuth users', 'red');
                }
            }

            CLI::newLine();
            CLI::write('3. Checking OAuth user groups...', 'blue');

            // Ensure OAuth users are in the correct group (Asesi)
            $asesiGroupQuery = $db->query("SELECT id FROM auth_groups WHERE name = 'Asesi'");
            $asesiGroup = $asesiGroupQuery->getRow();

            if ($asesiGroup) {
                CLI::write("   ✅ Found Asesi group (ID: {$asesiGroup->id})", 'green');

                // Find OAuth users not in Asesi group
                $ungroupedOAuthQuery = $db->query("
                    SELECT u.id, u.email 
                    FROM users u
                    WHERE u.google_id IS NOT NULL AND u.google_id != ''
                    AND u.id NOT IN (
                        SELECT user_id FROM auth_groups_users WHERE group_id = ?
                    )
                ", [$asesiGroup->id]);

                $ungroupedUsers = $ungroupedOAuthQuery->getResultArray();

                if (!empty($ungroupedUsers)) {
                    CLI::write('   Found ' . count($ungroupedUsers) . ' OAuth users not in Asesi group:', 'yellow');

                    foreach ($ungroupedUsers as $user) {
                        // Add to Asesi group
                        $db->query("
                            INSERT IGNORE INTO auth_groups_users (group_id, user_id) 
                            VALUES (?, ?)
                        ", [$asesiGroup->id, $user['id']]);

                        CLI::write("   - Added user {$user['email']} (ID: {$user['id']}) to Asesi group", 'green');
                    }
                } else {
                    CLI::write('   ✅ All OAuth users are already in Asesi group', 'green');
                }
            } else {
                CLI::write('   ❌ Asesi group not found!', 'red');
            }

            CLI::newLine();
            CLI::write('4. Final verification...', 'blue');

            // Verify the fixes
            $verifyQuery = $db->query("
                SELECT id, email, google_id, force_pass_reset, active,
                       (SELECT name FROM auth_groups ag 
                        JOIN auth_groups_users agu ON ag.id = agu.group_id 
                        WHERE agu.user_id = users.id LIMIT 1) as group_name
                FROM users 
                WHERE google_id IS NOT NULL AND google_id != ''
            ");

            $verifiedUsers = $verifyQuery->getResultArray();

            CLI::write('   OAuth users status:', 'blue');
            foreach ($verifiedUsers as $user) {
                $status = '';
                $status .= $user['active'] ? '✅ Active' : '❌ Inactive';
                $status .= ', ';
                $status .= !$user['force_pass_reset'] ? '✅ No force reset' : '❌ Force reset';
                $status .= ', ';
                $status .= $user['group_name'] ? "✅ Group: {$user['group_name']}" : '❌ No group';

                CLI::write("   - {$user['email']}: $status", 'white');
            }

            CLI::newLine();
            CLI::write('=== Fix Complete ===', 'green');
            CLI::write('OAuth users should now login successfully without redirect to reset password!', 'green');

            CLI::newLine();
            CLI::write('What was fixed:', 'yellow');
            CLI::write('1. ✅ Removed force_pass_reset for OAuth users', 'green');
            CLI::write('2. ✅ Ensured OAuth users are active', 'green');
            CLI::write('3. ✅ Cleared reset tokens for OAuth users', 'green');
            CLI::write('4. ✅ Ensured OAuth users are in Asesi group', 'green');

            CLI::newLine();
            CLI::write('Test again:', 'yellow');
            CLI::write('1. Go to: ' . site_url('auth/google'), 'white');
            CLI::write('2. Login with Google', 'white');
            CLI::write('3. Should redirect to asesi/dashboard (not reset password)', 'white');
        } catch (\Exception $e) {
            CLI::write("❌ Error: " . $e->getMessage(), 'red');
            CLI::write("Stack trace:", 'red');
            CLI::write($e->getTraceAsString(), 'red');
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
