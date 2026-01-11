<?php
// instructor_create_assignment.php

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


if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["course_id"], $data["title"], $data["due_date"])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

$courseId = $data["course_id"];
$title = $data["title"];
$description = isset($data["description"]) ? $data["description"] : null;
$dueDate = $data["due_date"];
$createdBy = $_SESSION["user_id"];

$checkStmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ? AND created_by = ?");
$checkStmt->execute([$courseId, $createdBy]);
if ($checkStmt->rowCount() === 0) {
    echo json_encode(["success" => false, "error" => "You do not have permission to assign work to this course"]);
    exit;
}

$insertStmt = $pdo->prepare("INSERT INTO assignment (course_id, created_by, title, description, attachment_url, due_date, created_at)
    VALUES (?, ?, ?, ?, NULL, ?, NOW())");


try {
    $insertStmt->execute([$courseId, $createdBy, $title, $description, $dueDate]);
    $assignmentId = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "assignment" => [
            "assignment_id" => $assignmentId,
            "course_id" => $courseId,
            "title" => $title,
            "description" => $description,
            "attachment_url" => null,
            "due_date" => $dueDate
        ]

    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

