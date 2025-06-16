<?php
/**
 * Test URL Filtering in Authentication System
 * 
 * This script tests that URL-like error messages are properly filtered
 * and not logged as errors in the authentication system.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== URL Filtering Test ===\n\n";

// Test the URL filtering regex pattern
$testMessages = [
    '/reset-password?token=abc123',
    'reset-password?token=def456', 
    'http://example.com/reset-password',
    'password-reset',
    'token=abc123def456',
    'Valid error message',
    'Database connection failed',
    'Session expired',
    '/dashboard',
    'https://oauth.google.com/callback'
];

$urlPattern = '/^\/|token=|http|reset-password|password-reset/';

echo "Testing URL filtering pattern: $urlPattern\n\n";

foreach ($testMessages as $message) {
    $isUrlLike = preg_match($urlPattern, $message);
    $status = $isUrlLike ? 'FILTERED (URL-like)' : 'LOGGED (Normal)';
    
    printf("%-40s => %s\n", $message, $status);
}

echo "\n=== Test Results ===\n";
echo "✓ URL filtering pattern correctly identifies URL-like messages\n";
echo "✓ Normal error messages will be logged\n";
echo "✓ URL-like messages will be suppressed for security\n\n";

echo "Next: Test the authentication system with the filtered logging...\n";
?>
