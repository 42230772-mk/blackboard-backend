<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Not authenticated"
    ]);
    exit;
}

// 2️⃣ Check role
if ($_SESSION["role"] !== "instructor") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "Access denied"
    ]);
    exit;
}

$instructorId = (int)$_SESSION["user_id"];

// 3️⃣ Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["course_id"]) || !isset($input["code"]) || !isset($input["title"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields: course_id, code, title"
    ]);
    exit;
}

$courseId = (int)$input["course_id"];
$code = trim($input["code"]);
$title = trim($input["title"]);
$description = isset($input["description"]) ? trim($input["description"]) : "";

try {
    // 4️⃣ Check ownership
    $checkStmt = $pdo->prepare("SELECT course_id FROM course WHERE course_id = ? AND created_by = ?");
    $checkStmt->execute([$courseId, $instructorId]);
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "error" => "Cannot edit course you don't own"
        ]);
        exit;
    }

    // 5️⃣ Update course
    $stmt = $pdo->prepare("
        UPDATE course
        SET code = ?, title = ?, description = ?
        WHERE course_id = ? AND created_by = ?
    ");
    $stmt->execute([$code, $title, $description, $courseId, $instructorId]);

    echo json_encode([
        "success" => true,
        "message" => "Course updated successfully"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Failed to update course"
    ]);
}
