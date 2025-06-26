<?php

/**
 * Testing script untuk memverifikasi data asesmen
 * Gunakan untuk debugging error "Undefined array key id_asesmen"
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load CodeIgniter
$pathsConfig = new \Config\Paths();
$bootstrap = rtrim(realpath($pathsConfig->systemDirectory), '\\/') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require $bootstrap;

// Load model
$asesmenModel = new \App\Models\AsesmenModel();

echo "=== Testing AsesmenModel ===\n\n";

// Test 1: Check primary key
echo "1. Primary Key: " . $asesmenModel->getTable() . " -> " . $asesmenModel->getPrimaryKey() . "\n\n";

// Test 2: Test getAllAsesmen method
echo "2. Testing getAllAsesmen() method:\n";
try {
    $asesmen = $asesmenModel->getAllAsesmen();
    echo "   - Total records: " . count($asesmen) . "\n";

    if (!empty($asesmen)) {
        echo "   - Sample data structure (first record):\n";
        $first = $asesmen[0];
        foreach ($first as $key => $value) {
            echo "     $key: $value\n";
        }

        // Check if id_asesmen exists
        if (isset($first['id_asesmen'])) {
            echo "   ✅ id_asesmen field exists\n";
        } else {
            echo "   ❌ id_asesmen field MISSING\n";
            echo "   Available fields: " . implode(', ', array_keys($first)) . "\n";
        }
    } else {
        echo "   - No data found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test raw database query
echo "3. Testing raw database query:\n";
try {
    $db = \Config\Database::connect();
    $query = $db->query("SELECT id_asesmen, id_skema, tujuan FROM asesmen LIMIT 1");
    $result = $query->getRowArray();

    if ($result) {
        echo "   - Raw query result:\n";
        foreach ($result as $key => $value) {
            echo "     $key: $value\n";
        }

        if (isset($result['id_asesmen'])) {
            echo "   ✅ id_asesmen field exists in raw query\n";
        } else {
            echo "   ❌ id_asesmen field MISSING in raw query\n";
        }
    } else {
        echo "   - No data found in raw query\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Test table structure
echo "4. Testing table structure:\n";
try {
    $db = \Config\Database::connect();
    $query = $db->query("DESCRIBE asesmen");
    $fields = $query->getResultArray();

    echo "   - Table fields:\n";
    foreach ($fields as $field) {
        echo "     " . $field['Field'] . " (" . $field['Type'] . ")" .
            ($field['Key'] == 'PRI' ? ' [PRIMARY KEY]' : '') . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
