<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once __DIR__ . "/../test_db.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["user_id"])) {
    echo json_encode(["success" => false, "error" => "Missing user_id"]);
    exit;
}

$userId = (int)$input["user_id"];

$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
try {
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(["success" => false, "error" => "User not found"]);
    } else {
        echo json_encode(["success" => true]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
