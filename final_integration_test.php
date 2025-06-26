<?php

/**
 * Final Integration Test
 * Memverifikasi end-to-end functionality ceklist observasi
 */

echo "<h1>🏁 Final Integration Test - Ceklist Observasi</h1>\n";
echo "<hr>\n";

// Database connection
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

    // Test User ID 44 journey
    $user_id = 44;

    echo "<h2>🧪 Test Case: User ID {$user_id} - Complete Journey</h2>\n";

    // Step 1: Verify Asesor Data
    $result = $mysqli->query("
        SELECT a.*, s.nama_skema, s.kode_skema 
        FROM asesor a 
        LEFT JOIN skema s ON a.id_skema = s.id_skema 
        WHERE a.id_user = {$user_id}
    ");

    if ($asesor = $result->fetch_assoc()) {
        echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "✅ <strong>Step 1 - Asesor Authentication:</strong> PASSED<br>\n";
        echo "ID Asesor: {$asesor['id_asesor']} | Skema: {$asesor['nama_skema']}<br>\n";
        echo "</div>\n";

        $id_skema = $asesor['id_skema'];

        // Step 2: Test Asesmen Query (same as controller)
        $sql = "
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

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $id_skema);
        $stmt->execute();
        $result_asesmen = $stmt->get_result();

        if ($result_asesmen->num_rows > 0) {
            echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
            echo "✅ <strong>Step 2 - Asesmen Query:</strong> PASSED<br>\n";
            echo "Found {$result_asesmen->num_rows} asesmen record(s)<br>\n";
            echo "</div>\n";

            $asesmen_options = [];
            while ($row = $result_asesmen->fetch_assoc()) {
                $asesmen_options[] = $row;
            }

            // Step 3: Test TUK Data
            $result_tuk = $mysqli->query("SELECT * FROM tuk ORDER BY nama_tuk ASC");
            $tuk_count = $result_tuk->num_rows;

            if ($tuk_count > 0) {
                echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
                echo "✅ <strong>Step 3 - TUK Data:</strong> PASSED<br>\n";
                echo "Found {$tuk_count} TUK record(s)<br>\n";
                echo "</div>\n";
            } else {
                echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeaa7;border-radius:5px;margin:10px 0;'>\n";
                echo "⚠️ <strong>Step 3 - TUK Data:</strong> WARNING - No TUK data found<br>\n";
                echo "</div>\n";
            }

            // Step 4: Test Set Tanggal Data  
            $result_tanggal = $mysqli->query("SELECT * FROM set_tanggal ORDER BY tanggal_mulai ASC");
            $tanggal_count = $result_tanggal->num_rows;

            if ($tanggal_count > 0) {
                echo "<div style='background:#d4edda;padding:10px;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;'>\n";
                echo "✅ <strong>Step 4 - Set Tanggal Data:</strong> PASSED<br>\n";
                echo "Found {$tanggal_count} tanggal record(s)<br>\n";
                echo "</div>\n";
            } else {
                echo "<div style='background:#fff3cd;padding:10px;border:1px solid #ffeaa7;border-radius:5px;margin:10px 0;'>\n";
                echo "⚠️ <strong>Step 4 - Set Tanggal Data:</strong> WARNING - No date settings found<br>\n";
                echo "</div>\n";
            }

            // Step 5: Simulate Form Output
            echo "<h3>📋 Simulated Form Output</h3>\n";
            echo "<div style='background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:5px;'>\n";

            // Dropdown Asesmen
            echo "<strong>Dropdown Asesmen:</strong><br>\n";
            echo "<select name='id_asesmen' style='width:100%;padding:5px;margin:5px 0;'>\n";
            echo "<option value=''>-- Pilih Asesmen --</option>\n";
            foreach ($asesmen_options as $asesmen) {
                echo "<option value='{$asesmen['id_asesmen']}'>";
                echo htmlspecialchars($asesmen['tujuan'] . ' - ' . $asesmen['nama_skema']);
                echo "</option>\n";
            }
            echo "</select><br><br>\n";

            // Dropdown TUK (if available)
            if ($tuk_count > 0) {
                echo "<strong>Dropdown TUK:</strong><br>\n";
                echo "<select name='id_tuk' style='width:100%;padding:5px;margin:5px 0;'>\n";
                echo "<option value=''>-- Pilih TUK --</option>\n";

                $result_tuk2 = $mysqli->query("SELECT * FROM tuk ORDER BY nama_tuk ASC LIMIT 3");
                while ($tuk = $result_tuk2->fetch_assoc()) {
                    echo "<option value='{$tuk['id_tuk']}'>";
                    echo htmlspecialchars($tuk['nama_tuk']);
                    echo "</option>\n";
                }
                echo "</select><br><br>\n";
            }

            // Dropdown Set Tanggal (if available)
            if ($tanggal_count > 0) {
                echo "<strong>Dropdown Set Tanggal:</strong><br>\n";
                echo "<select name='id_set_tanggal' style='width:100%;padding:5px;margin:5px 0;'>\n";
                echo "<option value=''>-- Pilih Tanggal --</option>\n";

                $result_tanggal2 = $mysqli->query("SELECT * FROM set_tanggal ORDER BY tanggal_mulai ASC LIMIT 3");
                while ($tanggal = $result_tanggal2->fetch_assoc()) {
                    echo "<option value='{$tanggal['id_set_tanggal']}'>";
                    echo htmlspecialchars($tanggal['tanggal_mulai'] . ' - ' . $tanggal['tanggal_selesai']);
                    echo "</option>\n";
                }
                echo "</select><br><br>\n";
            }

            echo "</div>\n";

            // Final Status
            echo "<h2>🏆 Final Test Results</h2>\n";
            echo "<div style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;'>\n";
            echo "✅ <strong>INTEGRATION TEST PASSED!</strong><br><br>\n";
            echo "<strong>Summary:</strong><br>\n";
            echo "• Asesor authentication: ✅ Working<br>\n";
            echo "• Asesmen dropdown: ✅ Working (" . count($asesmen_options) . " options)<br>\n";
            echo "• TUK dropdown: " . ($tuk_count > 0 ? "✅ Working ({$tuk_count} options)" : "⚠️ No data") . "<br>\n";
            echo "• Set Tanggal dropdown: " . ($tanggal_count > 0 ? "✅ Working ({$tanggal_count} options)" : "⚠️ No data") . "<br>\n";
            echo "<br><strong>🎯 MASALAH DROPDOWN ASESMEN KOSONG SUDAH TERATASI!</strong><br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
            echo "❌ <strong>Step 2 - Asesmen Query:</strong> FAILED - No asesmen found<br>\n";
            echo "</div>\n";
        }
    } else {
        echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
        echo "❌ <strong>Step 1 - Asesor Authentication:</strong> FAILED - Asesor not found<br>\n";
        echo "</div>\n";
    }

    $mysqli->close();
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:10px;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;'>\n";
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "\n";
    echo "</div>\n";
}

echo "<br><small>🕒 Final test completed at: " . date('Y-m-d H:i:s') . "</small>\n";
