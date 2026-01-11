<?php
// instructor_get_sessions.php

// ✅ CORS headers must be first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

session_start();

file_put_contents("debug_sessions.log", json_encode([
    "user_id" => $_SESSION["user_id"] ?? null,
    "role" => $_SESSION["role"] ?? null,
    "method" => $_SERVER["REQUEST_METHOD"],
    "cookies" => $_COOKIE
], JSON_PRETTY_PRINT));


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
        s.session_id,
        s.course_id,
        c.title AS course_title,
        s.start_time,
        s.end_time,
        s.platform,
        s.join_url,
        s.room
    FROM session s
    INNER JOIN course c ON s.course_id = c.course_id
    WHERE c.created_by = ?
    ORDER BY s.start_time ASC
");

    $stmt->execute([$instructor_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "sessions" => $sessions]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
