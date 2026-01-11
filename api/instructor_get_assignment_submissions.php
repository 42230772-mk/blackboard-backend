<?php
// instructor_get_assignment_submissions.php

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
    echo json_encode(["success" => false, "error" => "Only POST allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["assignment_id"])) {
    echo json_encode(["success" => false, "error" => "Missing assignment_id"]);
    exit;
}

$assignment_id = intval($data["assignment_id"]);
$instructor_id = intval($_SESSION["user_id"]);

if ($assignment_id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid assignment_id"]);
    exit;
}

try {
    // ✅ Security check: ensure this assignment belongs to instructor's course
    $checkStmt = $pdo->prepare("
        SELECT a.assignment_id
        FROM assignment a
        INNER JOIN course c ON a.course_id = c.course_id
        WHERE a.assignment_id = ? AND c.created_by = ?
    ");
    $checkStmt->execute([$assignment_id, $instructor_id]);

    if ($checkStmt->rowCount() === 0) {
        echo json_encode(["success" => false, "error" => "You do not have permission to view submissions for this assignment"]);
        exit;
    }

    // ✅ Fetch submissions
    $stmt = $pdo->prepare("
        SELECT 
            s.submission_id,
            s.assignment_id,
            s.submitted_by AS student_id,
            u.first_name,
            u.last_name,
            s.file_url,
            s.submitted_at
        FROM submission s
        INNER JOIN users u ON s.submitted_by = u.user_id
        WHERE s.assignment_id = ?
        ORDER BY s.submitted_at DESC
    ");

    $stmt->execute([$assignment_id]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "submissions" => $submissions
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
    exit;
}
?>
