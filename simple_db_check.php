<?php
// Simple database table check
require_once 'vendor/autoload.php';

try {
    // Initialize CodeIgniter config
    $config = new \Config\Database();
    $db = new \CodeIgniter\Database\MySQLi\Connection($config->default);

    echo "Database connection test...\n";

    // Get all tables
    $tables = $db->listTables();
    echo "Tables found: " . count($tables) . "\n\n";

    foreach ($tables as $table) {
        echo "Table: $table\n";
        if (strpos($table, 'tanggal') !== false || strpos($table, 'asesmen') !== false) {
            $fields = $db->getFieldNames($table);
            echo "  Fields: " . implode(', ', $fields) . "\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
