<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once("../../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data["admin_id"]) ||
    !isset($data["conversation_id"]) ||
    !isset($data["body"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id, conversation_id, or body"
    ]);
    exit;
}

$admin_id = intval($data["admin_id"]);
$conversation_id = intval($data["conversation_id"]);
$body = trim($data["body"]);
$message_type = isset($data["message_type"]) ? $data["message_type"] : "text";

if ($body === "") {
    echo json_encode([
        "success" => false,
        "message" => "Message body cannot be empty"
    ]);
    exit;
}

try {
    // ✅ Confirm admin role
    $adminCheck = $pdo->prepare("SELECT role FROM users WHERE user_id = ? LIMIT 1");
    $adminCheck->execute([$admin_id]);
    $admin = $adminCheck->fetch(PDO::FETCH_ASSOC);

    if (!$admin || $admin["role"] !== "admin") {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied"
        ]);
        exit;
    }

    // ✅ Permission: admin must be member of conversation
    $permSql = "
        SELECT 1
        FROM conversation_member
        WHERE conversation_id = ? AND user_id = ?
        LIMIT 1
    ";
    $permStmt = $pdo->prepare($permSql);
    $permStmt->execute([$conversation_id, $admin_id]);

    if (!$permStmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: admin not in this conversation"
        ]);
        exit;
    }

    // ✅ Insert message
    $insertSql = "
        INSERT INTO message (conversation_id, sender_id, body, message_type, is_read)
        VALUES (?, ?, ?, ?, 0)
    ";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        $conversation_id,
        $admin_id,
        $body,
        $message_type
    ]);

    $message_id = intval($pdo->lastInsertId());

    // ✅ Fetch inserted message
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
