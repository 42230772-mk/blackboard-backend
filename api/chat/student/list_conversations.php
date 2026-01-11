<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once("../../../config.php"); // ✅ Correct path to blackboard-backend/config.php

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
        SELECT 
            c.conversation_id,
            c.type,
            c.title,
            c.created_at,

            (
                SELECT m.body
                FROM message m
                WHERE m.conversation_id = c.conversation_id
                ORDER BY m.sent_at DESC
                LIMIT 1
            ) AS last_message,

            (
                SELECT m.sent_at
                FROM message m
                WHERE m.conversation_id = c.conversation_id
                ORDER BY m.sent_at DESC
                LIMIT 1
            ) AS last_message_time,

            (
                SELECT COUNT(*)
                FROM message m
                WHERE m.conversation_id = c.conversation_id
                  AND m.sender_id != ?
                  AND m.is_read = 0
            ) AS unread_count,

            u.user_id AS other_user_id,
            u.first_name AS other_first_name,
            u.last_name AS other_last_name,
            u.email AS other_email,
            u.role AS other_role

        FROM conversation c
        INNER JOIN conversation_member cm ON cm.conversation_id = c.conversation_id
        INNER JOIN conversation_member cm2 ON cm2.conversation_id = c.conversation_id AND cm2.user_id != ?
        INNER JOIN users u ON u.user_id = cm2.user_id

        WHERE cm.user_id = ?
        ORDER BY last_message_time DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $student_id, $student_id]);

    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "conversations" => $conversations
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
?>
