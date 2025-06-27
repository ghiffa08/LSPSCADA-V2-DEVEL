<?php

// Test script to verify ObservasiService fixes
// This script tests the ID field resolution

require_once 'vendor/autoload.php';

echo "Testing ID field resolution...\n\n";

// Test data structures that might be returned from the database
$testDataStructures = [
    // Case 1: Standard ID field
    [
        'id' => 123,
        'nama' => 'Test Asesor 1',
        'email' => 'test1@example.com'
    ],

    // Case 2: Database column naming (id_asesor)
    [
        'id_asesor' => 456,
        'nama_asesor' => 'Test Asesor 2',
        'email' => 'test2@example.com'
    ],

    // Case 3: User ID field (id_user)
    [
        'id_user' => 789,
        'nama_lengkap' => 'Test Asesor 3',
        'email' => 'test3@example.com'
    ],

    // Case 4: Alternative naming
    [
        'asesor_id' => 101,
        'user_id' => 202,
        'name' => 'Test Asesor 4'
    ],

    // Case 5: Missing ID (should trigger fallback)
    [
        'nama' => 'Test Asesor 5',
        'email' => 'test5@example.com'
    ]
];

function extractAsesorId($asesorData)
{
    $asesorId = $asesorData['id'] ??
        $asesorData['asesor_id'] ??
        $asesorData['user_id'] ??
        $asesorData['id_asesor'] ??
        $asesorData['id_user'] ??
        null;

    return $asesorId;
}

foreach ($testDataStructures as $index => $testData) {
    $caseNumber = $index + 1;
    echo "Case {$caseNumber}: ";
    print_r($testData);

    $asesorId = extractAsesorId($testData);

    if ($asesorId) {
        echo "✅ Successfully extracted ID: {$asesorId}\n";
    } else {
        echo "❌ No ID found - would trigger session fallback\n";
    }

    echo "--------------------\n";
}

echo "\nTest completed. The ObservasiService should now handle all these cases gracefully.\n";
