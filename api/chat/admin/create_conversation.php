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
include_once("../can_chat.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["admin_id"]) || !isset($data["target_user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id or target_user_id"
    ]);
    exit;
}

$admin_id = intval($data["admin_id"]);
$target_user_id = intval($data["target_user_id"]);

if ($admin_id <= 0 || $target_user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid admin_id or target_user_id"
    ]);
    exit;
}

try {
    // ✅ 1) Confirm admin exists and is role = admin
    $adminCheck = $pdo->prepare("SELECT role, department_id FROM users WHERE user_id = ? LIMIT 1");
    $adminCheck->execute([$admin_id]);
    $adminRow = $adminCheck->fetch(PDO::FETCH_ASSOC);

    if (!$adminRow || $adminRow["role"] !== "admin") {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: user is not an admin"
        ]);
        exit;
    }

    // ✅ 2) Confirm target exists
    $targetCheck = $pdo->prepare("SELECT role, department_id FROM users WHERE user_id = ? LIMIT 1");
    $targetCheck->execute([$target_user_id]);
    $targetRow = $targetCheck->fetch(PDO::FETCH_ASSOC);

    if (!$targetRow) {
        echo json_encode([
            "success" => false,
            "message" => "Target user not found"
        ]);
        exit;
    }

    // ❌ Block admin-admin always
    if ($targetRow["role"] === "admin") {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: admin cannot chat with another admin"
        ]);
        exit;
    }

    // ✅ 3) Department rule: admin can chat only with users in same department
    if (intval($adminRow["department_id"]) !== intval($targetRow["department_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied: admin and target user are not in the same department"
        ]);
        exit;
    }

    // ✅ 4) Check if conversation already exists between these two users
    $checkSql = "
        SELECT cm1.conversation_id
        FROM conversation_member cm1
        JOIN conversation_member cm2 
            ON cm1.conversation_id = cm2.conversation_id
        JOIN conversation c 
            ON c.conversation_id = cm1.conversation_id
        WHERE cm1.user_id = ?
          AND cm2.user_id = ?
          AND c.type = 'one_to_one'
        LIMIT 1
    ";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$admin_id, $target_user_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            "success" => true,
            "message" => "Conversation already exists",
            "conversation_id" => intval($existing["conversation_id"])
        ]);
        exit;
    }

    // ✅ 5) Create conversation
    $title = "Chat Admin-" . $admin_id . " User-" . $target_user_id;

    $createConv = $pdo->prepare("
        INSERT INTO conversation (type, title)
        VALUES ('one_to_one', ?)
    ");
    $createConv->execute([$title]);

    $conversation_id = intval($pdo->lastInsertId());

    // ✅ 6) Add both members
    $addMember = $pdo->prepare("
        INSERT INTO conversation_member (conversation_id, user_id)
        VALUES (?, ?)
    ");

    $addMember->execute([$conversation_id, $admin_id]);
    $addMember->execute([$conversation_id, $target_user_id]);

    echo json_encode([
        "success" => true,
        "message" => "Conversation created",
        "conversation_id" => $conversation_id
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
