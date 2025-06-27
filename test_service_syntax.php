<?php

// Simple test for ObservasiService
echo "Testing ObservasiService syntax and basic instantiation...\n";

// Test 1: Check syntax
echo "1. Checking PHP syntax...\n";
$syntaxCheck = shell_exec('php -l app/Services/ObservasiService.php 2>&1');
echo "   Result: " . trim($syntaxCheck) . "\n";

// Test 2: Check class structure
echo "2. Checking class structure...\n";
$content = file_get_contents('app/Services/ObservasiService.php');

// Check for proper cache instantiation
if (strpos($content, '\Config\Services::cache()') !== false) {
    echo "   ✓ Cache service correctly instantiated\n";
} else {
    echo "   ❌ Cache service not properly instantiated\n";
}

// Check for proper database instantiation  
if (strpos($content, '\Config\Database::connect()') !== false) {
    echo "   ✓ Database connection correctly instantiated\n";
} else {
    echo "   ❌ Database connection not properly instantiated\n";
}

// Check for deleteMatching method calls (not comments)
if (preg_match('/->deleteMatching\s*\(/', $content)) {
    echo "   ❌ deleteMatching method calls still present\n";
} else {
    echo "   ✓ deleteMatching method calls removed\n";
}

// Check for proper imports
$hasImports = strpos($content, 'use CodeIgniter\Cache\CacheInterface') !== false &&
    strpos($content, 'use CodeIgniter\Database\BaseConnection') !== false;

if ($hasImports) {
    echo "   ✓ Required imports present\n";
} else {
    echo "   ❌ Missing required imports\n";
}

echo "\n✅ ObservasiService analysis complete!\n";
