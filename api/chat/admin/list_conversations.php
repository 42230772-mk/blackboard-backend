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

if (!isset($data["admin_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id"
    ]);
    exit;
}

$admin_id = intval($data["admin_id"]);

try {
    // ✅ Verify admin
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

    // ✅ Fetch conversations
    $sql = "
        SELECT
            c.conversation_id,
            c.type,
            c.title,
            c.created_at,

            -- last message
            lm.body AS last_message,
            lm.sent_at AS last_message_time,

            -- unread count
            (
                SELECT COUNT(*)
                FROM message m2
                WHERE m2.conversation_id = c.conversation_id
                  AND m2.sender_id != ?
                  AND m2.is_read = 0
            ) AS unread_count,

            -- other user
            u.user_id AS other_user_id,
            u.first_name AS other_first_name,
            u.last_name AS other_last_name,
            u.email AS other_email,
            u.role AS other_role

        FROM conversation_member cm
        JOIN conversation c
            ON c.conversation_id = cm.conversation_id

        JOIN conversation_member cm2
            ON cm2.conversation_id = c.conversation_id
           AND cm2.user_id != ?

        JOIN users u
            ON u.user_id = cm2.user_id

        LEFT JOIN message lm
            ON lm.message_id = (
                SELECT m3.message_id
                FROM message m3
                WHERE m3.conversation_id = c.conversation_id
                ORDER BY m3.sent_at DESC
                LIMIT 1
            )

        WHERE cm.user_id = ?
        ORDER BY lm.sent_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $admin_id, $admin_id]);
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
    exit;
}
?>
