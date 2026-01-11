<?php
// instructor_get_notifications.php

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

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "error" => "Only GET method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION["user_id"];

require_once(__DIR__ . "/../test_db.php");

try {
    $stmt = $pdo->prepare("
        SELECT 
            n.notification_id,
            n.sender_id,
            CONCAT(u.first_name, ' ', u.last_name) AS sender_name,
            u.role AS sender_role,
            n.title,
            n.message,
            n.created_at,
            n.is_read
        FROM notification n
        INNER JOIN users u ON n.sender_id = u.user_id
        WHERE n.recipient_id = ?
          AND u.role = 'admin'
        ORDER BY n.created_at DESC
    ");

    $stmt->execute([$instructor_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "notifications" => $notifications]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
