<?php
echo "Simple DB Test\n";

require_once 'vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

// Quick test
$count = $db->table('skema')->countAllResults();
echo "Skema count: $count\n";

$service = new \App\Services\ObservasiService();
echo "Service created\n";

// Test foreign key validation
try {
    $testData = [
        'id_asesor' => 1,
        'id_asesi' => 'test123',
        'id_pengajuan' => 1,
        'tanggal_observasi' => '2025-06-26'
    ];

    $result = $service->saveObservation($testData);
    echo "Result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test complete\n";
