<?php
// instructor_delete_session.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Only POST method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "instructor") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);
$session_id = $input["session_id"] ?? null;

if (!$session_id) {
    echo json_encode(["success" => false, "error" => "Missing required field: session_id"]);
    exit;
}

require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Verify session belongs to course created by this instructor
    $check = $pdo->prepare("
        SELECT s.session_id
        FROM session s
        INNER JOIN course c ON s.course_id = c.course_id
        WHERE s.session_id = ? AND c.created_by = ?
    ");
    $check->execute([$session_id, $instructor_id]);
    $owned = $check->fetch(PDO::FETCH_ASSOC);

    if (!$owned) {
        echo json_encode(["success" => false, "error" => "You are not allowed to delete this session"]);
        exit;
    }

    // ✅ Delete session
    $stmt = $pdo->prepare("DELETE FROM session WHERE session_id = ?");
    $stmt->execute([$session_id]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
