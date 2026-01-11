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

if (!isset($data["student_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);

try {
    $sql = "
        SELECT COUNT(*) AS unread_count
        FROM message m
        INNER JOIN conversation_member cm 
            ON cm.conversation_id = m.conversation_id
        WHERE cm.user_id = ?
          AND m.sender_id != ?
          AND m.is_read = 0
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $student_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "unread_count" => intval($row["unread_count"])
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
