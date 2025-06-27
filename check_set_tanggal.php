<?php

// Check set_tanggal table structure in detail
try {
    $db = new mysqli('localhost', 'root', '', 'lsp_scada_app_devel');

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    echo "=== DETAILED set_tanggal TABLE ANALYSIS ===\n\n";

    $result = $db->query("DESCRIBE set_tanggal");
    echo "set_tanggal columns:\n";
    while ($field = $result->fetch_assoc()) {
        echo sprintf(
            "%-20s %-20s %-10s %s\n",
            $field['Field'],
            $field['Type'],
            $field['Key'],
            $field['Extra']
        );
    }

    echo "\n=== SAMPLE DATA FROM set_tanggal ===\n";
    $result = $db->query("SELECT * FROM set_tanggal LIMIT 3");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "No data found in set_tanggal\n";
    }

    echo "\n=== CHECK asesmen TABLE for tanggal relation ===\n";
    $result = $db->query("DESCRIBE asesmen");
    while ($field = $result->fetch_assoc()) {
        if (strpos($field['Field'], 'tanggal') !== false) {
            echo sprintf(
                "%-20s %-20s %-10s %s\n",
                $field['Field'],
                $field['Type'],
                $field['Key'],
                $field['Extra']
            );
        }
    }

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
