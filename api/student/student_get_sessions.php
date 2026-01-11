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
            s.session_id,
            s.course_id,
            s.title,
            s.start_time,
            s.end_time,
            s.platform,
            s.join_url,
            s.room,
            s.created_at,

            c.code AS course_code,
            c.title AS course_title
        FROM session s
        INNER JOIN course c ON c.course_id = s.course_id
        INNER JOIN enrollment e ON e.course_id = s.course_id
        WHERE e.student_id = ?
        ORDER BY s.start_time ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "sessions" => $sessions
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
