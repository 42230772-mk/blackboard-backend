<?php
// instructor_delete_office_hour.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Only POST method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);
$slot_id = $input["slot_id"] ?? null;

if (!$slot_id) {
    echo json_encode(["success" => false, "error" => "Missing required field: slot_id"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Ensure the slot belongs to this instructor
    $check = $pdo->prepare("SELECT slot_id FROM office_hour_slot WHERE slot_id = ? AND instructor_id = ?");
    $check->execute([$slot_id, $instructor_id]);
    $owned = $check->fetch(PDO::FETCH_ASSOC);

    if (!$owned) {
        echo json_encode(["success" => false, "error" => "You are not allowed to delete this office hour slot"]);
        exit;
    }

    // ✅ Delete slot
    $stmt = $pdo->prepare("DELETE FROM office_hour_slot WHERE slot_id = ?");
    $stmt->execute([$slot_id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
