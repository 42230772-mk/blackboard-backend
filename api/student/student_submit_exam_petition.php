<?php
// student_submit_exam_petition.php

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
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
    exit;
}

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "student") {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$studentId = (int)$_SESSION["user_id"];

require_once(__DIR__ . "/../../test_db.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["exam_id"]) || !is_numeric($input["exam_id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing or invalid exam_id"
    ]);
    exit;
}

$examId = (int)$input["exam_id"];
$reason = trim($input["reason"] ?? "");

if ($reason === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Reason is required"
    ]);
    exit;
}

try {
    // ✅ Step 1: Get exam course_id
    $stmtExam = $pdo->prepare("
        SELECT course_id
        FROM exam
        WHERE exam_id = ?
        LIMIT 1
    ");
    $stmtExam->execute([$examId]);
    $exam = $stmtExam->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Exam not found"
        ]);
        exit;
    }

    $courseId = (int)$exam["course_id"];

    // ✅ Step 2: Ensure student is enrolled in that course
    $stmtEnroll = $pdo->prepare("
        SELECT enrollment_id
        FROM enrollment
        WHERE course_id = ? AND student_id = ?
        LIMIT 1
    ");
    $stmtEnroll->execute([$courseId, $studentId]);
    $enrolled = $stmtEnroll->fetch(PDO::FETCH_ASSOC);

    if (!$enrolled) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "You are not enrolled in this course"
        ]);
        exit;
    }

    // ✅ Step 3: Prevent duplicate petition for same exam by same student
    $stmtDup = $pdo->prepare("
        SELECT petition_id
        FROM exam_petition
        WHERE exam_id = ? AND student_id = ?
        LIMIT 1
    ");
    $stmtDup->execute([$examId, $studentId]);

    if ($stmtDup->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "You already submitted a petition for this exam"
        ]);
        exit;
    }

    // ✅ Step 4: Insert petition
    $stmtInsert = $pdo->prepare("
        INSERT INTO exam_petition (exam_id, student_id, reason, status)
        VALUES (?, ?, ?, 'pending')
    ");

    $stmtInsert->execute([$examId, $studentId, $reason]);

    // ✅ Get inserted petition_id
    $petitionId = $pdo->lastInsertId();

        // ✅ Step 5: Get student's department_id
    $stmtDept = $pdo->prepare("
        SELECT department_id
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");
    $stmtDept->execute([$studentId]);
    $student = $stmtDept->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Student not found"
        ]);
        exit;
    }

    $departmentId = (int)$student["department_id"];


    // ✅ Step 6: Find admin(s) in same department
    $stmtAdmin = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE role = 'admin' AND department_id = ?
        LIMIT 1
    ");
    $stmtAdmin->execute([$departmentId]);
    $adminId = $stmtAdmin->fetchColumn();


    if ($adminId) {
        // ✅ Create notification message for admin
        $title = "📌 Exam Petition";
        $message =
            "Petition ID: $petitionId\n" .
            "Exam ID: $examId\n" .
            "Student ID: $studentId\n" .
            "Reason: $reason";

        // ✅ Insert notification (IMPORTANT: sender_id = studentId)
        $stmtNotif = $pdo->prepare("
            INSERT INTO notification (recipient_id, sender_id, title, message, created_at, is_read)
            VALUES (?, ?, ?, ?, NOW(), 0)
        ");
        $stmtNotif->execute([$adminId, $studentId, $title, $message]);
    }


    echo json_encode([
        "success" => true,
        "message" => "Petition submitted successfully"
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
