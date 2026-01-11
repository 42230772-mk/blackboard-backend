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
    $permRow = $permStmt->fetch(PDO::FETCH_ASSOC);

    if (!$permRow) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: student not in this conversation"
        ]);
        exit;
    }

    // ✅ Fetch messages
    $msgSql = "
        SELECT 
            m.message_id,
            m.conversation_id,
            m.sender_id,
            u.first_name,
            u.last_name,
            u.role,
            m.body,
            m.message_type,
            m.sent_at,
            m.is_read
        FROM message m
        LEFT JOIN users u ON u.user_id = m.sender_id
        WHERE m.conversation_id = ?
        ORDER BY m.sent_at ASC
    ";

    $msgStmt = $pdo->prepare($msgSql);
    $msgStmt->execute([$conversation_id]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "messages" => $messages
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
