<?php
// ---- CORS (MUST BE FIRST, NO SPACE BEFORE <?php) ----
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . "/../config.php";

// Check if department_id is provided
if (!isset($_GET["department_id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing department_id"
    ]);
    exit;
}

$departmentId = (int) $_GET["department_id"];

try {
    $stmt = $pdo->prepare("
        SELECT major_id, name
        FROM major
        WHERE department_id = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$departmentId]);

    echo json_encode([
        "success" => true,
        "majors" => $stmt->fetchAll()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch majors"
    ]);
}
