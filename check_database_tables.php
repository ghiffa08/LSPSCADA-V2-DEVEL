<?php

// Simple database check without full CodeIgniter bootstrap
try {
    // Direct database connection
    $config = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'lsp_scada_app_devel',
        'DBDriver' => 'MySQLi',
    ];

    $db = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    echo "=== DATABASE TABLE ANALYSIS ===\n\n";

    // Get all tables
    $result = $db->query("SHOW TABLES");
    $allTables = [];
    while ($row = $result->fetch_array()) {
        $allTables[] = $row[0];
    }

    echo "All tables in database:\n";
    foreach ($allTables as $table) {
        echo "- $table\n";
    }
    echo "\n";

    // Check specific tables that might be related to date/asesmen
    $relevantTables = array_filter($allTables, function ($table) {
        return strpos($table, 'tanggal') !== false ||
            strpos($table, 'asesmen') !== false ||
            strpos($table, 'set_') !== false ||
            strpos($table, 'date') !== false;
    });

    echo "=== RELEVANT TABLES ===\n";
    foreach ($relevantTables as $table) {
        echo "\nTABLE: $table\n";
        echo str_repeat("-", 50) . "\n";

        $result = $db->query("DESCRIBE `$table`");
        while ($field = $result->fetch_assoc()) {
            echo sprintf(
                "%-25s %-20s %-10s %s\n",
                $field['Field'],
                $field['Type'],
                $field['Key'],
                $field['Extra']
            );
        }
    }

    // Check specific tables
    echo "\n=== SPECIFIC TABLE CHECKS ===\n";
    $checkTables = ['tanggal_asesmen', 'set_tanggal', 'asesmen', 'observasi'];
    foreach ($checkTables as $table) {
        $exists = in_array($table, $allTables);
        echo "Table '$table': " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
    }

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
