<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once __DIR__ . "/../config.php";

/* ✅ Auth check */
if (!isset($_SESSION["user"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Not authenticated"
    ]);
    exit;
}

/*
  ✅ Session user id compatibility:
  Some of your files use user_id, and login.php stores id.
  We support BOTH safely without changing any old code.
*/
$instructorId = $_SESSION["user"]["user_id"] ?? ($_SESSION["user"]["id"] ?? null);
$role = $_SESSION["user"]["role"] ?? null;

if (!$instructorId || !$role) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Invalid session data"
    ]);
    exit;
}

if ($role !== "instructor") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "Access denied"
    ]);
    exit;
}

/* ✅ Read JSON input */
$input = json_decode(file_get_contents("php://input"), true);

$code = trim($input["code"] ?? "");
$title = trim($input["title"] ?? "");
$description = trim($input["description"] ?? "");
$semesterIdInput = $input["semester_id"] ?? null;

/* ✅ Validate required fields */
if ($code === "" || $title === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields: code, title"
    ]);
    exit;
}

/*
  ✅ Semester rule (NO assumptions):
  - If semester_id is provided => validate it exists.
  - If semester_id is NOT provided => use exactly ONE active semester.
    If no active semester => error.
    If multiple active semesters => error (data issue).
*/
try {
    $semesterId = null;

    if ($semesterIdInput !== null && $semesterIdInput !== "") {
        $semesterId = (int)$semesterIdInput;

        $checkSem = $pdo->prepare("SELECT semester_id FROM semester WHERE semester_id = ? LIMIT 1");
        $checkSem->execute([$semesterId]);

        if (!$checkSem->fetch()) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Invalid semester_id"
            ]);
            exit;
        }
    } else {
        $activeSem = $pdo->query("SELECT semester_id FROM semester WHERE is_active = 1");
        $activeRows = $activeSem->fetchAll(PDO::FETCH_ASSOC);

        if (count($activeRows) === 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "No active semester found. Provide semester_id."
            ]);
            exit;
        }

        if (count($activeRows) > 1) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Multiple active semesters found. Fix data or provide semester_id."
            ]);
            exit;
        }

        $semesterId = (int)$activeRows[0]["semester_id"];
    }

    /* ✅ Prevent duplicate code (global unique behavior) */
    $checkCode = $pdo->prepare("SELECT course_id FROM course WHERE code = ? LIMIT 1");
    $checkCode->execute([$code]);
    if ($checkCode->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "error" => "Course code already exists"
        ]);
        exit;
    }

    /* ✅ Insert course */
    $stmt = $pdo->prepare("
        INSERT INTO course (code, title, description, semester_id, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $code,
        $title,
        $description !== "" ? $description : null,
        $semesterId,
        (int)$instructorId
    ]);

    $courseId = (int)$pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "course" => [
            "course_id" => $courseId,
            "code" => $code,
            "title" => $title,
            "description" => ($description !== "" ? $description : null),
            "semester_id" => $semesterId,
            "created_by" => (int)$instructorId
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error"
    ]);
}
