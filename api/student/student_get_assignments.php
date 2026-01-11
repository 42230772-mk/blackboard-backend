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
        a.assignment_id,
        a.course_id,
        a.title,
        a.description,
        a.attachment_url,
        a.due_date,
        a.created_at,

        c.code AS course_code,
        c.title AS course_title,

        CASE 
            WHEN s.submission_id IS NOT NULL THEN 1
            ELSE 0
        END AS is_submitted

    FROM assignment a
    INNER JOIN course c ON c.course_id = a.course_id
    INNER JOIN enrollment e ON e.course_id = a.course_id

    LEFT JOIN submission s 
        ON s.assignment_id = a.assignment_id
        AND s.submitted_by = e.student_id

    WHERE e.student_id = ?
    ORDER BY a.due_date ASC
";


    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "assignments" => $assignments
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
