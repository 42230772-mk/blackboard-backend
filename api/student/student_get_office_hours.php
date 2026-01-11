<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

include_once("../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["student_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);

if ($student_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid student_id"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.slot_id,
            s.start_time,
            s.end_time,
            s.location,
            s.capacity,
            u.first_name AS instructor_first_name,
            u.last_name AS instructor_last_name,
            c.code AS course_code,
            c.title AS course_title,

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
        JOIN users u ON s.instructor_id = u.user_id
        JOIN enrollment e ON e.course_id = c.course_id
        WHERE e.student_id = ?
        ORDER BY s.start_time ASC
    ");

    $stmt->execute([$student_id]);
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
 catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
    exit;
}
