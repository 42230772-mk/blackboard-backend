<?php
// student_apply_exam_petition.php

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

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../../test_db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["exam_id"]) || !isset($data["reason"])) {
    echo json_encode(["success" => false, "message" => "Missing exam_id or reason"]);
    exit;
}

$exam_id = intval($data["exam_id"]);
$student_id = intval($_SESSION["user_id"]);
$reason = trim($data["reason"]);

if ($exam_id <= 0 || $reason === "") {
    echo json_encode(["success" => false, "message" => "Invalid exam_id or empty reason"]);
    exit;
}

try {
    // ✅ Check duplicate petition
    $check = $pdo->prepare("SELECT petition_id FROM exam_petition WHERE exam_id = ? AND student_id = ?");
    $check->execute([$exam_id, $student_id]);

    if ($check->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "You already submitted a petition for this exam"]);
        exit;
    }

    // ✅ Insert petition
    $stmt = $pdo->prepare("INSERT INTO exam_petition (exam_id, student_id, reason, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->execute([$exam_id, $student_id, $reason]);

    $petition_id = $pdo->lastInsertId();

    // ✅ Find admin (first admin in DB)
    $adminStmt = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' ORDER BY user_id ASC LIMIT 1");
    $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(["success" => false, "message" => "No admin found to receive petition"]);
        exit;
    }

    $admin_id = intval($admin["user_id"]);

        // ✅ Get exam title for clearer admin notification
    $examTitle = "Unknown Exam";
    $examStmt = $pdo->prepare("SELECT title FROM exam WHERE exam_id = ?");
    $examStmt->execute([$exam_id]);
    $examRow = $examStmt->fetch(PDO::FETCH_ASSOC);

    if ($examRow && isset($examRow["title"])) {
        $examTitle = $examRow["title"];
    }


    // ✅ Send notification to admin
    $notifTitle = "📌 Exam Petition";
    $notifMessage = "Petition ID: $petition_id\nExam: $examTitle (ID: $exam_id)\nStudent ID: $student_id\nReason: $reason";

    $notifStmt = $pdo->prepare("INSERT INTO notification (recipient_id, sender_id, title, message, created_at, is_read) VALUES (?, ?, ?, ?, NOW(), 0)");
    $notifStmt->execute([$admin_id, $student_id, $notifTitle, $notifMessage]);

    echo json_encode([
        "success" => true,
        "message" => "Petition submitted successfully",
        "petition_id" => $petition_id
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error", "error" => $e->getMessage()]);
    exit;
}
