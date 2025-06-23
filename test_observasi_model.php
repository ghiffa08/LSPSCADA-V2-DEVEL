<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \CodeIgniter\Config\Services::codeigniter();
$app->initialize();

// Test the ObservasiModel
$observasiModel = new \App\Models\ObservasiModel();

echo "Testing ObservasiModel methods...\n\n";

try {
    // Test getAsesiBySkema with a schema ID
    echo "1. Testing getAsesiBySkema(1)...\n";
    $result = $observasiModel->getAsesiBySkema(1);
    echo "✓ Success - returned " . count($result) . " records\n\n";

    // Test getStrukturObservasiSkema with a schema ID
    echo "2. Testing getStrukturObservasiSkema(1)...\n";
    $result = $observasiModel->getStrukturObservasiSkema(1);
    echo "✓ Success - returned " . count($result) . " records\n\n";

    // Test basic find functionality  
    echo "3. Testing findAll() with limit...\n";
    $result = $observasiModel->findAll(5);
    echo "✓ Success - returned " . count($result) . " records\n\n";

    echo "All basic tests passed! The database column issues appear to be resolved.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
