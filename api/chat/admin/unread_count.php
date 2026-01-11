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
    // ✅ Confirm role is admin
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

    // ✅ Count unread messages across conversations
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
    $stmt->execute([$admin_id, $admin_id]);
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
