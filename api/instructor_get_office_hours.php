<?php
// instructor_get_office_hours.php

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
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

$instructor_id = intval($_SESSION["user_id"]);

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.slot_id,
            s.course_id,
            c.code AS course_code,
            c.title AS course_title,
            s.start_time,
            s.end_time,
            s.location,
            s.capacity,

            (
              SELECT COUNT(*) 
              FROM office_hour_booking b
              WHERE b.slot_id = s.slot_id AND b.status = 'confirmed'
            ) AS booked_count,

            (s.capacity - (
              SELECT COUNT(*) 
              FROM office_hour_booking b
              WHERE b.slot_id = s.slot_id AND b.status = 'confirmed'
            )) AS remaining

        FROM office_hour_slot s
        JOIN course c ON s.course_id = c.course_id
        WHERE s.instructor_id = ?
        ORDER BY s.start_time ASC
    ");

    $stmt->execute([$instructor_id]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "office_hours" => $slots
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
    exit;
}
