<?php

require_once 'vendor/autoload.php';

// Define FCPATH
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Load CodeIgniter
$pathsPath = realpath(__DIR__ . '/app/Config/Paths.php');
$paths = new \Config\Paths();
require realpath(__DIR__ . '/system/bootstrap.php');

$app = \Config\Services::codeigniter();
$app->initialize();

try {
    echo "=== CHECKING USERS TABLE STRUCTURE ===\n";

    $db = \Config\Database::connect();

    // Get field names
    $fields = $db->getFieldNames('users');
    echo "Fields in users table:\n";
    foreach ($fields as $field) {
        echo "  - $field\n";
    }

    // Check if google_id exists
    if (in_array('google_id', $fields)) {
        echo "\n✅ google_id field EXISTS in users table\n";
    } else {
        echo "\n❌ google_id field DOES NOT EXIST in users table\n";
        echo "You need to add google_id field to users table!\n";
        echo "SQL: ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL;\n";
    }

    // Test a simple query
    echo "\n=== TESTING SIMPLE QUERY ===\n";
    $query = $db->query("SELECT id, email FROM users LIMIT 1");
    $result = $query->getResult();
    echo "Simple query works: " . (count($result) >= 0 ? "✅ YES" : "❌ NO") . "\n";

    // Test google_id query if field exists
    if (in_array('google_id', $fields)) {
        echo "\n=== TESTING GOOGLE_ID QUERY ===\n";
        try {
            $query = $db->query("SELECT id, email, google_id FROM users WHERE google_id IS NOT NULL LIMIT 1");
            $result = $query->getResult();
            echo "google_id query works: ✅ YES\n";
            echo "Found " . count($result) . " users with google_id\n";
        } catch (Exception $e) {
            echo "google_id query error: ❌ " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
