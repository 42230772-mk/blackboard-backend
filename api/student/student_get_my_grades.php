<?php
// student_get_my_grades.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "student") {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized"
    ]);
    exit;
}

$studentId = (int)$_SESSION["user_id"];

require_once(__DIR__ . "/../../test_db.php");

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.course_id,
            c.code,
            c.title,
            e.grade
        FROM enrollment e
        INNER JOIN course c ON e.course_id = c.course_id
        WHERE e.student_id = ?
        ORDER BY c.title ASC
    ");

    $stmt->execute([$studentId]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "grades" => $grades
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
    exit;
}
?>
