<?php
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

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../../test_db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["slot_id"])) {
    echo json_encode(["success" => false, "message" => "Missing slot_id"]);
    exit;
}

$slot_id = intval($data["slot_id"]);
$student_id = intval($_SESSION["user_id"]);

if ($slot_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid slot_id"]);
    exit;
}

try {
    // ✅ Check if slot exists
    $slotStmt = $pdo->prepare("SELECT capacity FROM office_hour_slot WHERE slot_id = ?");
    $slotStmt->execute([$slot_id]);
    $slot = $slotStmt->fetch(PDO::FETCH_ASSOC);

    if (!$slot) {
        echo json_encode(["success" => false, "message" => "Slot not found"]);
        exit;
    }

    $capacity = intval($slot["capacity"]);

    // ✅ Count booked seats
    $countStmt = $pdo->prepare("SELECT COUNT(*) as booked FROM office_hour_booking WHERE slot_id = ? AND status = 'confirmed'");
    $countStmt->execute([$slot_id]);
    $booked = intval($countStmt->fetch(PDO::FETCH_ASSOC)["booked"]);

    $remaining = $capacity - $booked;

    if ($remaining <= 0) {
        echo json_encode(["success" => false, "message" => "Slot is full"]);
        exit;
    }

    // ✅ Prevent duplicate booking
    $dupStmt = $pdo->prepare("SELECT booking_id FROM office_hour_booking WHERE slot_id = ? AND student_id = ? AND status = 'confirmed'");
    $dupStmt->execute([$slot_id, $student_id]);

    if ($dupStmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "You already booked this slot"]);
        exit;
    }

    // ✅ Insert booking
    $bookStmt = $pdo->prepare("INSERT INTO office_hour_booking (slot_id, student_id, status, booked_at) VALUES (?, ?, 'confirmed', NOW())");
    $bookStmt->execute([$slot_id, $student_id]);

    echo json_encode([
        "success" => true,
        "message" => "Slot booked successfully"
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error", "error" => $e->getMessage()]);
    exit;
}
