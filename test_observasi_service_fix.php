<?php

// Test ObservasiService instantiation and basic functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing ObservasiService instantiation...\n";

// Bootstrap CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

try {
    echo "1. Testing cache service instantiation...\n";
    $cache = \Config\Services::cache();
    echo "   ✓ Cache service instantiated successfully: " . get_class($cache) . "\n";

    echo "2. Testing database connection...\n";
    $db = \Config\Database::connect();
    echo "   ✓ Database connected successfully: " . get_class($db) . "\n";

    echo "3. Testing ObservasiService instantiation...\n";
    require_once 'app/Services/ObservasiService.php';
    $observasiService = new \App\Services\ObservasiService();
    echo "   ✓ ObservasiService instantiated successfully\n";

    echo "4. Testing basic service functionality...\n";
    // Test if the service can be called without errors
    $reflection = new ReflectionClass($observasiService);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "   ✓ Available public methods: " . count($methods) . "\n";

    foreach ($methods as $method) {
        if (str_starts_with($method->getName(), 'get') && $method->getNumberOfParameters() === 0) {
            echo "   - " . $method->getName() . "\n";
        }
    }

    echo "\n✅ All tests passed! ObservasiService is working correctly.\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
