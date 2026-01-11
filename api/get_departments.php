<?php
// ---- CORS ----
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// ---- DATABASE ----
require_once __DIR__ . "/../test_db.php";

try {
    $stmt = $pdo->query("
        SELECT department_id, name
        FROM department
        ORDER BY name ASC
    ");

    echo json_encode([
        "success" => true,
        "departments" => $stmt->fetchAll()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch departments"
    ]);
}
