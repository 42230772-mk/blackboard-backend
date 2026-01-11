<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

session_start();

// Correct path to your DB file
require_once __DIR__ . "/../test_db.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["email"]) || !isset($input["password"])) {
    echo json_encode([
        "success" => false,
        "error" => "Missing email or password"
    ]);
    exit;
}

$email = trim($input["email"]);
$password = $input["password"];

$stmt = $pdo->prepare("
    SELECT user_id AS id, CONCAT(first_name, ' ', last_name) AS name, email, password_hash, role
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid credentials"
    ]);
    exit;
}

if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid credentials"
    ]);
    exit;
}

if (!in_array($user["role"], ["student", "instructor", "admin"])) {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized role"
    ]);
    exit;
}

// ✅ Save session in BOTH formats
$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role']
];
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    "success" => true,
    "user" => [
        "id" => (int)$user["id"],
        "name" => $user["name"],
        "email" => $user["email"],
        "role" => $user["role"]
    ]
]);
