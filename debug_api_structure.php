<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== TESTING OBSERVASI API RESPONSE STRUCTURE ===\n\n";

try {
    $observasiService = new \App\Services\ObservasiService();

    // Get sample skema
    $db = \Config\Database::connect();
    $skema = $db->table('skema')->where('status', 'Y')->first();

    if (!$skema) {
        echo "No active skema found\n";
        exit;
    }

    echo "Testing with skema: {$skema['nama_skema']}\n";

    // Test getKukStructureForSchema
    $result = $observasiService->getKukStructureForSchema($skema['id_skema'], 'test_asesi');

    echo "Result structure:\n";
    echo "- success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "- message: " . $result['message'] . "\n";

    if ($result['success']) {
        $data = $result['data'];
        echo "- data.structure type: " . gettype($data['structure']) . "\n";
        echo "- data.structure keys: " . (is_array($data['structure']) ? count($data['structure']) : count((array)$data['structure'])) . "\n";

        if (is_array($data['structure']) || is_object($data['structure'])) {
            $firstKey = array_keys((array)$data['structure'])[0] ?? null;
            if ($firstKey) {
                echo "- first key: {$firstKey}\n";
                $firstUnit = $data['structure'][$firstKey];
                echo "- first unit structure:\n";
                echo "  - unit_info: " . (isset($firstUnit['unit_info']) ? 'exists' : 'missing') . "\n";
                echo "  - elements: " . (isset($firstUnit['elements']) ? 'exists' : 'missing') . "\n";

                if (isset($firstUnit['elements'])) {
                    $firstElement = array_values($firstUnit['elements'])[0] ?? null;
                    if ($firstElement) {
                        echo "  - first element kuks: " . count($firstElement['kuks'] ?? []) . "\n";
                    }
                }
            }
        }

        echo "- totalKUK: " . $data['totalKUK'] . "\n";
        echo "- existingData: " . count($data['existingData']) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
