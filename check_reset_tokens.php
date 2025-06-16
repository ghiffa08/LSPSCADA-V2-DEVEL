<?php
// Script untuk mengecek users yang butuh password reset tapi tidak punya token
require_once 'vendor/autoload.php';

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=lsp_scada_app_devel',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Checking Users with Password Reset Issues ===\n\n";

    // Check users with force_pass_reset = 1
    $stmt = $db->query("
        SELECT id, username, email, force_pass_reset, reset_hash, reset_expires, google_id
        FROM users 
        WHERE force_pass_reset = 1
        ORDER BY id
    ");
    
    $resetUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resetUsers) > 0) {
        echo "Users with force_pass_reset = 1:\n";
        echo "ID | Username | Email | Google ID | Reset Hash | Reset Expires\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($resetUsers as $user) {
            $googleId = $user['google_id'] ? 'Yes' : 'No';
            $resetHash = $user['reset_hash'] ? substr($user['reset_hash'], 0, 20) . '...' : 'NULL';
            $resetExpires = $user['reset_expires'] ?: 'NULL';
            
            echo sprintf(
                "%d | %s | %s | %s | %s | %s\n",
                $user['id'],
                $user['username'],
                $user['email'],
                $googleId,
                $resetHash,
                $resetExpires
            );
        }
        
        // Check for OAuth users with force_pass_reset = 1 (should be fixed)
        $oauthResetUsers = array_filter($resetUsers, function($user) {
            return !empty($user['google_id']);
        });
        
        if (count($oauthResetUsers) > 0) {
            echo "\n⚠️  OAuth users still have force_pass_reset = 1 (this should be fixed):\n";
            foreach ($oauthResetUsers as $user) {
                echo "- ID {$user['id']}: {$user['username']} ({$user['email']})\n";
            }
            
            echo "\nFix command:\n";
            echo "UPDATE users SET force_pass_reset = 0 WHERE google_id IS NOT NULL AND force_pass_reset = 1;\n";
        }
        
        // Check for users without valid reset tokens
        $noTokenUsers = array_filter($resetUsers, function($user) {
            return empty($user['reset_hash']) || empty($user['reset_expires']);
        });
        
        if (count($noTokenUsers) > 0) {
            echo "\n⚠️  Users need password reset but have no valid token:\n";
            foreach ($noTokenUsers as $user) {
                echo "- ID {$user['id']}: {$user['username']} ({$user['email']})\n";
            }
            
            echo "\nThese users will be redirected to login page instead of reset-password.\n";
        }
        
    } else {
        echo "✅ No users found with force_pass_reset = 1\n";
    }
    
    // Check total user counts
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as oauth FROM users WHERE google_id IS NOT NULL AND deleted_at IS NULL");
    $oauthUsers = $stmt->fetch(PDO::FETCH_ASSOC)['oauth'];
    
    echo "\nUser Statistics:\n";
    echo "Total active users: {$totalUsers}\n";
    echo "OAuth users: {$oauthUsers}\n";
    echo "Users needing password reset: " . count($resetUsers) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
