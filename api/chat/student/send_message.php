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

if (
    !isset($data["student_id"]) ||
    !isset($data["conversation_id"]) ||
    !isset($data["body"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id, conversation_id, or body"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);
$conversation_id = intval($data["conversation_id"]);
$body = trim($data["body"]);

// ✅ NEW: message_type support (default text)
$message_type = isset($data["message_type"]) ? trim($data["message_type"]) : "text";

// ✅ allow only these two for now
$allowed_types = ["text", "file"];
if (!in_array($message_type, $allowed_types)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid message_type (allowed: text, file)"
    ]);
    exit;
}

if ($body === "") {
    echo json_encode([
        "success" => false,
        "message" => "Message body cannot be empty"
    ]);
    exit;
}

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

    // ✅ Insert message (message_type is dynamic now)
    $insertSql = "
        INSERT INTO message (conversation_id, sender_id, body, message_type, is_read)
        VALUES (?, ?, ?, ?, 0)
    ";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        $conversation_id,
        $student_id,
        $body,
        $message_type
    ]);

    $message_id = intval($pdo->lastInsertId());

    // ✅ Fetch inserted message with sender info
    $fetchSql = "
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
        WHERE m.message_id = ?
        LIMIT 1
    ";
    $fetchStmt = $pdo->prepare($fetchSql);
    $fetchStmt->execute([$message_id]);
    $message = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => $message
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
