<?php
// instructor_get_courses.php

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

require_once(__DIR__ . "/../test_db.php");

try {
    $stmt = $pdo->prepare("
        SELECT 
            course_id,
            code,
            title,
            description,
            semester_id,
            created_at
        FROM course
        WHERE created_by = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$instructorId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "courses" => $courses
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
