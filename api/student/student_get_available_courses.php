<?php
header("Content-Type: application/json");
include(__DIR__ . "/../../config.php");

// ✅ If preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

// ✅ Only accept POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "This endpoint requires POST JSON",
        "method" => $_SERVER["REQUEST_METHOD"]
    ]);
    exit;
}

if (!isset($pdo)) {
    echo json_encode(["success" => false, "message" => "PDO connection failed"]);
    exit;
}

// ✅ Read raw JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON or empty body",
        "raw" => $raw
    ]);
    exit;
}

$student_id = $data["student_id"] ?? null;

if (!$student_id) {
    echo json_encode(["success" => false, "message" => "student_id missing"]);
    exit;
}

try {
    // ✅ Courses NOT enrolled by student
    $stmt = $pdo->prepare("
        SELECT c.course_id, c.code, c.title
        FROM course c
        WHERE c.course_id NOT IN (
            SELECT e.course_id
            FROM enrollment e
            WHERE e.student_id = ?
        )
        ORDER BY c.title ASC
    ");
    $stmt->execute([$student_id]);

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "courses" => $courses
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
