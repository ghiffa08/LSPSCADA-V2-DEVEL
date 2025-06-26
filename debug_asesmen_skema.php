<?php

// Database Investigation Script untuk Dropdown Asesmen Kosong
$host = 'localhost';
$database = 'lsp_scada_app_devel';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>🔍 Database Investigation: Dropdown Asesmen Kosong</h1>";
    echo "<hr>";

    // 1. Verifikasi Data Asesor
    echo "<h2>1. ✅ Verifikasi Data Asesor</h2>";
    $stmt = $pdo->query("SELECT * FROM asesor WHERE id_user = 44");
    $asesor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asesor) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>✅ Asesor Found:</strong></p>";
        echo "<ul>";
        echo "<li>ID Asesor: " . $asesor['id_asesor'] . "</li>";
        echo "<li>ID User: " . $asesor['id_user'] . "</li>";
        echo "<li>Nomor Registrasi: " . $asesor['nomor_registrasi'] . "</li>";
        echo "<li>ID Skema: " . $asesor['id_skema'] . "</li>";
        echo "</ul>";
        echo "</div>";

        $id_skema = $asesor['id_skema'];
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>❌ Asesor dengan ID User 44 tidak ditemukan!</strong></p>";
        echo "</div>";
        exit;
    }

    // 2. Verifikasi Data Skema
    echo "<h2>2. ✅ Verifikasi Data Skema</h2>";
    $stmt = $pdo->prepare("SELECT * FROM skema WHERE id_skema = ?");
    $stmt->execute([$id_skema]);
    $skema = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($skema) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>✅ Skema Found:</strong></p>";
        echo "<ul>";
        echo "<li>ID Skema: " . $skema['id_skema'] . "</li>";
        echo "<li>Nama Skema: " . $skema['nama_skema'] . "</li>";
        echo "<li>Kode Skema: " . $skema['kode_skema'] . "</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>❌ Skema dengan ID $id_skema tidak ditemukan!</strong></p>";
        echo "</div>";
        exit;
    }

    // 3. CRITICAL: Cek Data Asesmen
    echo "<h2>3. ❓ Investigasi Data Asesmen</h2>";
    $stmt = $pdo->prepare("SELECT * FROM asesmen WHERE id_skema = ?");
    $stmt->execute([$id_skema]);
    $asesmen_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p><strong>Jumlah asesmen untuk skema ID $id_skema:</strong> " . count($asesmen_data) . "</p>";

    if (empty($asesmen_data)) {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>⚠️ MASALAH DITEMUKAN: Tidak ada data asesmen untuk skema ID $id_skema!</strong></p>";
        echo "<p>Ini adalah penyebab dropdown kosong.</p>";
        echo "</div>";

        // Check struktur tabel asesmen
        echo "<h3>📋 Struktur Tabel Asesmen</h3>";
        $stmt = $pdo->query("DESCRIBE asesmen");
        $struktur = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($struktur as $field) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Check dependencies
        echo "<h3>🔗 Check Dependencies</h3>";

        // Check TUK
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM tuk");
        $tuk_count = $stmt->fetch()['count'];
        echo "<p>📍 Data TUK: $tuk_count records</p>";

        if ($tuk_count == 0) {
            echo "<p style='color: orange;'>⚠️ Membuat data TUK...</p>";
            $pdo->exec("INSERT INTO tuk (nama_tuk, alamat_tuk) VALUES ('TUK LSP SCADA Utama', 'Jakarta Pusat')");
            echo "<p style='color: green;'>✅ TUK created</p>";
        }

        // Check set_tanggal atau tanggal_asesmen
        $tables = ['set_tanggal', 'tanggal_asesmen', 'jadwal_asesmen'];
        $tanggal_table = null;
        $tanggal_id = null;

        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                echo "<p>📅 Data $table: $count records</p>";

                if ($count > 0) {
                    $tanggal_table = $table;
                    // Get first ID
                    $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
                    $first_record = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tanggal_id = $first_record[array_keys($first_record)[0]]; // First column (usually ID)
                    break;
                }
            } catch (Exception $e) {
                echo "<p style='color: gray;'>📅 Tabel $table tidak ada</p>";
            }
        }

        if (!$tanggal_table) {
            echo "<p style='color: orange;'>⚠️ Membuat data set_tanggal...</p>";
            $currentDate = date('Y-m-d');
            $pdo->exec("INSERT INTO set_tanggal (tanggal, keterangan) VALUES ('$currentDate', 'Periode Asesmen Default')");
            $tanggal_table = 'set_tanggal';
            $tanggal_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>✅ set_tanggal created dengan ID: $tanggal_id</p>";
        }

        // Get TUK ID
        $stmt = $pdo->query("SELECT * FROM tuk LIMIT 1");
        $tuk = $stmt->fetch(PDO::FETCH_ASSOC);
        $tuk_id = $tuk['id_tuk'];

        // Create sample asesmen data
        echo "<h3>🚀 Membuat Data Asesmen Sample</h3>";

        $sample_asesmen = [
            "Asesmen Teori - Dasar Programming",
            "Asesmen Praktik - Web Development",
            "Asesmen Praktik - Database Management",
            "Asesmen Project - Final Assessment"
        ];

        // Determine correct column names for tanggal
        $tanggal_field = ($tanggal_table == 'set_tanggal') ? 'id_tanggal' : 'id_' . $tanggal_table;

        foreach ($sample_asesmen as $tujuan) {
            try {
                $sql = "INSERT INTO asesmen (tujuan, id_skema, id_tuk, $tanggal_field) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tujuan, $id_skema, $tuk_id, $tanggal_id]);
                echo "<p style='color: green;'>✅ Created: $tujuan</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error creating '$tujuan': " . $e->getMessage() . "</p>";
                // Try alternative column name
                try {
                    $alt_field = 'id_set_tanggal';
                    $sql = "INSERT INTO asesmen (tujuan, id_skema, id_tuk, $alt_field) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$tujuan, $id_skema, $tuk_id, $tanggal_id]);
                    echo "<p style='color: green;'>✅ Created with alternative field: $tujuan</p>";
                } catch (Exception $e2) {
                    echo "<p style='color: red;'>❌ Also failed with alternative: " . $e2->getMessage() . "</p>";
                }
            }
        }
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>✅ Data asesmen ditemukan:</strong></p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Tujuan</th><th>ID Skema</th><th>ID TUK</th></tr>";
        foreach ($asesmen_data as $asesmen) {
            echo "<tr>";
            echo "<td>" . $asesmen['id_asesmen'] . "</td>";
            echo "<td>" . $asesmen['tujuan'] . "</td>";
            echo "<td>" . $asesmen['id_skema'] . "</td>";
            echo "<td>" . ($asesmen['id_tuk'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }

    // 4. Test Query Controller
    echo "<h2>4. 🧪 Test Query Controller</h2>";

    // Simulasi query yang digunakan di controller
    try {
        $sql = "SELECT asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, 
                       skema.nama_skema, skema.kode_skema
                FROM asesmen 
                LEFT JOIN skema ON asesmen.id_skema = skema.id_skema 
                WHERE asesmen.id_skema = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_skema]);
        $controller_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<p><strong>Query Controller Result:</strong> " . count($controller_result) . " records</p>";

        if (!empty($controller_result)) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<p><strong>✅ Controller query berhasil!</strong></p>";
            echo "<h4>Preview Dropdown:</h4>";
            echo "<select style='width: 100%; padding: 8px;'>";
            echo "<option value=''>-- Pilih Asesmen --</option>";
            foreach ($controller_result as $item) {
                echo "<option value='" . $item['id_asesmen'] . "'>";
                echo htmlspecialchars($item['tujuan']) . " - " . htmlspecialchars($item['nama_skema']);
                echo "</option>";
            }
            echo "</select>";
            echo "</div>";

            echo "<h4>📊 Raw Data:</h4>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            print_r($controller_result);
            echo "</pre>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<p><strong>❌ Controller query tidak mengembalikan data!</strong></p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>❌ Error dalam query controller:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }

    // 5. Summary & Next Steps
    echo "<h2>5. 📋 Summary & Next Steps</h2>";

    // Re-check after potential data creation
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM asesmen WHERE id_skema = ?");
    $stmt->execute([$id_skema]);
    $final_count = $stmt->fetch()['count'];

    if ($final_count > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
        echo "<h3 style='color: green;'>🎉 MASALAH TERPECAHKAN!</h3>";
        echo "<ul>";
        echo "<li>✅ Data asesmen tersedia: $final_count records</li>";
        echo "<li>✅ Query controller akan berfungsi</li>";
        echo "<li>✅ Dropdown asesmen akan terisi</li>";
        echo "</ul>";
        echo "<p><strong>Silakan refresh halaman ceklist observasi!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h3 style='color: red;'>❌ Masalah Masih Ada</h3>";
        echo "<p>Data asesmen masih belum tersedia. Perlu investigasi lebih lanjut pada:</p>";
        echo "<ul>";
        echo "<li>Struktur tabel asesmen</li>";
        echo "<li>Permissions database</li>";
        echo "<li>Foreign key constraints</li>";
        echo "</ul>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Database Connection Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
