<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== TESTING OBSERVASI SAVE WITH ENHANCED ERROR HANDLING ===\n\n";

try {
    $db = \Config\Database::connect();
    echo "✓ Database connected\n";

    // Test ObservasiService instantiation
    $observasiService = new \App\Services\ObservasiService();
    echo "✓ ObservasiService instantiated\n";

    // Get sample data for testing
    $asesor = $db->table('asesor')->limit(1)->get()->getRowArray();
    $asesi = $db->table('asesi')->limit(1)->get()->getRowArray();

    if (!$asesor || !$asesi) {
        echo "❌ Missing sample data\n";
        echo "Asesor: " . ($asesor ? "Found" : "Not found") . "\n";
        echo "Asesi: " . ($asesi ? "Found" : "Not found") . "\n";
        exit;
    }

    echo "Sample asesor: {$asesor['id_asesor']}\n";
    echo "Sample asesi: {$asesi['id_asesi']}\n";

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

    echo "Sample pengajuan: {$pengajuan['id_pengajuan']}\n";

    // Test data
    $testData = [
        'id_asesor' => $asesor['id_asesor'],
        'id_asesi' => $asesi['id_asesi'],
        'id_pengajuan' => $pengajuan['id_pengajuan'],
        'tanggal_observasi' => date('Y-m-d')
    ];

    echo "\nTesting save operation...\n";
    echo "Test data: " . json_encode($testData) . "\n";

    $result = $observasiService->saveObservation($testData);

    echo "\nResult:\n";
    if ($result['success']) {
        echo "✓ Save successful!\n";
        echo "Message: {$result['message']}\n";
        if (isset($result['data']['id_observasi'])) {
            echo "Observation ID: {$result['data']['id_observasi']}\n";
        }
    } else {
        echo "❌ Save failed: {$result['message']}\n";
    }

    // Check logs for more details
    echo "\nCheck the logs for detailed information:\n";
    echo "- writable/logs/log-" . date('Y-m-d') . ".log\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTING COMPLETE ===\n";
