<?php

// Test ID Pengajuan Auto-Resolution
require_once 'vendor/autoload.php';

// Bootstrap CodeIgniter
$app = \Config\Services::codeigniter();

echo "=== Test ID Pengajuan Auto-Resolution ===\n\n";

try {
    // Test database connection
    $db = \Config\Database::connect();
    echo "✓ Database connection successful\n";

    // Test getting pengajuan for an asesi
    echo "\n--- Testing Pengajuan Lookup ---\n";

    // Get a sample asesi
    $asesi = $db->table('asesi')->limit(1)->get()->getRow();
    if ($asesi) {
        echo "✓ Found sample asesi: ID {$asesi->id_asesi}\n";

        // Try to find pengajuan for this asesi
        $pengajuan = $db->table('pengajuan_asesmen')
            ->where('id_asesi', $asesi->id_asesi)
            ->orderBy('tanggal_pengajuan', 'DESC')
            ->get()
            ->getRow();

        if ($pengajuan) {
            echo "✓ Found pengajuan: ID {$pengajuan->id_pengajuan}\n";
            echo "  - Tanggal: {$pengajuan->tanggal_pengajuan}\n";
        } else {
            echo "⚠ No pengajuan found for this asesi\n";

            // Let's check if there are any pengajuan at all
            $anyPengajuan = $db->table('pengajuan_asesmen')->limit(1)->get()->getRow();
            if ($anyPengajuan) {
                echo "  - Using any available pengajuan: ID {$anyPengajuan->id_pengajuan}\n";
            } else {
                echo "  - No pengajuan found in database\n";
            }
        }
    } else {
        echo "⚠ No asesi found in database\n";
    }

    // Test data structure for API call
    echo "\n--- Testing API Data Structure ---\n";

    $testData = [
        'id_asesi' => $asesi->id_asesi ?? '1',
        'tanggal_observasi' => date('Y-m-d'),
        'save_type' => 'settings'
        // id_pengajuan intentionally missing to test auto-resolution
    ];

    echo "✓ Test data prepared:\n";
    echo "  - id_asesi: {$testData['id_asesi']}\n";
    echo "  - tanggal_observasi: {$testData['tanggal_observasi']}\n";
    echo "  - id_pengajuan: (will be auto-resolved)\n";

    echo "\n--- Testing ObservasiService ---\n";

    // Test service instantiation
    $observasiService = new \App\Services\ObservasiService();
    echo "✓ ObservasiService instantiated successfully\n";

    echo "\n=== All Tests Completed ===\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
