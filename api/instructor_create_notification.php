<?php
// instructor_create_notification.php

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
$message = $input["message"] ?? null;


if (!$course_id || !$title || !$message) {
    echo json_encode(["success" => false, "error" => "Missing required fields: course_id, title, message"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Ensure instructor owns the course
    $checkCourse = $pdo->prepare("
        SELECT course_id
        FROM course
        WHERE course_id = ?
          AND created_by = ?
    ");
    $checkCourse->execute([$course_id, $instructor_id]);
    $course = $checkCourse->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(["success" => false, "error" => "You are not allowed to send notifications for this course"]);
        exit;
    }

    // ✅ Get enrolled students
    $stmtStudents = $pdo->prepare("
        SELECT student_id
        FROM enrollment
        WHERE course_id = ?
          AND role_in_course = 'student'
          AND status = 'active'
    ");
    $stmtStudents->execute([$course_id]);
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    if (!$students || count($students) === 0) {
        echo json_encode(["success" => false, "error" => "No active students enrolled in this course"]);
        exit;
    }

    // ✅ Insert notification for each student
    $stmtInsert = $pdo->prepare("
        INSERT INTO notification (recipient_id, sender_id, title, message)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($students as $s) {
        $stmtInsert->execute([$s["student_id"], $instructor_id, $title, $message]);
    }

    echo json_encode([
        "success" => true,
        "sent_to" => count($students)
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
