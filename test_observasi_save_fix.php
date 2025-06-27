<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== TESTING OBSERVASI SAVE FIXES ===\n\n";

try {
    $db = \Config\Database::connect();
    echo "✓ Database connected\n";

    // Test ObservasiService instantiation
    $observasiService = new \App\Services\ObservasiService();
    echo "✓ ObservasiService instantiated\n";

    // Test with minimal valid data
    echo "\n1. Testing with valid sample data:\n";

    // Get sample data
    $asesor = $db->table('asesor')->limit(1)->get()->getRowArray();
    $asesi = $db->table('asesi')->limit(1)->get()->getRowArray();

    if (!$asesor || !$asesi) {
        echo "❌ Missing sample data (asesor or asesi)\n";
        exit;
    }

    echo "   Sample asesor: {$asesor['id_asesor']}\n";
    echo "   Sample asesi: {$asesi['id_asesi']}\n";

    // Get pengajuan for this asesi
    $pengajuan = $db->table('pengajuan_asesmen')
        ->where('id_asesi', $asesi['id_asesi'])
        ->limit(1)
        ->get()
        ->getRowArray();

    if (!$pengajuan) {
        echo "❌ No pengajuan found for asesi\n";
        exit;
    }

    echo "   Sample pengajuan: {$pengajuan['id_pengajuan']}\n";

    // Test data
    $testData = [
        'id_asesor' => $asesor['id_asesor'],
        'id_asesi' => $asesi['id_asesi'],
        'id_pengajuan' => $pengajuan['id_pengajuan'],
        'tanggal_observasi' => date('Y-m-d')
    ];

    echo "   Testing save operation...\n";
    $result = $observasiService->saveObservation($testData);

    if ($result['success']) {
        echo "   ✓ Save successful!\n";
        echo "   Message: {$result['message']}\n";
        echo "   Observation ID: {$result['data']['id_observasi']}\n";
    } else {
        echo "   ❌ Save failed: {$result['message']}\n";
    }

    echo "\n2. Testing with invalid data:\n";

    $invalidData = [
        'id_asesor' => 99999, // Non-existent
        'id_asesi' => $asesi['id_asesi'],
        'id_pengajuan' => $pengajuan['id_pengajuan'],
        'tanggal_observasi' => date('Y-m-d')
    ];

    $result2 = $observasiService->saveObservation($invalidData);

    if (!$result2['success']) {
        echo "   ✓ Validation correctly failed: {$result2['message']}\n";
    } else {
        echo "   ❌ Should have failed but didn't\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING COMPLETE ===\n";
