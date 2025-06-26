<?php

/**
 * Cleanup Script - Remove Debug Files
 * Menghapus file-file debugging setelah testing selesai
 */

echo "<h1>🧹 Cleanup Debug Files</h1>\n";
echo "<hr>\n";

$debug_files = [
    'debug_asesmen_skema.php',
    'test_asesmen_dropdown.php',
    'final_integration_test.php',
    'cleanup_debug_files.php' // This file itself
];

$kept_files = [
    'DROPDOWN_ASESMEN_FIX_FINAL_REPORT.md' // Keep the final report
];

echo "<h2>📋 Files to Remove:</h2>\n";
echo "<ul>\n";
foreach ($debug_files as $file) {
    echo "<li>{$file}</li>\n";
}
echo "</ul>\n";

echo "<h2>📄 Files to Keep:</h2>\n";
echo "<ul>\n";
foreach ($kept_files as $file) {
    echo "<li>{$file}</li>\n";
}
echo "</ul>\n";

echo "<h2>🔥 Cleanup Process:</h2>\n";

$removed_count = 0;
$error_count = 0;

foreach ($debug_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<div style='background:#d4edda;padding:5px;margin:5px 0;border-radius:3px;'>\n";
            echo "✅ Removed: {$file}\n";
            echo "</div>\n";
            $removed_count++;
        } else {
            echo "<div style='background:#f8d7da;padding:5px;margin:5px 0;border-radius:3px;'>\n";
            echo "❌ Failed to remove: {$file}\n";
            echo "</div>\n";
            $error_count++;
        }
    } else {
        echo "<div style='background:#fff3cd;padding:5px;margin:5px 0;border-radius:3px;'>\n";
        echo "⚠️ File not found: {$file}\n";
        echo "</div>\n";
    }
}

echo "<h2>📊 Cleanup Summary:</h2>\n";
echo "<div style='background:#e3f2fd;padding:15px;border:1px solid #90caf9;border-radius:5px;'>\n";
echo "<strong>Results:</strong><br>\n";
echo "• Files removed: {$removed_count}<br>\n";
echo "• Errors: {$error_count}<br>\n";
echo "• Total processed: " . count($debug_files) . "<br>\n";

if ($error_count === 0) {
    echo "<br>🎉 <strong>Cleanup completed successfully!</strong><br>\n";
    echo "📁 Final report tersimpan di: DROPDOWN_ASESMEN_FIX_FINAL_REPORT.md<br>\n";
} else {
    echo "<br>⚠️ <strong>Cleanup completed with errors</strong><br>\n";
    echo "🔍 Please check file permissions<br>\n";
}

echo "</div>\n";

echo "<br><h2>🎯 Next Steps:</h2>\n";
echo "<ol>\n";
echo "<li>✅ Review final report: <strong>DROPDOWN_ASESMEN_FIX_FINAL_REPORT.md</strong></li>\n";
echo "<li>🧪 Conduct User Acceptance Testing with real asesor</li>\n";
echo "<li>📊 Monitor application performance</li>\n";
echo "<li>📝 Update system documentation</li>\n";
echo "</ol>\n";

echo "<br><small>🕒 Cleanup completed at: " . date('Y-m-d H:i:s') . "</small>\n";

// Self-destruct after 5 seconds (commented for safety)
// echo "<script>setTimeout(() => { window.location.reload(); }, 5000);</script>\n";
