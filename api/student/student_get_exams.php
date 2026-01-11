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
    $sql = "
        SELECT
            ex.exam_id,
            ex.course_id,
            ex.title,
            ex.type,
            ex.start_time,
            ex.end_time,
            ex.total_marks,
            ex.created_at,

            c.code AS course_code,
            c.title AS course_title
        FROM exam ex
        INNER JOIN course c ON c.course_id = ex.course_id
        INNER JOIN enrollment e ON e.course_id = ex.course_id
        WHERE e.student_id = ?
        ORDER BY ex.start_time ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "exams" => $exams
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
?>
