<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once("../../../config.php");
require_once("../can_chat.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["student_id"]) || !isset($data["target_user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id or target_user_id"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);
$target_user_id = intval($data["target_user_id"]);

if ($student_id === $target_user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Cannot create conversation with self"
    ]);
    exit;
}

try {
    // ✅ Permission check using shared helper
    $perm = canChat($pdo, $student_id, $target_user_id);

    if (!$perm["allowed"]) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied",
            "reason" => $perm["reason"]
        ]);
        exit;
    }

    // ✅ Check if one_to_one conversation already exists
    $checkSql = "
        SELECT cm1.conversation_id
        FROM conversation_member cm1
        INNER JOIN conversation_member cm2 
            ON cm1.conversation_id = cm2.conversation_id
        INNER JOIN conversation c ON c.conversation_id = cm1.conversation_id
        WHERE cm1.user_id = ?
          AND cm2.user_id = ?
          AND c.type = 'one_to_one'
        LIMIT 1
    ";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$student_id, $target_user_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            "success" => true,
            "message" => "Conversation already exists",
            "conversation_id" => intval($existing["conversation_id"])
        ]);
        exit;
    }

    // ✅ Create conversation + members (transaction)
    $pdo->beginTransaction();

    $title = "Chat Student-User";

    $insertConvSql = "
        INSERT INTO conversation (type, title)
        VALUES ('one_to_one', ?)
    ";
    $insertConvStmt = $pdo->prepare($insertConvSql);
    $insertConvStmt->execute([$title]);

    $conversation_id = intval($pdo->lastInsertId());

    $insertMemberSql = "
        INSERT INTO conversation_member (conversation_id, user_id)
        VALUES (?, ?)
    ";
    $insertMemberStmt = $pdo->prepare($insertMemberSql);

    $insertMemberStmt->execute([$conversation_id, $student_id]);
    $insertMemberStmt->execute([$conversation_id, $target_user_id]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Conversation created",
        "conversation_id" => $conversation_id
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
    exit;
}
?>
