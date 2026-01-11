<?php
// admin_update_exam_petition.php

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

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["petition_id"]) || !isset($data["status"])) {
    echo json_encode(["success" => false, "error" => "Missing petition_id or status"]);
    exit;
}

$petition_id = intval($data["petition_id"]);
$status = trim($data["status"]); // approved / rejected
$admin_comment = isset($data["admin_comment"]) ? trim($data["admin_comment"]) : null;

if ($petition_id <= 0 || !in_array($status, ["approved", "rejected"])) {
    echo json_encode(["success" => false, "error" => "Invalid petition_id or status"]);
    exit;
}

try {
    // ✅ Fetch petition info first
    $stmt = $pdo->prepare("SELECT exam_id, student_id, status FROM exam_petition WHERE petition_id = ?");
    $stmt->execute([$petition_id]);
    $petition = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$petition) {
        echo json_encode(["success" => false, "error" => "Petition not found"]);
        exit;
    }

    // ✅ Prevent changing already processed petitions
    if ($petition["status"] !== "pending") {
        echo json_encode(["success" => false, "error" => "Petition already processed"]);
        exit;
    }

    $student_id = intval($petition["student_id"]);
    $exam_id = intval($petition["exam_id"]);

    // ✅ Update petition status
    $update = $pdo->prepare("UPDATE exam_petition SET status = ?, admin_comment = ? WHERE petition_id = ?");
    $update->execute([$status, $admin_comment, $petition_id]);

    // ✅ Send notification back to student
    $title = "✅ Exam Petition Result";
    $message = "Your petition for Exam ID $exam_id was $status.";

    if ($admin_comment) {
        $message .= "\nAdmin Comment: $admin_comment";
    }

    $notifStmt = $pdo->prepare("
        INSERT INTO notification (recipient_id, sender_id, title, message, created_at, is_read)
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");
    $notifStmt->execute([$student_id, $_SESSION["user_id"], $title, $message]);

    echo json_encode([
        "success" => true,
        "message" => "Petition updated successfully",
        "status" => $status
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
    exit;
}
