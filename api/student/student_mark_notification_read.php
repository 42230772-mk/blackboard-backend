<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

include_once("../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["student_id"]) || !isset($data["notification_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id or notification_id"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);
$notification_id = intval($data["notification_id"]);

if ($student_id <= 0 || $notification_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input"
    ]);
    exit;
}

try {
    // ✅ Update only if it belongs to this student
    $sql = "
        UPDATE notification
        SET is_read = 1
        WHERE notification_id = ?
          AND recipient_id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$notification_id, $student_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Notification not found or not allowed"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Notification marked as read"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
    exit;
}
?>
