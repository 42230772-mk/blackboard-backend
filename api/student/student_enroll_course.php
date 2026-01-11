<?php
header("Content-Type: application/json");
include(__DIR__ . "/../../config.php");


if (!isset($pdo)) {
    echo json_encode(["success" => false, "message" => "PDO connection failed"]);
    exit;
}

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data["student_id"] ?? null;
$course_id  = $data["course_id"] ?? null;

if (!$student_id || !$course_id) {
    echo json_encode(["success" => false, "message" => "Missing student_id or course_id"]);
    exit;
}

try {
    // ✅ Prevent duplicate enrollment
    $check = $pdo->prepare("SELECT COUNT(*) FROM enrollment WHERE student_id = ? AND course_id = ?");
    $check->execute([$student_id, $course_id]);

    if ($check->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "Already enrolled in this course"]);
        exit;
    }

    // ✅ Insert enrollment
    $stmt = $pdo->prepare("
        INSERT INTO enrollment (course_id, student_id, role_in_course, status)
        VALUES (?, ?, 'student', 'active')
    ");
    $stmt->execute([$course_id, $student_id]);

    echo json_encode(["success" => true, "message" => "Enrolled successfully"]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
