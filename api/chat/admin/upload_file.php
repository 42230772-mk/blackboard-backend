<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

include_once("../../../config.php");

if (!isset($_POST["admin_id"]) || !isset($_POST["conversation_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id or conversation_id"
    ]);
    exit;
}

$admin_id = intval($_POST["admin_id"]);
$conversation_id = intval($_POST["conversation_id"]);

if (!isset($_FILES["file"])) {
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded"
    ]);
    exit;
}

$file = $_FILES["file"];
$uploadDir = "../../../uploads/chat/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$uniqueName = uniqid("chat_", true) . "." . $ext;
$targetPath = $uploadDir . $uniqueName;

if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
    echo json_encode([
        "success" => false,
        "message" => "Upload failed"
    ]);
    exit;
}

$fileUrl = "/blackboard-backend/uploads/chat/" . $uniqueName;

try {
    $sql = "INSERT INTO message (conversation_id, sender_id, body, message_type)
            VALUES (?, ?, ?, 'file')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$conversation_id, $admin_id, $fileUrl]);

    $message_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => [
            "message_id" => $message_id,
            "conversation_id" => $conversation_id,
            "sender_id" => $admin_id,
            "body" => $fileUrl,
            "message_type" => "file",
            "sent_at" => date("Y-m-d H:i:s")
        ]
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
