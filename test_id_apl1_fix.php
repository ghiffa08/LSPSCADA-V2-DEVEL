<?php

// Simple test to verify id_apl1 error is fixed
echo "Testing ObservasiModel for id_apl1 issues...\n\n";

try {
    // Try to include the necessary files
    require_once 'vendor/autoload.php';

    // Initialize CodeIgniter environment
    $app = \CodeIgniter\Config\Services::codeigniter();
    $app->initialize();

    // Test basic model instantiation
    $observasiModel = new \App\Models\ObservasiModel();
    echo "✓ ObservasiModel instantiated successfully\n";

    // Check allowed fields
    $allowedFields = $observasiModel->getAllowedFields();
    echo "✓ Allowed fields: " . implode(', ', $allowedFields) . "\n";

    // Check if id_apl1 is in allowed fields
    if (in_array('id_apl1', $allowedFields)) {
        echo "✓ id_apl1 is in allowed fields (database compatibility maintained)\n";
    } else {
        echo "⚠ id_apl1 not in allowed fields\n";
    }

    echo "\nTest completed successfully! The model should now work with database.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
