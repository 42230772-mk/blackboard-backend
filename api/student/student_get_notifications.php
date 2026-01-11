<?php
// student_get_notifications.php (SESSION BASED)

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Only POST allowed"]);
    exit;
}

// ✅ Session check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$student_id = $_SESSION["user_id"];

require_once(__DIR__ . "/../../test_db.php");

try {
    $sql = "
        SELECT
            n.notification_id,
            n.sender_id,
            n.title,
            n.message,
            n.created_at,
            n.is_read,

            u.first_name AS sender_first_name,
            u.last_name AS sender_last_name,
            u.role AS sender_role
        FROM notification n
        LEFT JOIN users u ON u.user_id = n.sender_id
        WHERE n.recipient_id = ?
        ORDER BY n.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
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
?>
