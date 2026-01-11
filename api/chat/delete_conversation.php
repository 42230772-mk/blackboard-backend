<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once("../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['conversation_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing user_id or conversation_id"
    ]);
    exit;
}

$user_id = (int)$data['user_id'];
$conversation_id = (int)$data['conversation_id'];

try {
    // ✅ Check membership
    $check = $pdo->prepare("
        SELECT 1
        FROM conversation_member
        WHERE conversation_id = ? AND user_id = ?
        LIMIT 1
    ");
    $check->execute([$conversation_id, $user_id]);

    if (!$check->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Permission denied"
        ]);
        exit;
    }

    // ✅ delete uploaded files from disk
    $fileStmt = $pdo->prepare("
        SELECT body 
        FROM message 
        WHERE conversation_id = ? AND message_type = 'file'
    ");
    $fileStmt->execute([$conversation_id]);
    $fileMessages = $fileStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fileMessages as $fm) {
        $path = $fm["body"];

        if ($path && strpos($path, "/blackboard-backend/uploads/chat/") === 0) {
            // ✅ Correct absolute disk path
            $fullPath = __DIR__ . "/../../uploads/chat/" . basename($path);

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    // ✅ Delete conversation (CASCADE handles messages + members)
    $delete = $pdo->prepare("
        DELETE FROM conversation
        WHERE conversation_id = ?
    ");
    $delete->execute([$conversation_id]);

    echo json_encode([
        "success" => true,
        "message" => "Conversation deleted permanently"
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
