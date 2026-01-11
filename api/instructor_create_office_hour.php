<?php
// instructor_create_office_hour.php

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

$course_id = $input["course_id"] ?? null; // optional
$start_time = $input["start_time"] ?? null;
$end_time = $input["end_time"] ?? null;
$location = $input["location"] ?? null;
$capacity = $input["capacity"] ?? 1;

if (!$start_time || !$end_time || !$location) {
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields: start_time, end_time, location"
    ]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ If course_id provided, ensure instructor owns the course
    if ($course_id !== null) {
        $check = $pdo->prepare("SELECT course_id FROM course WHERE course_id = ? AND created_by = ?");
        $check->execute([$course_id, $instructor_id]);
        $owned = $check->fetch(PDO::FETCH_ASSOC);

        if (!$owned) {
            echo json_encode(["success" => false, "error" => "You are not allowed to create office hours for this course"]);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO office_hour_slot (instructor_id, course_id, start_time, end_time, location, capacity)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $instructor_id,
        $course_id,
        $start_time,
        $end_time,
        $location,
        $capacity
    ]);

    $new_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "slot_id" => $new_id
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
