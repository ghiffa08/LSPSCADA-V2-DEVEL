<?php

// Simple debug for observasi issues
require 'vendor/autoload.php';

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "Testing Observasi Issues...\n";

$db = \Config\Database::connect();

// 1. Check if skema has units
$skema = $db->table('skema')->where('status', 'Y')->first();
if (!$skema) {
    echo "ERROR: No active skema found\n";
    exit;
}

echo "Testing with skema: {$skema['nama_skema']}\n";

// 2. Check the join query directly
$query = "
    SELECT COUNT(*) as total
    FROM skema s
    INNER JOIN kelompok_kerja kk ON kk.id_skema = s.id_skema
    INNER JOIN kelompok_unit ku ON ku.id_kelompok = kk.id_kelompok
    INNER JOIN unit u ON u.id_unit = ku.id_unit AND u.id_skema = s.id_skema
    INNER JOIN elemen e ON e.id_unit = u.id_unit AND e.id_skema = s.id_skema
    INNER JOIN kuk k ON k.id_elemen = e.id_elemen AND k.id_unit = u.id_unit AND k.id_skema = s.id_skema
    WHERE s.id_skema = {$skema['id_skema']} AND s.status = 'Y' AND u.status = 'Y'
";

$result = $db->query($query)->getRow();
echo "Total KUKs found: {$result->total}\n";

if ($result->total == 0) {
    echo "Problem found: No KUKs match the complex join\n";

    // Check each table individually
    echo "Checking each table:\n";

    $kelompok_kerja = $db->table('kelompok_kerja')->where('id_skema', $skema['id_skema'])->countAllResults();
    echo "- kelompok_kerja: {$kelompok_kerja}\n";

    if ($kelompok_kerja > 0) {
        $kelompok_ids = $db->table('kelompok_kerja')->where('id_skema', $skema['id_skema'])->get()->getResultArray();
        $ids = array_column($kelompok_ids, 'id_kelompok');

        $kelompok_unit = $db->table('kelompok_unit')->whereIn('id_kelompok', $ids)->countAllResults();
        echo "- kelompok_unit: {$kelompok_unit}\n";

        if ($kelompok_unit > 0) {
            $unit_active = $db->table('unit')->where('id_skema', $skema['id_skema'])->where('status', 'Y')->countAllResults();
            echo "- unit (active): {$unit_active}\n";

            $unit_all = $db->table('unit')->where('id_skema', $skema['id_skema'])->countAllResults();
            echo "- unit (all): {$unit_all}\n";
        }
    }
}

echo "Testing ObservasiService...\n";
try {
    $service = new \App\Services\ObservasiService();
    echo "ObservasiService created successfully\n";
} catch (Exception $e) {
    echo "ERROR creating ObservasiService: " . $e->getMessage() . "\n";
}

echo "Done.\n";
