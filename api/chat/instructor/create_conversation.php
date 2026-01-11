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

if (!isset($data["instructor_id"]) || !isset($data["student_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing instructor_id or student_id"
    ]);
    exit;
}

$instructor_id = intval($data["instructor_id"]);
$student_id = intval($data["student_id"]);

if ($instructor_id === $student_id) {
    echo json_encode([
        "success" => false,
        "message" => "Cannot create conversation with self"
    ]);
    exit;
}

try {
   
    // ✅ Permission check using shared helper
    $perm = canChat($pdo, $instructor_id, $student_id);

    if (!$perm["allowed"]) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied",
            "reason" => $perm["reason"]
        ]);
        exit;
    }


    // ✅ 2) Check if conversation already exists between these two users
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
    $checkStmt->execute([$instructor_id, $student_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            "success" => true,
            "message" => "Conversation already exists",
            "conversation_id" => intval($existing["conversation_id"])
        ]);
        exit;
    }

    // ✅ 3) Create conversation + add members in a transaction
    $pdo->beginTransaction();

    $title = "Chat Instructor-Student";

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

    // Instructor member
    $insertMemberStmt->execute([$conversation_id, $instructor_id]);

    // Student member
    $insertMemberStmt->execute([$conversation_id, $student_id]);

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
