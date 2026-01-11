<?php
header("Content-Type: application/json");

// ✅ include your PDO connection file
include(__DIR__ . "/../config.php"); // adjust path if db.php is elsewhere

// ✅ Safety check
if (!isset($pdo)) {
    echo json_encode([
        "status" => "error",
        "message" => "PDO connection not found. Check db.php include path."
    ]);
    exit;
}

try {
    // ✅ Courses per Semester (Bar chart)
    $stmt1 = $pdo->query("SELECT semester_id, COUNT(*) as total FROM course GROUP BY semester_id ORDER BY semester_id ASC");
    $rows1 = $stmt1->fetchAll();

    $semesterLabels = [];
    $semesterData = [];

    foreach ($rows1 as $row) {
        $semesterLabels[] = "Semester " . $row["semester_id"];
        $semesterData[] = (int)$row["total"];
    }

    // ✅ Courses per Month (Line chart)
    $stmt2 = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total
        FROM course
        GROUP BY month
        ORDER BY month ASC
    ");
    $rows2 = $stmt2->fetchAll();

    $monthLabels = [];
    $monthData = [];

    foreach ($rows2 as $row) {
        $monthLabels[] = $row["month"];
        $monthData[] = (int)$row["total"];
    }

    echo json_encode([
        "status" => "success",
        "courses_per_semester" => [
            "labels" => $semesterLabels,
            "data" => $semesterData
        ],
        "courses_per_month" => [
            "labels" => $monthLabels,
            "data" => $monthData
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
