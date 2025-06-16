<?php

// Simple database check without CodeIgniter bootstrap
try {
    echo "=== CHECKING USERS TABLE STRUCTURE ===\n";

    // Database connection (adjust these settings to match your database)
    $host = 'localhost';
    $database = 'lsp_scada_app_devel';  // Adjust database name
    $username = 'root';  // Adjust username
    $password = '';      // Adjust password

    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get table structure
    $stmt = $pdo->query("DESCRIBE users");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Fields in users table:\n";
    $fieldNames = [];
    foreach ($fields as $field) {
        $fieldNames[] = $field['Field'];
        echo "  - {$field['Field']} ({$field['Type']}) " .
            ($field['Null'] === 'YES' ? 'NULL' : 'NOT NULL') .
            ($field['Default'] !== null ? " DEFAULT '{$field['Default']}'" : '') . "\n";
    }

    // Check if google_id exists
    if (in_array('google_id', $fieldNames)) {
        echo "\n✅ google_id field EXISTS in users table\n";

        // Test google_id query
        echo "\n=== TESTING GOOGLE_ID QUERY ===\n";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE google_id IS NOT NULL");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "google_id query works: ✅ YES\n";
            echo "Found {$result['count']} users with google_id\n";
        } catch (Exception $e) {
            echo "google_id query error: ❌ " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n❌ google_id field DOES NOT EXIST in users table\n";
        echo "Available fields: " . implode(', ', $fieldNames) . "\n";
        echo "\nYou need to add google_id field to users table!\n";
        echo "SQL: ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL;\n";
    }

    // Check current users
    echo "\n=== CURRENT USERS ===\n";
    $stmt = $pdo->query("SELECT id, email, username, nama_lengkap FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "  - ID: {$user['id']}, Email: {$user['email']}, Username: {$user['username']}, Name: {$user['nama_lengkap']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e->getCode() == 1049) {
        echo "Database does not exist. Please check database name.\n";
    } elseif ($e->getCode() == 1045) {
        echo "Access denied. Please check username/password.\n";
    } elseif ($e->getCode() == 1146) {
        echo "Table 'users' does not exist.\n";
    }
}
