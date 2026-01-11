<?php
// student_upload_submission.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Only POST allowed"]);
    exit;
}

require_once(__DIR__ . "/../../test_db.php");

// ✅ Validate required fields
if (!isset($_POST["assignment_id"], $_POST["student_id"])) {
    echo json_encode(["success" => false, "message" => "Missing assignment_id or student_id"]);
    exit;
}

$assignment_id = intval($_POST["assignment_id"]);
$student_id = intval($_POST["student_id"]);

if ($assignment_id <= 0 || $student_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid assignment_id or student_id"]);
    exit;
}

// ✅ Validate file
if (!isset($_FILES["submission"]) || $_FILES["submission"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "No submission file uploaded"]);
    exit;
}

// ✅ Upload folder
$uploadDir = __DIR__ . "/../../uploads/submissions/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$originalName = basename($_FILES["submission"]["name"]);
$ext = pathinfo($originalName, PATHINFO_EXTENSION);

// ✅ Safe unique filename
$filename = "submission_" . $assignment_id . "_" . $student_id . "_" . time() . "_" . preg_replace("/[^A-Za-z0-9_\-\.]/", "_", $originalName);
$targetPath = $uploadDir . $filename;

// ✅ Move file
if (!move_uploaded_file($_FILES["submission"]["tmp_name"], $targetPath)) {
    echo json_encode(["success" => false, "message" => "Failed to save file"]);
    exit;
}

// ✅ URL stored in DB
$file_url = "uploads/submissions/" . $filename;

try {
    // ✅ Check if submission already exists for student + assignment
    $checkStmt = $pdo->prepare("SELECT submission_id FROM submission WHERE assignment_id = ? AND submitted_by = ?");
    $checkStmt->execute([$assignment_id, $student_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // ✅ Update existing submission
        $updateStmt = $pdo->prepare("UPDATE submission SET file_url = ?, submitted_at = NOW() WHERE submission_id = ?");
        $updateStmt->execute([$file_url, $existing["submission_id"]]);
        $submission_id = $existing["submission_id"];
    } else {
        // ✅ Insert new submission
        $insertStmt = $pdo->prepare("INSERT INTO submission (assignment_id, submitted_by, file_url, submitted_at) VALUES (?, ?, ?, NOW())");
        $insertStmt->execute([$assignment_id, $student_id, $file_url]);
        $submission_id = $pdo->lastInsertId();
    }

    echo json_encode([
        "success" => true,
        "message" => "Submission uploaded successfully",
        "submission_id" => $submission_id,
        "file_url" => $file_url
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
    exit;
}
?>
