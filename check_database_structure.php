<?php
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

try {
    $db = \Config\Database::connect();

    echo "=== DATABASE STRUCTURE ANALYSIS ===\n\n";

    // Check all tables
    echo "1. ALL TABLES IN DATABASE:\n";
    $tables = $db->listTables();
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    echo "\n";

    // Check tables with 'tanggal' in name
    echo "2. TABLES CONTAINING 'tanggal':\n";
    $query = $db->query("SHOW TABLES LIKE '%tanggal%'");
    $tanggalTables = $query->getResultArray();
    foreach ($tanggalTables as $table) {
        $tableName = array_values($table)[0];
        echo "   - $tableName\n";

        // Show structure of tanggal-related tables
        $structure = $db->query("DESCRIBE $tableName");
        $columns = $structure->getResultArray();
        echo "     Columns:\n";
        foreach ($columns as $column) {
            echo "       * {$column['Field']} ({$column['Type']}) - {$column['Key']}\n";
        }
        echo "\n";
    }

    // Check tables with 'asesmen' in name
    echo "3. TABLES CONTAINING 'asesmen':\n";
    $query = $db->query("SHOW TABLES LIKE '%asesmen%'");
    $asesmenTables = $query->getResultArray();
    foreach ($asesmenTables as $table) {
        $tableName = array_values($table)[0];
        echo "   - $tableName\n";

        // Show structure of asesmen-related tables
        $structure = $db->query("DESCRIBE $tableName");
        $columns = $structure->getResultArray();
        echo "     Columns:\n";
        foreach ($columns as $column) {
            echo "       * {$column['Field']} ({$column['Type']}) - {$column['Key']}\n";
        }
        echo "\n";
    }

    // Check tables with 'set_' prefix
    echo "4. TABLES STARTING WITH 'set_':\n";
    $query = $db->query("SHOW TABLES LIKE 'set_%'");
    $setTables = $query->getResultArray();
    foreach ($setTables as $table) {
        $tableName = array_values($table)[0];
        echo "   - $tableName\n";

        // Show structure
        $structure = $db->query("DESCRIBE $tableName");
        $columns = $structure->getResultArray();
        echo "     Columns:\n";
        foreach ($columns as $column) {
            echo "       * {$column['Field']} ({$column['Type']}) - {$column['Key']}\n";
        }
        echo "\n";
    }

    // Check if asesmen table has id_tanggal column
    echo "5. ASESMEN TABLE STRUCTURE:\n";
    if (in_array('asesmen', $tables)) {
        $structure = $db->query("DESCRIBE asesmen");
        $columns = $structure->getResultArray();
        echo "   Columns in 'asesmen' table:\n";
        foreach ($columns as $column) {
            echo "     * {$column['Field']} ({$column['Type']}) - {$column['Key']}\n";
        }
    } else {
        echo "   asesmen table not found!\n";
    }

    echo "\n=== RELATIONSHIP ANALYSIS ===\n";

    // Check if foreign key relationships exist
    echo "6. FOREIGN KEY CONSTRAINTS:\n";
    $fkQuery = $db->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = DATABASE()
            AND (TABLE_NAME LIKE '%asesmen%' OR TABLE_NAME LIKE '%tanggal%' OR TABLE_NAME LIKE 'set_%')
    ");

    $foreignKeys = $fkQuery->getResultArray();
    foreach ($foreignKeys as $fk) {
        echo "   {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
    }

    echo "\n=== ANALYSIS COMPLETE ===\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
