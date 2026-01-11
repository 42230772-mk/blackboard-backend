<?php
// instructor_create_exam.php

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

$course_id = $input["course_id"] ?? null;
$title = $input["title"] ?? null;
$type = $input["type"] ?? "quiz"; // quiz | midterm | final
$start_time = $input["start_time"] ?? null;
$end_time = $input["end_time"] ?? null;
$total_marks = $input["total_marks"] ?? null;

if (!$course_id || !$title || !$start_time || !$end_time || $total_marks === null) {
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields: course_id, title, start_time, end_time, total_marks"
    ]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Ensure instructor owns this course
    $check = $pdo->prepare("SELECT course_id FROM course WHERE course_id = ? AND created_by = ?");
    $check->execute([$course_id, $instructor_id]);
    $owned = $check->fetch(PDO::FETCH_ASSOC);

    if (!$owned) {
        echo json_encode(["success" => false, "error" => "You are not allowed to create exams for this course"]);
        exit;
    }

    // ✅ Insert exam
    $stmt = $pdo->prepare("
        INSERT INTO exam (course_id, created_by, title, type, start_time, end_time, total_marks)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $course_id,
        $instructor_id,
        $title,
        $type,
        $start_time,
        $end_time,
        $total_marks
    ]);

    $new_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "exam_id" => $new_id
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
