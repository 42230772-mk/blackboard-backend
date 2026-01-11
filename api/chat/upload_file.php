<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

// ✅ Must be POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// ✅ Ensure file exists
if (!isset($_FILES["file"])) {
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded (field name must be 'file')"
    ]);
    exit;
}

$file = $_FILES["file"];

// ✅ Handle upload errors
if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "success" => false,
        "message" => "Upload error code: " . $file["error"]
    ]);
    exit;
}

// ✅ Validation: max size 10MB
$maxSize = 10 * 1024 * 1024;
if ($file["size"] > $maxSize) {
    echo json_encode([
        "success" => false,
        "message" => "File too large. Max 10MB allowed."
    ]);
    exit;
}

// ✅ Allowed extensions (images + docs + pdf)
$allowedExtensions = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx", "txt", "zip"];
$originalName = $file["name"];
$tmpPath = $file["tmp_name"];

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid file type. Allowed: " . implode(", ", $allowedExtensions)
    ]);
    exit;
}

// ✅ Generate safe new name
$newName = uniqid("chat_", true) . "." . $ext;

// ✅ Save location
$uploadDir = __DIR__ . "/../../uploads/chat/";
$destination = $uploadDir . $newName;

// ✅ Ensure folder exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Move uploaded file
if (!move_uploaded_file($tmpPath, $destination)) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to move uploaded file"
    ]);
    exit;
}

// ✅ Public URL
$fileUrl = "/blackboard-backend/uploads/chat/" . $newName;

// ✅ Return success
echo json_encode([
    "success" => true,
    "file_url" => $fileUrl,
    "file_name" => $originalName,
    "file_extension" => $ext,
    "file_size" => $file["size"]
]);
