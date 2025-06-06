<?php
// Simple role test
echo "=== Simple Role Test ===\n";

// Manual database query
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lsp_scada_app_devel";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Database connected\n\n";

    // Check users table
    $stmt = $pdo->query("SELECT id, email, nama_lengkap, role FROM users WHERE id IN (1, 4, 7) ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== Users ===\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Email: {$user['email']}, Name: {$user['nama_lengkap']}, Role: {$user['role']}\n";
    }

    echo "\n=== Auth Groups Users ===\n";
    $stmt = $pdo->query("
        SELECT agu.user_id, agu.group_id, ag.name as group_name 
        FROM auth_groups_users agu 
        LEFT JOIN auth_groups ag ON agu.group_id = ag.id 
        ORDER BY agu.user_id
    ");
    $groupUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($groupUsers as $row) {
        echo "User {$row['user_id']} -> Group {$row['group_id']} ({$row['group_name']})\n";
    }

    echo "\n=== Test specific asesi users ===\n";
    $stmt = $pdo->query("
        SELECT u.id, u.email, u.nama_lengkap, u.role, agu.group_id, ag.name as group_name
        FROM users u
        LEFT JOIN auth_groups_users agu ON u.id = agu.user_id
        LEFT JOIN auth_groups ag ON agu.group_id = ag.id
        WHERE u.id IN (4, 5, 6, 16)
        ORDER BY u.id
    ");
    $asesiUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($asesiUsers as $user) {
        echo "Asesi User: ID={$user['id']}, Role={$user['role']}, Group={$user['group_name']} (ID:{$user['group_id']})\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

echo "\n=== Test Complete ===\n";
