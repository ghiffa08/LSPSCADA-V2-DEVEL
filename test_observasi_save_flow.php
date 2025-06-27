<?php
// Test script untuk memverifikasi alur save observasi yang sudah diperbaiki

require 'vendor/autoload.php';

// Inisialisasi CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

use App\Services\ObservasiService;

echo "=== TEST OBSERVASI SAVE FLOW ===\n\n";

try {
    $observasiService = new ObservasiService();

    // Test data
    $testData = [
        'id_asesmen' => 47,
        'id_skema' => 1,
        'id_asesi' => 'ASI001',
        'tanggal_observasi' => date('Y-m-d'),
    ];

    echo "1. Testing saveObservation (settings) to get id_observasi...\n";

    // Test save settings to create observasi record
    $result = $observasiService->saveObservation($testData);

    if ($result['success']) {
        echo "✓ Settings saved successfully!\n";
        echo "Data returned: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";

        $id_observasi = $result['data']['id_observasi'] ?? null;

        if ($id_observasi) {
            echo "✓ Got id_observasi: {$id_observasi}\n\n";

            echo "2. Testing single KUK save with id_observasi...\n";

            // Test single KUK save
            $kukResult = $observasiService->saveKUKDetail([
                'id_observasi' => $id_observasi,
                'id_kuk' => 1,
                'kompeten' => 'Y',
                'keterangan' => 'Test KUK save'
            ]);

            if ($kukResult['success']) {
                echo "✓ Single KUK saved successfully!\n";
                echo "Result: " . json_encode($kukResult, JSON_PRETTY_PRINT) . "\n\n";
            } else {
                echo "✗ Single KUK save failed: " . $kukResult['message'] . "\n\n";
            }

            echo "3. Testing batch KUK save...\n";

            // Test batch KUK save
            $batchResult = $observasiService->batchSaveKUK($id_observasi, [
                1 => ['kompeten' => 'Y', 'keterangan' => 'Batch test 1'],
                2 => ['kompeten' => 'N', 'keterangan' => 'Batch test 2'],
                3 => ['kompeten' => 'Y', 'keterangan' => 'Batch test 3']
            ]);

            if ($batchResult['success']) {
                echo "✓ Batch KUK save successful!\n";
                echo "Result: " . json_encode($batchResult, JSON_PRETTY_PRINT) . "\n\n";
            } else {
                echo "✗ Batch KUK save failed: " . $batchResult['message'] . "\n\n";
            }
        } else {
            echo "✗ No id_observasi returned from settings save\n\n";
        }
    } else {
        echo "✗ Settings save failed: " . $result['message'] . "\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
