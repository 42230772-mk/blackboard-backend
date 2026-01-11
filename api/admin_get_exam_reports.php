<?php
header("Content-Type: application/json");
include(__DIR__ . "/../config.php"); // adjust if needed

if (!isset($pdo)) {
    echo json_encode(["status" => "error", "message" => "PDO connection not found"]);
    exit;
}

try {
    // ✅ Petition status counts
    $stmt1 = $pdo->query("
        SELECT status, COUNT(*) as total
        FROM exam_petition
        GROUP BY status
    ");
    $rows1 = $stmt1->fetchAll();

    $petitionLabels = [];
    $petitionData = [];

    foreach ($rows1 as $row) {
        $petitionLabels[] = ucfirst($row["status"]);
        $petitionData[] = (int)$row["total"];
    }

    // ✅ Exams scheduled per month
    $stmt2 = $pdo->query("
        SELECT DATE_FORMAT(start_time, '%Y-%m') as month, COUNT(*) as total
        FROM exam_schedule
        WHERE start_time IS NOT NULL
        GROUP BY month
        ORDER BY month ASC
    ");
    $rows2 = $stmt2->fetchAll();

    $examMonthLabels = [];
    $examMonthData = [];

    foreach ($rows2 as $row) {
        $examMonthLabels[] = $row["month"];
        $examMonthData[] = (int)$row["total"];
    }

    // ✅ Notifications Read vs Unread
    $stmt3 = $pdo->query("
        SELECT is_read, COUNT(*) as total
        FROM notification
        GROUP BY is_read
    ");
    $rows3 = $stmt3->fetchAll();

    $notifLabels = [];
    $notifData = [];

    foreach ($rows3 as $row) {
        $notifLabels[] = ((int)$row["is_read"] === 1) ? "Read" : "Unread";
        $notifData[] = (int)$row["total"];
    }

    // ✅ Notifications per month
    $stmt4 = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total
        FROM notification
        WHERE created_at IS NOT NULL
        GROUP BY month
        ORDER BY month ASC
    ");
    $rows4 = $stmt4->fetchAll();

    $notifMonthLabels = [];
    $notifMonthData = [];

    foreach ($rows4 as $row) {
        $notifMonthLabels[] = $row["month"];
        $notifMonthData[] = (int)$row["total"];
    }

    echo json_encode([
        "status" => "success",
        "petition_status" => [
            "labels" => $petitionLabels,
            "data" => $petitionData
        ],
        "exams_per_month" => [
            "labels" => $examMonthLabels,
            "data" => $examMonthData
        ],
        "notifications_read_status" => [
            "labels" => $notifLabels,
            "data" => $notifData
        ],
        "notifications_per_month" => [
            "labels" => $notifMonthLabels,
            "data" => $notifMonthData
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Query failed",
        "details" => $e->getMessage()
    ]);
}
?>
