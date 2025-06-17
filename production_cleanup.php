<?php

/**
 * Production Cleanup Script
 * Removes debug files and optimizes for production deployment
 */

echo "=== LSP SCADA Production Cleanup ===" . PHP_EOL;

// Files to remove for production
$filesToRemove = [
    'quick_auth_test.php',
    'validate_auth_fix.php',
    'optimize_auth_production.php',
    'test_api.html',
    'AUTH_SYSTEM_FIXED.md',
    'AUTHENTICATION_FIX_COMPLETE.md',
    'DASHBOARD_FIX_REPORT.md',
    'OAUTH_FIX_SUMMARY.md',
];

$removedFiles = [];
$notFoundFiles = [];

foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            $removedFiles[] = $file;
        } else {
            echo "❌ Failed to remove: $file" . PHP_EOL;
        }
    } else {
        $notFoundFiles[] = $file;
    }
}

echo PHP_EOL . "✅ Removed files (" . count($removedFiles) . "):" . PHP_EOL;
foreach ($removedFiles as $file) {
    echo "  ✓ $file" . PHP_EOL;
}

if (!empty($notFoundFiles)) {
    echo PHP_EOL . "ℹ️ Files not found (" . count($notFoundFiles) . "):" . PHP_EOL;
    foreach ($notFoundFiles as $file) {
        echo "  - $file" . PHP_EOL;
    }
}

// Clear logs
$logPath = 'writable/logs/';
if (is_dir($logPath)) {
    $logFiles = glob($logPath . '*.log');
    $clearedLogs = 0;

    foreach ($logFiles as $logFile) {
        if (file_put_contents($logFile, '') !== false) {
            $clearedLogs++;
        }
    }

    echo PHP_EOL . "🗑️ Cleared $clearedLogs log files" . PHP_EOL;
}

// Clear session files
$sessionPath = 'writable/session/';
if (is_dir($sessionPath)) {
    $sessionFiles = glob($sessionPath . 'ci_session*');
    foreach ($sessionFiles as $sessionFile) {
        unlink($sessionFile);
    }
    echo "🗑️ Cleared session files" . PHP_EOL;
}

// Clear cache
$cachePath = 'writable/cache/';
if (is_dir($cachePath)) {
    $cacheFiles = glob($cachePath . '*');
    $clearedCache = 0;

    foreach ($cacheFiles as $cacheFile) {
        if (is_file($cacheFile) && unlink($cacheFile)) {
            $clearedCache++;
        }
    }

    echo "🗑️ Cleared $clearedCache cache files" . PHP_EOL;
}

echo PHP_EOL . "✅ Production cleanup completed!" . PHP_EOL;
echo PHP_EOL . "📋 Next steps for production deployment:" . PHP_EOL;
echo "1. Copy .env.production to .env" . PHP_EOL;
echo "2. Update database credentials in .env" . PHP_EOL;
echo "3. Set proper file permissions (755 for directories, 644 for files)" . PHP_EOL;
echo "4. Configure web server (Apache/Nginx)" . PHP_EOL;
echo "5. Set up SSL certificate for HTTPS" . PHP_EOL;
echo "6. Configure log rotation" . PHP_EOL;
echo "7. Test authentication flows" . PHP_EOL;
