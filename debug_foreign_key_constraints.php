<?php

require_once 'vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

// Get database connection
$db = \Config\Database::connect();

echo "=== DEBUGGING FOREIGN KEY CONSTRAINTS ===\n\n";

// 1. Check detail_observasi table structure
echo "1. DETAIL_OBSERVASI TABLE STRUCTURE:\n";
$query = $db->query("SHOW CREATE TABLE detail_observasi");
$result = $query->getRow();
echo $result->{'Create Table'} . "\n\n";

// 2. Check skema table
echo "2. SKEMA TABLE - Check if records exist:\n";
$skemaQuery = $db->query("SELECT id_skema, kode_skema, nama_skema, status FROM skema LIMIT 5");
$skemaResults = $skemaQuery->getResult();
foreach ($skemaResults as $row) {
    echo "ID: {$row->id_skema}, Kode: {$row->kode_skema}, Nama: {$row->nama_skema}, Status: {$row->status}\n";
}
echo "\n";

// 3. Check observasi table structure
echo "3. OBSERVASI TABLE STRUCTURE:\n";
$obsQuery = $db->query("SHOW CREATE TABLE observasi");
$obsResult = $obsQuery->getRow();
echo $obsResult->{'Create Table'} . "\n\n";

// 4. Check foreign key constraints
echo "4. FOREIGN KEY CONSTRAINTS ON detail_observasi:\n";
$fkQuery = $db->query("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME,
        DELETE_RULE,
        UPDATE_RULE
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'detail_observasi' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
");
$fkResults = $fkQuery->getResult();
foreach ($fkResults as $fk) {
    echo "Constraint: {$fk->CONSTRAINT_NAME}\n";
    echo "  Column: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    echo "  Delete: {$fk->DELETE_RULE}, Update: {$fk->UPDATE_RULE}\n\n";
}

// 5. Test specific ID values that might be causing issues
echo "5. TESTING SPECIFIC IDS:\n";
$testIds = [1, 2, 3, 47]; // Common IDs that might be used
foreach ($testIds as $id) {
    $check = $db->query("SELECT COUNT(*) as count FROM skema WHERE id_skema = ?", [$id]);
    $count = $check->getRow()->count;
    echo "id_skema = {$id}: " . ($count > 0 ? "EXISTS" : "NOT FOUND") . "\n";
}
echo "\n";

// 6. Check what IDs are typically used in frontend
echo "6. AVAILABLE SKEMA IDS:\n";
$allSkema = $db->query("SELECT id_skema, kode_skema FROM skema WHERE status = 'Y' ORDER BY id_skema");
$allResults = $allSkema->getResult();
foreach ($allResults as $row) {
    echo "ID: {$row->id_skema}, Kode: {$row->kode_skema}\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
