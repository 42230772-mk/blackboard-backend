<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . "/../test_db.php";


// ✅ 1) Validate assignment_id
if (!isset($_POST['assignment_id']) || empty($_POST['assignment_id'])) {
    echo json_encode(["success" => false, "message" => "assignment_id is required"]);
    exit;
}
$assignment_id = intval($_POST['assignment_id']);

// ✅ 2) Validate file exists
if (!isset($_FILES['attachment'])) {
    echo json_encode(["success" => false, "message" => "No attachment file was uploaded"]);
    exit;
}

$file = $_FILES['attachment'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "Upload error: " . $file['error']]);
    exit;
}

// ✅ 3) Create uploads folder if missing
$uploadDir = __DIR__ . "/../uploads/assignments/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ 4) Generate safe filename
$originalName = basename($file['name']);
$originalName = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", $originalName);

$finalFileName = "assignment_" . $assignment_id . "_" . time() . "_" . $originalName;
$targetPath = $uploadDir . $finalFileName;

// ✅ 5) Move file to uploads folder
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(["success" => false, "message" => "Failed to save file to uploads folder"]);
    exit;
}

// ✅ 6) Save URL into DB
$attachment_url = "uploads/assignments/" . $finalFileName;

$stmt = $pdo->prepare("UPDATE assignment SET attachment_url = :url WHERE assignment_id = :id");

if (!$stmt->execute([
    ":url" => $attachment_url,
    ":id" => $assignment_id
])) {
    echo json_encode(["success" => false, "message" => "Database update failed"]);
    exit;
}


// ✅ 7) Return success response
echo json_encode([
    "success" => true,
    "message" => "Attachment uploaded successfully",
    "attachment_url" => $attachment_url
]);
