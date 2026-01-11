<?php
// instructor_get_course_students.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// ✅ Validate course_id
if (!isset($_GET["course_id"]) || !is_numeric($_GET["course_id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing or invalid course_id"
    ]);
    exit;
}

$courseId = (int)$_GET["course_id"];

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Step 1: Check that this course belongs to this instructor
    $stmtCheck = $pdo->prepare("
        SELECT course_id
        FROM course
        WHERE course_id = ? AND created_by = ?
        LIMIT 1
    ");
    $stmtCheck->execute([$courseId, $instructorId]);

    $course = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "You are not allowed to view students for this course"
        ]);
        exit;
    }

    // ✅ Step 2: Get enrolled students
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id AS student_id,
            CONCAT(u.first_name, ' ', u.last_name) AS full_name
        FROM enrollment e
        INNER JOIN users u ON e.student_id = u.user_id
        WHERE e.course_id = ? AND u.role = 'student'
        ORDER BY u.first_name ASC
    ");


    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "students" => $students
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
