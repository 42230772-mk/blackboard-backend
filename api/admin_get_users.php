<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once __DIR__ . "/../test_db.php";

try {
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email, role FROM users ORDER BY user_id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Capitalize roles for consistency
    foreach ($users as &$u) {
        $u['role'] = ucfirst($u['role']);
    }

    echo json_encode(["success" => true, "users" => $users]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
