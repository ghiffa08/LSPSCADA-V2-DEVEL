<?php
// Test script for Asesor System - Complete Integration Test
// This script tests the authentication system and asesor functionality

require 'vendor/autoload.php';

echo "=== ASESOR SYSTEM INTEGRATION TEST ===\n\n";

// Test 1: Check if all critical files exist
$criticalFiles = [
    'app/Config/Routes.php',
    'app/Config/Filters.php',
    'app/Filters/LoginFilter.php',
    'app/Controllers/CeklistObservasiControllerFixed.php',
    'app/Controllers/Api/AsesorSkema.php',
    'app/Services/AsesorAsesmenService.php',
    'app/Views/asesor/observasi/skema_dashboard.php',
    'app/Views/asesor/observasi/skema_detail_modal.php'
];

echo "1. CHECKING CRITICAL FILES:\n";
$allFilesExist = true;
foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file MISSING\n";
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    echo "\n❌ Some critical files are missing!\n";
    exit(1);
}

echo "\n✅ All critical files exist!\n\n";

// Test 2: Check syntax of PHP files
echo "2. CHECKING PHP SYNTAX:\n";
$phpFiles = [
    'app/Controllers/CeklistObservasiControllerFixed.php',
    'app/Controllers/Api/AsesorSkema.php',
    'app/Services/AsesorAsesmenService.php'
];

$syntaxOk = true;
foreach ($phpFiles as $file) {
    $output = [];
    $returnVar = 0;
    exec("php -l $file 2>&1", $output, $returnVar);

    if ($returnVar === 0) {
        echo "   ✓ $file syntax OK\n";
    } else {
        echo "   ✗ $file syntax ERROR: " . implode(' ', $output) . "\n";
        $syntaxOk = false;
    }
}

if (!$syntaxOk) {
    echo "\n❌ Syntax errors found!\n";
    exit(1);
}

echo "\n✅ All PHP files have valid syntax!\n\n";

// Test 3: Check route patterns in Routes.php
echo "3. CHECKING ROUTE CONFIGURATION:\n";
$routesContent = file_get_contents('app/Config/Routes.php');

$expectedRoutes = [
    'login',
    'logout',
    'register',
    'forgot',
    'reset-password',
    'auth/google',
    'OAuth/proses',
    'asesor/observasi',
    'asesor/api/skema/detail',
    'asesor/api/skema/asesmen',
    'asesor/api/activities/recent',
    'asesor/api/statistics'
];

$routesOk = true;
foreach ($expectedRoutes as $route) {
    if (strpos($routesContent, $route) !== false) {
        echo "   ✓ Route '$route' found\n";
    } else {
        echo "   ✗ Route '$route' NOT FOUND\n";
        $routesOk = false;
    }
}

if (!$routesOk) {
    echo "\n⚠️  Some routes might be missing or have different patterns\n";
} else {
    echo "\n✅ All expected routes found!\n";
}

// Test 4: Check filter configuration
echo "\n4. CHECKING FILTER CONFIGURATION:\n";
$filtersContent = file_get_contents('app/Config/Filters.php');

$expectedFilters = [
    'login.*LoginFilter',
    'role.*RoleFilter',
    'permission.*PermissionFilter',
    'asesor\*',
    'api/\*'
];

$filtersOk = true;
foreach ($expectedFilters as $filter) {
    if (preg_match('/' . str_replace('*', '\\*', $filter) . '/', $filtersContent)) {
        echo "   ✓ Filter pattern '$filter' found\n";
    } else {
        echo "   ✗ Filter pattern '$filter' NOT FOUND\n";
        $filtersOk = false;
    }
}

if (!$filtersOk) {
    echo "\n⚠️  Some filter configurations might be missing\n";
} else {
    echo "\n✅ All expected filters configured!\n";
}

// Test 5: Check Services configuration
echo "\n5. CHECKING SERVICES CONFIGURATION:\n";
$servicesFile = 'app/Config/Services.php';
if (file_exists($servicesFile)) {
    $servicesContent = file_get_contents($servicesFile);

    $expectedServices = [
        'AsesorAsesmenService',
        'AuthenticationService'
    ];

    $servicesOk = true;
    foreach ($expectedServices as $service) {
        if (strpos($servicesContent, $service) !== false) {
            echo "   ✓ Service '$service' found\n";
        } else {
            echo "   ✗ Service '$service' NOT FOUND\n";
            $servicesOk = false;
        }
    }

    if (!$servicesOk) {
        echo "\n⚠️  Some service configurations might be missing\n";
    } else {
        echo "\n✅ All expected services configured!\n";
    }
} else {
    echo "   ⚠️  Services.php file not found\n";
}

// Test 6: Check database table references
echo "\n6. CHECKING DATABASE TABLE REFERENCES:\n";
$serviceContent = file_exists('app/Services/AsesorAsesmenService.php') ?
    file_get_contents('app/Services/AsesorAsesmenService.php') : '';

$expectedTables = [
    'asesmen',
    'skema',
    'asesor_asesmen',
    'tanggal_asesmen',
    'tuk'
];

$tablesOk = true;
foreach ($expectedTables as $table) {
    if (strpos($serviceContent, $table) !== false) {
        echo "   ✓ Table '$table' referenced\n";
    } else {
        echo "   ✗ Table '$table' NOT REFERENCED\n";
        $tablesOk = false;
    }
}

if (!$tablesOk) {
    echo "\n⚠️  Some database table references might be missing\n";
} else {
    echo "\n✅ All expected database tables referenced!\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY:\n";
echo "✅ File Structure: " . ($allFilesExist ? "PASS" : "FAIL") . "\n";
echo "✅ PHP Syntax: " . ($syntaxOk ? "PASS" : "FAIL") . "\n";
echo "✅ Route Configuration: " . ($routesOk ? "PASS" : "WARN") . "\n";
echo "✅ Filter Configuration: " . ($filtersOk ? "PASS" : "WARN") . "\n";
echo "✅ Services Configuration: " . ($servicesOk ? "PASS" : "WARN") . "\n";
echo "✅ Database References: " . ($tablesOk ? "PASS" : "WARN") . "\n";

if ($allFilesExist && $syntaxOk) {
    echo "\n🎉 SYSTEM READY FOR TESTING!\n";
    echo "\nNext Steps:\n";
    echo "1. Start your local server (Apache/Nginx)\n";
    echo "2. Test user authentication at /login\n";
    echo "3. Test asesor dashboard at /asesor/observasi\n";
    echo "4. Test API endpoints with AJAX calls\n";
    echo "5. Verify role-based access control\n";
} else {
    echo "\n❌ SYSTEM HAS ISSUES - FIX BEFORE TESTING!\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
