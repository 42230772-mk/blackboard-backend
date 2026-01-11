<?php
// admin_create_notification.php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Handle preflight OPTIONS request immediately
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

session_start();


if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Only POST method is allowed"]);
    exit;
}

if (!isset($_SESSION["user_id"]) || strtolower($_SESSION["role"]) !== "admin") {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized",
        "debug_file" => "admin_create_notification.php",
        "debug_role" => $_SESSION["role"] ?? "NO_SESSION"
    ]);
    exit;
}


$admin_id = $_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);

$recipient_id = isset($input["recipient_id"]) && $input["recipient_id"] !== null
    ? intval($input["recipient_id"])
    : null;

$send_to_department = isset($input["send_to_department"]) && $input["send_to_department"] === true;
$send_to_instructors_department = isset($input["send_to_instructors_department"]) && $input["send_to_instructors_department"] === true;


$title = $input["title"] ?? null;
$message = $input["message"] ?? null;

if (!$title || !$message) {
    echo json_encode(["success" => false, "error" => "Missing required fields: title, message"]);
    exit;
}

if (!$recipient_id && !$send_to_department && !$send_to_instructors_department) {
    echo json_encode([
        "success" => false,
        "error" => "You must provide recipient_id OR send_to_department=true OR send_to_instructors_department=true"
    ]);
    exit;
}


require_once(__DIR__ . "/../test_db.php");

try {
    // ✅ Get admin department_id
    $stmtAdmin = $pdo->prepare("SELECT department_id FROM users WHERE user_id = ? AND role = 'admin'");
    $stmtAdmin->execute([$admin_id]);
    $adminRow = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

    if (!$adminRow) {
        echo json_encode(["success" => false, "error" => "Admin not found"]);
        exit;
    }

    $admin_department_id = intval($adminRow["department_id"]);

    // ✅ Case 1: Send to one student
    if ($recipient_id) {
        $recipient_id = intval($recipient_id);

        // must be student + same department
        $stmtStudent = $pdo->prepare("
            SELECT user_id, department_id, role
            FROM users
            WHERE user_id = ?
        ");
        $stmtStudent->execute([$recipient_id]);
        $studentRow = $stmtStudent->fetch(PDO::FETCH_ASSOC);

        if (!$studentRow) {
            echo json_encode(["success" => false, "error" => "Recipient not found"]);
            exit;
        }

        if ($studentRow["role"] !== "student" && $studentRow["role"] !== "instructor") {
            echo json_encode(["success" => false, "error" => "Admin can only send notifications to students or instructors"]);
            exit;
        }


        if (intval($studentRow["department_id"]) !== $admin_department_id) {
            echo json_encode(["success" => false, "error" => "You can only notify students in your department"]);
            exit;
        }

        // insert notification
        $stmt = $pdo->prepare("
            INSERT INTO notification (recipient_id, sender_id, title, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$recipient_id, $admin_id, $title, $message]);

        echo json_encode([
            "success" => true,
            "notification_id" => $pdo->lastInsertId()
        ]);
        exit;
    }

    // ✅ Case 2.1: Send to all instructors in department
    if ($send_to_instructors_department) {

        $stmtInstructors = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE role = 'instructor'
          AND department_id = ?
    ");
        $stmtInstructors->execute([$admin_department_id]);
        $instructors = $stmtInstructors->fetchAll(PDO::FETCH_ASSOC);

        if (!$instructors || count($instructors) === 0) {
            echo json_encode(["success" => false, "error" => "No instructors found in your department"]);
            exit;
        }

        $stmtInsert = $pdo->prepare("
        INSERT INTO notification (recipient_id, sender_id, title, message)
        VALUES (?, ?, ?, ?)
    ");

        $sentCount = 0;
        $failedInserts = [];

        foreach ($instructors as $i) {
            try {
                $ok = $stmtInsert->execute([$i["user_id"], $admin_id, $title, $message]);

                if ($ok) {
                    $sentCount++;
                } else {
                    $errorInfo = $stmtInsert->errorInfo();
                    $failedInserts[] = [
                        "user_id" => $i["user_id"],
                        "error" => $errorInfo
                    ];
                }
            } catch (PDOException $ex) {
                $failedInserts[] = [
                    "user_id" => $i["user_id"],
                    "error" => $ex->getMessage()
                ];
            }
        }

        echo json_encode([
            "success" => true,
            "sent_to_instructors" => $sentCount,
            "failed_count" => count($failedInserts),
            "failed_details" => $failedInserts
        ]);
        exit;
    }


    // ✅ Case 2.2: Send to all students in department
    if ($send_to_department) {
        $stmtStudents = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE role = 'student'
              AND department_id = ?
        ");
        $stmtStudents->execute([$admin_department_id]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        if (!$students || count($students) === 0) {
            echo json_encode(["success" => false, "error" => "No students found in your department"]);
            exit;
        }

        $stmtInsert = $pdo->prepare("
            INSERT INTO notification (recipient_id, sender_id, title, message)
            VALUES (?, ?, ?, ?)
        ");

        $sentCount = 0;
        $failedInserts = [];

        foreach ($students as $s) {
            try {
                $ok = $stmtInsert->execute([$s["user_id"], $admin_id, $title, $message]);

                if ($ok) {
                    $sentCount++;
                } else {
                    $errorInfo = $stmtInsert->errorInfo();
                    $failedInserts[] = [
                        "user_id" => $s["user_id"],
                        "error" => $errorInfo
                    ];
                }
            } catch (PDOException $ex) {
                $failedInserts[] = [
                    "user_id" => $s["user_id"],
                    "error" => $ex->getMessage()
                ];
            }
        }



        echo json_encode([
            "success" => true,
            "sent_to" => $sentCount,
            "failed_count" => count($failedInserts),
            "failed_details" => $failedInserts
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
