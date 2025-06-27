<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== OBSERVASI DEBUG SCRIPT ===\n\n";

try {
    // Test database connection
    $db = \Config\Database::connect();
    echo "✓ Database connection OK\n";

    // Test ObservasiService instantiation
    $observasiService = new \App\Services\ObservasiService();
    echo "✓ ObservasiService instantiation OK\n\n";

    // 1. Test untuk check apakah ada data skema
    echo "1. CHECKING SKEMA DATA:\n";
    $skemas = $db->table('skema')->where('status', 'Y')->get()->getResultArray();
    echo "   Found " . count($skemas) . " active skemas\n";

    if (count($skemas) > 0) {
        $testSkema = $skemas[0];
        echo "   Test skema: {$testSkema['id_skema']} - {$testSkema['nama_skema']}\n";

        // 2. Test struktur KUK untuk skema
        echo "\n2. CHECKING KUK STRUCTURE:\n";
        $result = $observasiService->getKukStructureForSchema($testSkema['id_skema']);

        if ($result['success']) {
            $structure = $result['data']['structure'];
            $totalKUK = $result['data']['totalKUK'];
            echo "   ✓ Structure loaded successfully\n";
            echo "   Total KUK: {$totalKUK}\n";
            echo "   Units found: " . count($structure) . "\n";

            if (count($structure) == 0) {
                echo "   ❌ NO UNITS FOUND - This is the problem!\n";

                // Debug: Check kelompok_kerja
                $kelompok = $db->table('kelompok_kerja')->where('id_skema', $testSkema['id_skema'])->get()->getResultArray();
                echo "   Kelompok kerja for skema: " . count($kelompok) . "\n";

                if (count($kelompok) > 0) {
                    // Check kelompok_unit
                    $kelompokUnit = $db->table('kelompok_unit')->whereIn('id_kelompok', array_column($kelompok, 'id_kelompok'))->get()->getResultArray();
                    echo "   Kelompok unit: " . count($kelompokUnit) . "\n";

                    if (count($kelompokUnit) > 0) {
                        // Check unit
                        $units = $db->table('unit')
                            ->where('id_skema', $testSkema['id_skema'])
                            ->where('status', 'Y')
                            ->get()->getResultArray();
                        echo "   Units for skema: " . count($units) . "\n";

                        if (count($units) > 0) {
                            $testUnit = $units[0];
                            echo "   Test unit: {$testUnit['id_unit']} - {$testUnit['nama_unit']}\n";

                            // Check elemen
                            $elemen = $db->table('elemen')
                                ->where('id_unit', $testUnit['id_unit'])
                                ->where('id_skema', $testSkema['id_skema'])
                                ->get()->getResultArray();
                            echo "   Elemen for unit: " . count($elemen) . "\n";

                            if (count($elemen) > 0) {
                                $testElemen = $elemen[0];
                                echo "   Test elemen: {$testElemen['id_elemen']} - {$testElemen['nama_elemen']}\n";

                                // Check KUK
                                $kuks = $db->table('kuk')
                                    ->where('id_elemen', $testElemen['id_elemen'])
                                    ->where('id_unit', $testUnit['id_unit'])
                                    ->where('id_skema', $testSkema['id_skema'])
                                    ->get()->getResultArray();
                                echo "   KUKs for elemen: " . count($kuks) . "\n";
                            }
                        }
                    }
                }
            }
        } else {
            echo "   ❌ Error: {$result['message']}\n";
        }

        // 3. Test save observation
        echo "\n3. TESTING SAVE OBSERVATION:\n";

        // Check if there's any asesi
        $asesis = $db->table('asesi')->limit(1)->get()->getResultArray();
        if (count($asesis) > 0) {
            $testAsesi = $asesis[0];
            echo "   Test asesi: {$testAsesi['id_asesi']}\n";

            // Check pengajuan for this asesi
            $pengajuan = $db->table('pengajuan_asesmen')
                ->where('id_asesi', $testAsesi['id_asesi'])
                ->orderBy('tanggal_pengajuan', 'DESC')
                ->get()->getRowArray();

            if ($pengajuan) {
                echo "   Found pengajuan: {$pengajuan['id_pengajuan']}\n";

                // Try to save minimal observation
                $testData = [
                    'id_asesor' => 1, // Assuming asesor ID 1 exists
                    'id_asesi' => $testAsesi['id_asesi'],
                    'id_pengajuan' => $pengajuan['id_pengajuan'],
                    'tanggal_observasi' => date('Y-m-d')
                ];

                echo "   Testing save with data:\n";
                foreach ($testData as $key => $value) {
                    echo "   - {$key}: {$value}\n";
                }

                $saveResult = $observasiService->saveObservation($testData);

                if ($saveResult['success']) {
                    echo "   ✓ Save successful!\n";
                } else {
                    echo "   ❌ Save failed: {$saveResult['message']}\n";
                }
            } else {
                echo "   ❌ No pengajuan found for asesi\n";
            }
        } else {
            echo "   ❌ No asesi found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
