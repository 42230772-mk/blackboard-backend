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
    !isset($data["conversation_id"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id or conversation_id"
    ]);
    exit;
}

$admin_id = intval($data["admin_id"]);
$conversation_id = intval($data["conversation_id"]);

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

    // ✅ Permission: admin must be member of this conversation
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

    // ✅ Mark all messages from others as read
    $updateSql = "
        UPDATE message
        SET is_read = 1
        WHERE conversation_id = ?
          AND sender_id != ?
          AND is_read = 0
    ";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$conversation_id, $admin_id]);

    echo json_encode([
        "success" => true,
        "message" => "Messages marked as read",
        "updated_count" => $updateStmt->rowCount()
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
