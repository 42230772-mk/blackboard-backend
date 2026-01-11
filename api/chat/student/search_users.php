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
require_once("../can_chat.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["student_id"]) || !isset($data["search"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing student_id or search"
    ]);
    exit;
}

$student_id = intval($data["student_id"]);
$search = trim($data["search"]);

if ($student_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid student_id"
    ]);
    exit;
}

try {
    // ✅ Find users matching search (students shouldn't search students)
    $sql = "
        SELECT user_id, first_name, last_name, email, role, department_id
        FROM users
        WHERE user_id != ?
          AND (role = 'instructor' OR role = 'admin')
          AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
        LIMIT 30
    ";

    $stmt = $pdo->prepare($sql);
    $like = "%" . $search . "%";
    $stmt->execute([$student_id, $like, $like, $like]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allowed = [];

    foreach ($candidates as $u) {
        $check = canChat($pdo, $student_id, intval($u["user_id"]));
        if ($check["allowed"]) {
            $allowed[] = [
                "user_id" => intval($u["user_id"]),
                "first_name" => $u["first_name"],
                "last_name" => $u["last_name"],
                "email" => $u["email"],
                "role" => $u["role"]
            ];
        }
    }

    echo json_encode([
        "success" => true,
        "users" => $allowed
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
