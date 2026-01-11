<?php
header("Content-Type: application/json");

// ✅ CORS (allow React frontend)
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// ✅ Handle preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

include(__DIR__ . "/../../config.php");
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "This endpoint requires POST JSON",
        "method" => $_SERVER["REQUEST_METHOD"]
    ]);
    exit;
}


// ✅ Read JSON body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// ✅ Validate JSON
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON or empty body",
        "raw" => $raw
    ]);
    exit;
}

// ✅ Get student_id
$student_id = $data["student_id"] ?? null;

if (!$student_id) {
    echo json_encode(["success" => false, "message" => "Missing student_id"]);
    exit;
}

try {
    // ✅ Fetch enrolled courses
    $stmt = $pdo->prepare("
        SELECT 
            c.course_id, 
            c.code, 
            c.title, 
            c.created_by,
            u.first_name AS instructor_first_name,
            u.last_name AS instructor_last_name
        FROM enrollment e
        JOIN course c ON e.course_id = c.course_id
        LEFT JOIN users u ON c.created_by = u.user_id
        WHERE e.student_id = ?
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
