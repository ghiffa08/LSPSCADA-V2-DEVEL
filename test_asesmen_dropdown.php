<?php

/**
 * Test Script untuk Verifikasi Dropdown Asesmen
 * Memverifikasi bahwa data asesmen tersedia untuk dropdown
 */

// Simulasi environment CodeIgniter
require_once 'vendor/autoload.php';

// Database connection (gunakan konfigurasi yang sama dengan app)
$config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'lsp_scada_app_devel',
    'driver'   => 'mysqli'
];

try {
    $mysqli = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    echo "<h1>🧪 Test Dropdown Asesmen</h1>\n";
    echo "<hr>\n";

    // Test 1: Verifikasi data asesor
    echo "<h2>1. 👤 Data Asesor (id_user=44)</h2>\n";
    $result = $mysqli->query("
        SELECT a.*, s.nama_skema, s.kode_skema 
        FROM asesor a 
        LEFT JOIN skema s ON a.id_skema = s.id_skema 
        WHERE a.id_user = 44
    ");

    if ($asesor = $result->fetch_assoc()) {
        echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "✅ <strong>Asesor Found:</strong><br>\n";
        echo "ID Asesor: {$asesor['id_asesor']}<br>\n";
        echo "ID Skema: {$asesor['id_skema']}<br>\n";
        echo "Nama Skema: {$asesor['nama_skema']}<br>\n";
        echo "Kode Skema: {$asesor['kode_skema']}<br>\n";
        echo "</div>\n";

        $id_skema = $asesor['id_skema'];
    } else {
        echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "❌ <strong>Asesor tidak ditemukan!</strong>\n";
        echo "</div>\n";
        exit;
    }

    // Test 2: Query asesmen seperti di controller
    echo "<h2>2. 📋 Query Asesmen untuk Dropdown</h2>\n";

    // Query 1: JOIN seperti di controller yang sudah diperbaiki
    $sql_join = "
        SELECT DISTINCT 
            a.id_asesmen,
            a.tujuan,
            a.id_skema,
            s.nama_skema,
            s.kode_skema
        FROM asesmen a
        LEFT JOIN skema s ON a.id_skema = s.id_skema  
        WHERE a.id_skema = ?
        ORDER BY a.id_asesmen ASC
    ";

    $stmt = $mysqli->prepare($sql_join);
    $stmt->bind_param("i", $id_skema);
    $stmt->execute();
    $result_join = $stmt->get_result();

    echo "<h3>🔗 Query dengan JOIN:</h3>\n";
    echo "<pre>" . htmlspecialchars($sql_join) . "</pre>\n";

    $asesmen_data = [];
    if ($result_join->num_rows > 0) {
        echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "✅ <strong>Data asesmen ditemukan:</strong><br>\n";

        while ($row = $result_join->fetch_assoc()) {
            $asesmen_data[] = $row;
            echo "- ID: {$row['id_asesmen']}, Tujuan: {$row['tujuan']}, Skema: {$row['nama_skema']}<br>\n";
        }
        echo "</div>\n";
    } else {
        echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeaa7;border-radius:5px;margin:10px 0;'>\n";
        echo "⚠️ <strong>Tidak ada data asesmen ditemukan dengan JOIN query</strong>\n";
        echo "</div>\n";
    }

    // Test 3: Simulasi dropdown HTML
    echo "<h2>3. 🎛️ Preview Dropdown HTML</h2>\n";

    if (!empty($asesmen_data)) {
        echo "<div style='background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:5px;'>\n";
        echo "<label><strong>Pilih Asesmen:</strong></label><br>\n";
        echo "<select name='id_asesmen' style='width:100%;padding:8px;margin:5px 0;'>\n";
        echo "<option value=''>-- Pilih Asesmen --</option>\n";

        foreach ($asesmen_data as $asesmen) {
            echo "<option value='{$asesmen['id_asesmen']}'>";
            echo htmlspecialchars($asesmen['tujuan'] . ' - ' . $asesmen['nama_skema']);
            echo "</option>\n";
        }

        echo "</select>\n";
        echo "</div>\n";

        echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "✅ <strong>Dropdown berhasil dibuat dengan " . count($asesmen_data) . " pilihan asesmen!</strong>\n";
        echo "</div>\n";
    } else {
        echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "❌ <strong>Dropdown kosong - tidak ada data asesmen!</strong>\n";
        echo "</div>\n";
    }

    // Test 4: Test query fallback
    echo "<h2>4. 🔄 Test Query Fallback</h2>\n";

    $sql_simple = "SELECT * FROM asesmen WHERE id_skema = ?";
    $stmt2 = $mysqli->prepare($sql_simple);
    $stmt2->bind_param("i", $id_skema);
    $stmt2->execute();
    $result_simple = $stmt2->get_result();

    echo "<h3>🎯 Query Simple:</h3>\n";
    echo "<pre>" . htmlspecialchars($sql_simple) . "</pre>\n";

    if ($result_simple->num_rows > 0) {
        echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "✅ <strong>Fallback query berhasil: " . $result_simple->num_rows . " record</strong>\n";
        echo "</div>\n";
    } else {
        echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "❌ <strong>Fallback query juga kosong!</strong>\n";
        echo "</div>\n";
    }

    // Summary
    echo "<h2>5. 📊 Summary</h2>\n";
    echo "<div style='background:#e3f2fd;padding:15px;border:1px solid #90caf9;border-radius:5px;'>\n";
    echo "<strong>Status Perbaikan:</strong><br>\n";

    if (!empty($asesmen_data)) {
        echo "🟢 <strong>BERHASIL:</strong> Dropdown asesmen sudah terisi dengan data yang benar<br>\n";
        echo "📋 Total asesmen tersedia: " . count($asesmen_data) . "<br>\n";
        echo "🎯 Query controller berfungsi dengan baik<br>\n";
        echo "✨ Masalah dropdown kosong sudah teratasi!<br>\n";
    } else {
        echo "🔴 <strong>MASIH ADA MASALAH:</strong> Dropdown masih kosong<br>\n";
        echo "🔍 Perlu investigasi lebih lanjut<br>\n";
    }
    echo "</div>\n";

    $mysqli->close();
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
}

echo "<br><small>🕒 Test completed at: " . date('Y-m-d H:i:s') . "</small>\n";
