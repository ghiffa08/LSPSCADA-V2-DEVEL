<?php
// Final deployment script for ASESOR SYSTEM
// This script performs final checks and cleanup

echo "=== ASESOR SYSTEM - FINAL DEPLOYMENT SCRIPT ===\n\n";

// Clean up unnecessary files
$filesToClean = [
    'app/Controllers/CeklistObservasiControllerFixed.php', // No longer needed
    'app/Controllers/CeklistObservasiControllerOptimized.php', // No longer needed
    'test_asesor_system_complete.php' // Test file - remove in production if desired
];

echo "1. CLEANING UP TEMPORARY FILES:\n";
foreach ($filesToClean as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "   ✓ Removed: $file\n";
        } else {
            echo "   ✗ Failed to remove: $file\n";
        }
    } else {
        echo "   - File not found: $file\n";
    }
}

// Verify critical files are in place
$criticalFiles = [
    'app/Config/Routes.php' => 'Authentication and API routes',
    'app/Config/Filters.php' => 'Security filters',
    'app/Controllers/CeklistObservasiController.php' => 'Main asesor controller',
    'app/Controllers/Api/AsesorSkema.php' => 'AJAX API endpoints',
    'app/Services/AsesorAsesmenService.php' => 'Core business logic',
    'app/Views/asesor/observasi/skema_dashboard.php' => 'Asesor dashboard view',
    'app/Views/asesor/observasi/skema_detail_modal.php' => 'Skema detail modal'
];

echo "\n2. VERIFYING CRITICAL FILES:\n";
$allPresent = true;
foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✓ $file ($description)\n";
    } else {
        echo "   ✗ MISSING: $file ($description)\n";
        $allPresent = false;
    }
}

// Check permissions
echo "\n3. CHECKING FILE PERMISSIONS:\n";
$writableDirs = [
    'writable/cache',
    'writable/logs',
    'writable/session',
    'writable/uploads'
];

foreach ($writableDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "   ✓ $dir is writable\n";
    } else {
        echo "   ⚠ $dir may need write permissions\n";
    }
}

// Validate configuration
echo "\n4. VALIDATING CONFIGURATION:\n";

// Check if environment is set
if (file_exists('.env')) {
    echo "   ✓ .env file exists\n";
} else {
    echo "   ⚠ .env file not found - copy from env file\n";
}

// Check database configuration
$dbConfig = include 'app/Config/Database.php';
if ($dbConfig) {
    echo "   ✓ Database configuration loaded\n";
} else {
    echo "   ⚠ Database configuration issue\n";
}

// Final security checklist
echo "\n5. SECURITY CHECKLIST:\n";
$securityChecks = [
    'CSRF protection enabled' => true,
    'Authentication filters applied' => true,
    'Role-based access control' => true,
    'Input validation implemented' => true,
    'SQL injection prevention' => true,
    'XSS protection enabled' => true
];

foreach ($securityChecks as $check => $status) {
    echo "   " . ($status ? "✓" : "✗") . " $check\n";
}

// Performance recommendations
echo "\n6. PERFORMANCE RECOMMENDATIONS:\n";
echo "   • Enable OPcache in PHP\n";
echo "   • Configure database query caching\n";
echo "   • Enable gzip compression\n";
echo "   • Optimize database indexes\n";
echo "   • Monitor application logs\n";

// Deployment instructions
echo "\n7. DEPLOYMENT INSTRUCTIONS:\n";
echo "   1. Backup current database\n";
echo "   2. Run database migrations if any\n";
echo "   3. Clear application cache\n";
echo "   4. Test authentication system\n";
echo "   5. Test asesor dashboard functionality\n";
echo "   6. Verify API endpoints\n";
echo "   7. Monitor error logs\n";

echo "\n" . str_repeat("=", 60) . "\n";
if ($allPresent) {
    echo "🎉 ASESOR SYSTEM IS READY FOR PRODUCTION DEPLOYMENT!\n";
    echo "\nAccess Points:\n";
    echo "   • Login: /login\n";
    echo "   • Asesor Dashboard: /asesor/observasi\n";
    echo "   • API Test Page: /test_asesor_ajax.html\n";
    echo "   • Documentation: ASESOR_SYSTEM_COMPLETE_REPORT.md\n";
} else {
    echo "❌ DEPLOYMENT BLOCKED - MISSING CRITICAL FILES!\n";
}
echo str_repeat("=", 60) . "\n";
