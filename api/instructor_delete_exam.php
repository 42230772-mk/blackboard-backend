<?php
// instructor_delete_exam.php

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
    echo json_encode(["success" => false, "error" => "Only POST method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);
$exam_id = $input["exam_id"] ?? null;

if (!$exam_id) {
    echo json_encode(["success" => false, "error" => "Missing required field: exam_id"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Ensure exam belongs to a course created by this instructor
    $check = $pdo->prepare("
        SELECT e.exam_id
        FROM exam e
        INNER JOIN course c ON e.course_id = c.course_id
        WHERE e.exam_id = ? AND c.created_by = ?
    ");
    $check->execute([$exam_id, $instructor_id]);
    $owned = $check->fetch(PDO::FETCH_ASSOC);

    if (!$owned) {
        echo json_encode(["success" => false, "error" => "You are not allowed to delete this exam"]);
        exit;
    }

    // ✅ Delete exam
    $stmt = $pdo->prepare("DELETE FROM exam WHERE exam_id = ?");
    $stmt->execute([$exam_id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
