<?php
// Test script untuk memverifikasi struktur API response
require_once 'vendor/autoload.php';

// Load CodeIgniter environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test';

// Bootstrap the application
$app = require_once FCPATH . '../app/Config/Paths.php';
$app = new \CodeIgniter\CodeIgniter($app);
$app->initialize();

echo "=== TESTING OBSERVASI API STRUCTURE ===\n\n";

try {
    // Simulate API request parameters
    $params = [
        'id_skema' => '1',
        'id_asesmen' => '3',
        'id_asesi' => 'ASI002'
    ];

    echo "Testing dengan parameter:\n";
    echo "- id_skema: {$params['id_skema']}\n";
    echo "- id_asesmen: {$params['id_asesmen']}\n";
    echo "- id_asesi: {$params['id_asesi']}\n\n";

    // Load service
    $observasiService = \Config\Services::observasiService();

    // Test load observasi
    echo "1. Testing loadObservasi...\n";
    $result = $observasiService->loadObservasi($params['id_skema'], $params['id_asesmen'], $params['id_asesi']);

    echo "Result structure:\n";
    echo "- success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "- totalKUK: " . ($result['totalKUK'] ?? 0) . "\n";
    echo "- observasi type: " . gettype($result['observasi'] ?? null) . "\n";

    if (isset($result['observasi']) && is_array($result['observasi'])) {
        echo "- observasi keys: " . implode(', ', array_keys($result['observasi'])) . "\n";

        // Check first unit structure
        $firstUnit = current($result['observasi']);
        if ($firstUnit) {
            echo "- first unit structure:\n";
            echo "  - unit_info keys: " . implode(', ', array_keys($firstUnit['unit_info'] ?? [])) . "\n";
            echo "  - elements type: " . gettype($firstUnit['elements'] ?? null) . "\n";

            if (isset($firstUnit['elements']) && is_array($firstUnit['elements'])) {
                echo "  - elements keys: " . implode(', ', array_keys($firstUnit['elements'])) . "\n";

                // Check first element structure
                $firstElement = current($firstUnit['elements']);
                if ($firstElement) {
                    echo "  - first element structure:\n";
                    echo "    - element_info keys: " . implode(', ', array_keys($firstElement['element_info'] ?? [])) . "\n";
                    echo "    - kuks type: " . gettype($firstElement['kuks'] ?? null) . "\n";
                    echo "    - kuks count: " . count($firstElement['kuks'] ?? []) . "\n";

                    if (isset($firstElement['kuks']) && is_array($firstElement['kuks']) && !empty($firstElement['kuks'])) {
                        $firstKuk = $firstElement['kuks'][0];
                        echo "    - first KUK keys: " . implode(', ', array_keys($firstKuk)) . "\n";
                    }
                }
            }
        }
    }

    echo "\n2. Testing JavaScript conversion simulation...\n";

    // Simulate JavaScript convertObservasiObjectToArray function
    if (isset($result['observasi']) && is_array($result['observasi'])) {
        $observasiArray = [];

        foreach ($result['observasi'] as $unitKey => $unit) {
            $unitInfo = $unit['unit_info'] ?? [];

            foreach ($unit['elements'] ?? [] as $elemenKey => $element) {
                $elemenInfo = $element['element_info'] ?? [];

                foreach ($element['kuks'] ?? [] as $kuk) {
                    $observasiArray[] = [
                        'id_kelompok' => $unitInfo['id_kelompok'] ?? 1,
                        'nama_kelompok' => $unitInfo['nama_kelompok'] ?? 'Kelompok Utama',
                        'id_unit' => $unitInfo['id_unit'] ?? '',
                        'kode_unit' => $unitInfo['kode_unit'] ?? '',
                        'nama_unit' => $unitInfo['nama_unit'] ?? '',
                        'id_elemen' => $elemenInfo['id_elemen'] ?? '',
                        'kode_elemen' => $elemenInfo['kode_elemen'] ?? '',
                        'nama_elemen' => $elemenInfo['nama_elemen'] ?? '',
                        'id_kuk' => $kuk['id_kuk'] ?? '',
                        'kode_kuk' => $kuk['kode_kuk'] ?? '',
                        'kriteria_unjuk_kerja' => $kuk['nama_kuk'] ?? ''
                    ];
                }
            }
        }

        echo "Converted array count: " . count($observasiArray) . "\n";
        echo "Sample converted item:\n";
        if (!empty($observasiArray)) {
            $sample = $observasiArray[0];
            foreach ($sample as $key => $value) {
                echo "  - {$key}: {$value}\n";
            }
        }
    }

    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
