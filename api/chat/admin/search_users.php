<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ handle preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}


include_once("../../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["admin_id"]) || !isset($data["search_term"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing admin_id or search_term"
    ]);
    exit;
}

$admin_id = intval($data["admin_id"]);
$search_term = trim($data["search_term"]);

if ($search_term === "") {
    echo json_encode([
        "success" => true,
        "users" => []
    ]);
    exit;
}

try {
    // ✅ Get admin department
    $deptSql = "SELECT department_id FROM users WHERE user_id = ? AND role = 'admin' LIMIT 1";
    $deptStmt = $pdo->prepare($deptSql);
    $deptStmt->execute([$admin_id]);
    $adminRow = $deptStmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminRow) {
        echo json_encode([
            "success" => false,
            "message" => "Admin not found"
        ]);
        exit;
    }

    $department_id = intval($adminRow["department_id"]);

    // ✅ Search students + instructors in same department
    $sql = "
        SELECT user_id, first_name, last_name, email, role
        FROM users
        WHERE department_id = ?
          AND role IN ('student', 'instructor')
          AND (
            first_name LIKE ? OR
            last_name LIKE ? OR
            email LIKE ?
          )
        ORDER BY first_name ASC
        LIMIT 20
    ";

    $like = "%" . $search_term . "%";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$department_id, $like, $like, $like]);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
