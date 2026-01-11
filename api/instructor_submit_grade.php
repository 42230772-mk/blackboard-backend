<?php
// instructor_submit_grade.php

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

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "instructor") {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$instructorId = (int)$_SESSION["user_id"];

require_once(__DIR__ . "/../test_db.php");

// ✅ Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["course_id"]) || !is_numeric($input["course_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing or invalid course_id"]);
    exit;
}

if (!isset($input["student_id"]) || !is_numeric($input["student_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing or invalid student_id"]);
    exit;
}

if (!isset($input["grade"]) || !is_numeric($input["grade"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing or invalid grade"]);
    exit;
}

$courseId  = (int)$input["course_id"];
$studentId = (int)$input["student_id"];
$grade     = (float)$input["grade"];

try {
    // ✅ 1) Ensure course belongs to instructor
    $stmtCheckCourse = $pdo->prepare("
        SELECT course_id 
        FROM course 
        WHERE course_id = ? AND created_by = ?
        LIMIT 1
    ");
    $stmtCheckCourse->execute([$courseId, $instructorId]);

    if (!$stmtCheckCourse->fetch()) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "You are not allowed to grade this course"
        ]);
        exit;
    }

    // ✅ 2) Ensure student is enrolled in this course
    $stmtCheckEnroll = $pdo->prepare("
        SELECT enrollment_id 
        FROM enrollment
        WHERE course_id = ? AND student_id = ?
        LIMIT 1
    ");
    $stmtCheckEnroll->execute([$courseId, $studentId]);

    $enrollRow = $stmtCheckEnroll->fetch(PDO::FETCH_ASSOC);

    if (!$enrollRow) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Student is not enrolled in this course"
        ]);
        exit;
    }

    // ✅ 3) Update grade
    $stmtUpdate = $pdo->prepare("
        UPDATE enrollment
        SET grade = ?
        WHERE course_id = ? AND student_id = ?
    ");
    $stmtUpdate->execute([$grade, $courseId, $studentId]);

    echo json_encode([
        "success" => true,
        "message" => "Grade submitted successfully"
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
    exit;
}
?>
