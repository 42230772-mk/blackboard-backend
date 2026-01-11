<?php
// admin_get_notifications.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

$admin_id = (int)$_SESSION["user_id"];

try {
    $stmt = $pdo->prepare("
        SELECT 
            n.notification_id,
            n.recipient_id,
            n.sender_id,
            n.title,
            n.message,
            n.created_at,
            n.is_read,
            u.first_name AS sender_first_name,
            u.last_name AS sender_last_name,
            u.role AS sender_role,

            ep.status AS petition_status

        FROM notification n
        LEFT JOIN users u ON n.sender_id = u.user_id

        LEFT JOIN exam_petition ep 
            ON ep.petition_id = CAST(
                SUBSTRING_INDEX(
                    SUBSTRING_INDEX(n.message, 'Petition ID: ', -1),
                    '\n',
                    1
                ) AS UNSIGNED
            )

        WHERE n.recipient_id = ?
        ORDER BY n.created_at DESC
    ");

    $stmt->execute([$admin_id]);

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "notifications" => $notifications
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
    exit;
}
