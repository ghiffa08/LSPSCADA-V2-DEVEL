<?php

// Simple test to check if the collation fix works
require_once 'vendor/autoload.php';

// Set environment for CodeIgniter
define('ENVIRONMENT', 'development');

try {
    echo "Testing database collation fixes...\n\n";

    // Test basic database connection
    $db = \Config\Database::connect();
    echo "✓ Database connection successful\n";

    // Test a simple query on observasi table
    $result = $db->table('observasi')->limit(1)->get()->getResultArray();
    echo "✓ Basic observasi table query successful\n";

    // Test joining observasi with asesi (where collation mismatch occurs)
    $query = $db->table('observasi')
        ->select('observasi.id_observasi, asesi.id_asesi')
        ->join('asesi', 'asesi.id_asesi = observasi.id_asesi COLLATE utf8mb4_general_ci')
        ->limit(1)
        ->get();

    $result = $query->getResultArray();
    echo "✓ Collation fix test successful - observasi + asesi join works\n";

    // Test joining observasi with pengajuan_asesmen
    $query2 = $db->table('observasi')
        ->select('observasi.id_observasi, pengajuan_asesmen.id_skema')
        ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_asesi = observasi.id_asesi COLLATE utf8mb4_general_ci')
        ->limit(1)
        ->get();

    $result2 = $query2->getResultArray();
    echo "✓ Collation fix test successful - observasi + pengajuan_asesmen join works\n";

    echo "\n🎉 All collation fixes appear to be working correctly!\n";
    echo "The ObservasiModel should now work without collation errors.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";

    if (strpos($e->getMessage(), 'collation') !== false) {
        echo "This is still a collation error. You may need to:\n";
        echo "1. Update your database schema to use consistent collations\n";
        echo "2. Or adjust the COLLATE clauses in the model\n";
    }
}
