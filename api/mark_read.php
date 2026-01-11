<?php
// api/mark_read.php
require_once __DIR__ . '/../config.php';

// expects JSON { "conversation_id": 1, "user_id": 2, "message_ids": [10,11] }
$input = json_decode(file_get_contents('php://input'), true);
$conversationId = (int)($input['conversation_id'] ?? 0);
$userId = (int)($input['user_id'] ?? 0);
$messageIds = $input['message_ids'] ?? [];

if (!$conversationId || !$userId || !is_array($messageIds) || count($messageIds) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'conversation_id, user_id and message_ids required']);
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
    $sql = "UPDATE `message` SET `is_read` = 1 WHERE conversation_id = ? AND message_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$conversationId], $messageIds);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
