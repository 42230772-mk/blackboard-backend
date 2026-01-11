<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once("../../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["student_id"]) || !isset($data["conversation_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id or conversation_id"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);
$conversation_id = intval($data["conversation_id"]);

try {
    // ✅ Permission check: student must be in conversation
    $permSql = "
        SELECT 1
        FROM conversation_member
        WHERE conversation_id = ? AND user_id = ?
        LIMIT 1
    ";
    $permStmt = $pdo->prepare($permSql);
    $permStmt->execute([$conversation_id, $student_id]);

    if (!$permStmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: student not in this conversation"
        ]);
        exit;
    }

    // ✅ Mark messages as read (only messages NOT sent by student)
    $updateSql = "
        UPDATE message
        SET is_read = 1
        WHERE conversation_id = ?
          AND sender_id != ?
          AND is_read = 0
    ";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$conversation_id, $student_id]);

    $updated_count = $updateStmt->rowCount();

    echo json_encode([
        "success" => true,
        "message" => "Messages marked as read",
        "updated_count" => $updated_count
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
