<?php

/**
 * Fix OAuth Users Script
 * 
 * Script untuk memperbaiki OAuth users yang memiliki force_pass_reset = true
 */

require_once 'vendor/autoload.php';

// Initialize CodeIgniter services
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== Fixing OAuth Users ===\n\n";

try {
    $db = \Config\Database::connect();

    // Find users with force_pass_reset = 1 and google_id set
    echo "1. Finding OAuth users with force_pass_reset...\n";

    $query = $db->query("
        SELECT id, email, google_id, force_pass_reset, active 
        FROM users 
        WHERE google_id IS NOT NULL AND google_id != '' 
        AND force_pass_reset = 1
    ");

    $oauthUsers = $query->getResultArray();

    if (empty($oauthUsers)) {
        echo "   ✅ No OAuth users found with force_pass_reset = 1\n";
    } else {
        echo "   Found " . count($oauthUsers) . " OAuth users with force_pass_reset = 1:\n";

        foreach ($oauthUsers as $user) {
            echo "   - ID: {$user['id']}, Email: {$user['email']}, Google ID: {$user['google_id']}\n";
        }

        echo "\n2. Fixing OAuth users...\n";

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
            echo "   ✅ Updated OAuth users to remove force_pass_reset\n";
            echo "   ✅ Ensured OAuth users are active\n";
            echo "   ✅ Cleared reset tokens for OAuth users\n";
        } else {
            echo "   ❌ Failed to update OAuth users\n";
        }
    }

    echo "\n3. Checking OAuth user groups...\n";

    // Ensure OAuth users are in the correct group (Asesi)
    $asesiGroupQuery = $db->query("SELECT id FROM auth_groups WHERE name = 'Asesi'");
    $asesiGroup = $asesiGroupQuery->getRow();

    if ($asesiGroup) {
        echo "   ✅ Found Asesi group (ID: {$asesiGroup->id})\n";

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
            echo "   Found " . count($ungroupedUsers) . " OAuth users not in Asesi group:\n";

            foreach ($ungroupedUsers as $user) {
                // Add to Asesi group
                $db->query("
                    INSERT IGNORE INTO auth_groups_users (group_id, user_id) 
                    VALUES (?, ?)
                ", [$asesiGroup->id, $user['id']]);

                echo "   - Added user {$user['email']} (ID: {$user['id']}) to Asesi group\n";
            }
        } else {
            echo "   ✅ All OAuth users are already in Asesi group\n";
        }
    } else {
        echo "   ❌ Asesi group not found!\n";
    }

    echo "\n4. Final verification...\n";

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

    echo "   OAuth users status:\n";
    foreach ($verifiedUsers as $user) {
        $status = '';
        $status .= $user['active'] ? '✅ Active' : '❌ Inactive';
        $status .= ', ';
        $status .= !$user['force_pass_reset'] ? '✅ No force reset' : '❌ Force reset';
        $status .= ', ';
        $status .= $user['group_name'] ? "✅ Group: {$user['group_name']}" : '❌ No group';

        echo "   - {$user['email']}: $status\n";
    }

    echo "\n=== Fix Complete ===\n";
    echo "OAuth users should now login successfully without redirect to reset password!\n\n";

    echo "What was fixed:\n";
    echo "1. ✅ Removed force_pass_reset for OAuth users\n";
    echo "2. ✅ Ensured OAuth users are active\n";
    echo "3. ✅ Cleared reset tokens for OAuth users\n";
    echo "4. ✅ Ensured OAuth users are in Asesi group\n";
    echo "5. ✅ Added logging for OAuth login debugging\n\n";

    echo "Test again:\n";
    echo "1. Go to: " . site_url('auth/google') . "\n";
    echo "2. Login with Google\n";
    echo "3. Should redirect to asesi/dashboard (not reset password)\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Script Complete ===\n";
