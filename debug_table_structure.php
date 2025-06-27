<?php

require_once 'vendor/autoload.php';

// Load CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== CHECKING OBSERVASI TABLE STRUCTURE ===\n\n";

try {
    $db = \Config\Database::connect();

    // Check observasi table structure
    echo "1. OBSERVASI TABLE STRUCTURE:\n";
    $fields = $db->getFieldData('observasi');
    foreach ($fields as $field) {
        echo "   - {$field->name} ({$field->type}";
        if ($field->max_length) {
            echo ", max_length: {$field->max_length}";
        }
        if ($field->nullable) {
            echo ", nullable";
        }
        echo ")\n";
    }

    // Check detail_observasi table structure
    echo "\n2. DETAIL_OBSERVASI TABLE STRUCTURE:\n";
    $detailFields = $db->getFieldData('detail_observasi');
    foreach ($detailFields as $field) {
        echo "   - {$field->name} ({$field->type}";
        if ($field->max_length) {
            echo ", max_length: {$field->max_length}";
        }
        if ($field->nullable) {
            echo ", nullable";
        }
        echo ")\n";
    }

    // Test basic insert into observasi table
    echo "\n3. TESTING BASIC INSERT:\n";

    // Get sample data
    $asesor = $db->table('asesor')->limit(1)->get()->getRowArray();
    $asesi = $db->table('asesi')->limit(1)->get()->getRowArray();
    $pengajuan = $db->table('pengajuan_asesmen')->limit(1)->get()->getRowArray();

    if ($asesor && $asesi && $pengajuan) {
        $testData = [
            'id_asesor' => $asesor['id_asesor'],
            'id_asesi' => $asesi['id_asesi'],
            'id_pengajuan' => $pengajuan['id_pengajuan'],
            'tanggal_observasi' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 'draft'
        ];

        echo "   Test data: " . json_encode($testData) . "\n";

        // Test direct insert
        $insertResult = $db->table('observasi')->insert($testData);

        if ($insertResult) {
            $insertedId = $db->insertID();
            echo "   ✓ Direct insert successful, ID: {$insertedId}\n";

            // Clean up
            $db->table('observasi')->where('id_observasi', $insertedId)->delete();
            echo "   ✓ Test record cleaned up\n";
        } else {
            $error = $db->error();
            echo "   ❌ Direct insert failed: " . json_encode($error) . "\n";
        }
    } else {
        echo "   ❌ Missing sample data for test\n";
    }

    // Check database connection and transaction support
    echo "\n4. DATABASE INFO:\n";
    echo "   Database: " . $db->getDatabase() . "\n";
    echo "   Platform: " . $db->getPlatform() . "\n";
    echo "   Version: " . $db->getVersion() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
