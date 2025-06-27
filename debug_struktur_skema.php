<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== CHECKING DATABASE STRUCTURE FOR OBSERVASI ===\n\n";

try {
    $db = \Config\Database::connect();

    // 1. Check skema data
    echo "1. CHECKING SKEMA:\n";
    $skemas = $db->table('skema')->where('status', 'Y')->get()->getResultArray();
    echo "   Active skemas: " . count($skemas) . "\n";

    if (count($skemas) > 0) {
        $testSkema = $skemas[0];
        echo "   Test skema: {$testSkema['id_skema']} - {$testSkema['nama_skema']}\n";

        // 2. Check struktur data untuk skema ini
        echo "\n2. CHECKING SKEMA STRUCTURE:\n";

        // Check kelompok_kerja
        $kelompokKerja = $db->table('kelompok_kerja')
            ->where('id_skema', $testSkema['id_skema'])
            ->get()->getResultArray();
        echo "   Kelompok kerja: " . count($kelompokKerja) . "\n";

        if (count($kelompokKerja) > 0) {
            $kelompokIds = array_column($kelompokKerja, 'id_kelompok');

            // Check kelompok_unit
            $kelompokUnit = $db->table('kelompok_unit')
                ->whereIn('id_kelompok', $kelompokIds)
                ->get()->getResultArray();
            echo "   Kelompok unit: " . count($kelompokUnit) . "\n";

            if (count($kelompokUnit) > 0) {
                $unitIds = array_column($kelompokUnit, 'id_unit');

                // Check unit yang match dengan kelompok dan skema
                $units = $db->table('unit')
                    ->whereIn('id_unit', $unitIds)
                    ->where('id_skema', $testSkema['id_skema'])
                    ->where('status', 'Y')
                    ->get()->getResultArray();
                echo "   Active units matching skema: " . count($units) . "\n";

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

                        if (count($kuks) > 0) {
                            echo "   ✓ Complete structure found!\n";

                            // Test the complex query from ObservasiService
                            echo "\n3. TESTING COMPLEX QUERY:\n";
                            $complexQuery = "
                                SELECT COUNT(*) as total
                                FROM skema s
                                INNER JOIN kelompok_kerja kk ON kk.id_skema = s.id_skema
                                INNER JOIN kelompok_unit ku ON ku.id_kelompok = kk.id_kelompok
                                INNER JOIN unit u ON u.id_unit = ku.id_unit AND u.id_skema = s.id_skema
                                INNER JOIN elemen e ON e.id_unit = u.id_unit AND e.id_skema = s.id_skema
                                INNER JOIN kuk k ON k.id_elemen = e.id_elemen AND k.id_unit = u.id_unit AND k.id_skema = s.id_skema
                                WHERE s.id_skema = {$testSkema['id_skema']} 
                                  AND s.status = 'Y' 
                                  AND u.status = 'Y'
                            ";

                            $result = $db->query($complexQuery)->getRow();
                            echo "   Complex query result: {$result->total} KUKs\n";

                            if ($result->total > 0) {
                                echo "   ✓ ObservasiService query should work!\n";
                            } else {
                                echo "   ❌ Complex query returns 0 - there's a join issue\n";
                            }
                        } else {
                            echo "   ❌ No KUKs found\n";
                        }
                    } else {
                        echo "   ❌ No elemen found\n";
                    }
                } else {
                    echo "   ❌ No active units found\n";
                }
            } else {
                echo "   ❌ No kelompok_unit found\n";
            }
        } else {
            echo "   ❌ No kelompok_kerja found\n";
        }
    } else {
        echo "   ❌ No active skemas found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
