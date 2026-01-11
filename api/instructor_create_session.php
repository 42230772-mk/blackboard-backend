<?php
// instructor_create_session.php

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
$start_time = $input["start_time"] ?? null;
$end_time = $input["end_time"] ?? null;
$platform = $input["platform"] ?? null;
$join_url = $input["join_url"] ?? null;
$room = $input["room"] ?? null;

if (!$course_id || !$start_time || !$end_time) {
    echo json_encode(["success" => false, "error" => "Missing required fields: course_id, start_time, end_time"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Ensure instructor owns the course
    $check = $pdo->prepare("SELECT course_id FROM course WHERE course_id = ? AND created_by = ?");
    $check->execute([$course_id, $instructor_id]);
    $owned = $check->fetch(PDO::FETCH_ASSOC);

    if (!$owned) {
        echo json_encode(["success" => false, "error" => "You are not allowed to create sessions for this course"]);
        exit;
    }

    // ✅ Insert session
    $stmt = $pdo->prepare("
        INSERT INTO session (course_id, host_id, title, start_time, end_time, platform, join_url, room)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $course_id,
        $instructor_id,
        $title,
        $start_time,
        $end_time,
        $platform,
        $join_url,
        $room
    ]);

    $new_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "session_id" => $new_id
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
