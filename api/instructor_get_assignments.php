<?php
// instructor_get_assignments.php

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

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "error" => "Only GET method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION["user_id"];

require_once(__DIR__ . "/../test_db.php");

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.assignment_id,
            a.course_id,
            c.title AS course_title,
            a.title,
            a.description,
            a.attachment_url,
            a.due_date,
            a.created_at
        FROM assignment a
        INNER JOIN course c ON a.course_id = c.course_id
        WHERE c.created_by = ?
        ORDER BY a.due_date ASC
    ");

    $stmt->execute([$instructor_id]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "assignments" => $assignments]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
