<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

include_once("../../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["instructor_id"]) || !isset($data["search"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing instructor_id or search"
    ]);
    exit;
}

$instructor_id = intval($data["instructor_id"]);
$search = trim($data["search"]);

if ($instructor_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid instructor_id"
    ]);
    exit;
}

try {
    $like = "%" . $search . "%";

    // ✅ 1) Get instructor department
    $deptStmt = $pdo->prepare("SELECT department_id FROM users WHERE user_id = ? AND role = 'instructor' LIMIT 1");
    $deptStmt->execute([$instructor_id]);
    $instructorRow = $deptStmt->fetch(PDO::FETCH_ASSOC);

    if (!$instructorRow) {
        echo json_encode([
            "success" => false,
            "message" => "Instructor not found"
        ]);
        exit;
    }

    $department_id = intval($instructorRow["department_id"]);

    // ✅ 2) Get admins in same department matching search
    $adminSql = "
        SELECT user_id, first_name, last_name, email, role
        FROM users
        WHERE role = 'admin'
          AND department_id = ?
          AND user_id != ?
          AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
    ";
    $adminStmt = $pdo->prepare($adminSql);
    $adminStmt->execute([$department_id, $instructor_id, $like, $like, $like]);
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ 3) Get students sharing courses with this instructor matching search
    $studentSql = "
        SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.role
        FROM users u
        INNER JOIN enrollment e ON e.student_id = u.user_id
        INNER JOIN course c ON c.course_id = e.course_id
        WHERE c.created_by = ?
          AND u.role = 'student'
          AND u.user_id != ?
          AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)
    ";
    $studentStmt = $pdo->prepare($studentSql);
    $studentStmt->execute([$instructor_id, $instructor_id, $like, $like, $like]);
    $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ merge results
    $users = array_merge($admins, $students);

    echo json_encode([
        "success" => true,
        "users" => $users
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
