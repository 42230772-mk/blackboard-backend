<?php
// instructor_delete_course.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

session_start();

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

if (!$course_id) {
    echo json_encode(["success" => false, "error" => "Missing course_id"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // Check ownership
    $stmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ? AND created_by = ?");
    $stmt->execute([$course_id, $instructor_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(["success" => false, "error" => "Course not found or access denied"]);
        exit;
    }

    // Proceed to delete
    $stmt = $pdo->prepare("DELETE FROM course WHERE course_id = ?");
    $stmt->execute([$course_id]);

    echo json_encode(["success" => true, "message" => "Course deleted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
 