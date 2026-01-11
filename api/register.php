<?php
// ---- CORS (MUST BE FIRST, NO SPACE BEFORE <?php) ----
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once __DIR__ . "/../test_db.php";

$input = json_decode(file_get_contents("php://input"), true);

// ---- VALIDATE REQUIRED FIELDS ----
$required = ["first_name", "last_name", "email", "password", "department_id", "major_id"];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(["success" => false, "error" => "Missing field: $field"]);
        exit;
    }
}

$first_name    = trim($input["first_name"]);
$last_name     = trim($input["last_name"]);
$email         = trim($input["email"]);
$password      = $input["password"];
$department_id = (int) $input["department_id"];
$major_id      = (int) $input["major_id"];

/* 🔒 FORCE ROLE */
$role = "student";

// ---- CHECK IF EMAIL ALREADY EXISTS ----
$check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo json_encode(["success" => false, "error" => "Email already exists"]);
    exit;
}

// ---- HASH PASSWORD ----
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// ---- INSERT USER ----
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password_hash, role, department_id, major_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $first_name,
    $last_name,
    $email,
    $passwordHash,
    $role,
    $department_id,
    $major_id
]);

$userId = $pdo->lastInsertId();

// ---- RETURN RESPONSE ----
echo json_encode([
    "success" => true,
    "user" => [
        "user_id"    => (int)$userId,
        "first_name" => $first_name,
        "last_name"  => $last_name,
        "email"      => $email,
        "role"       => $role,
        "department_id" => $department_id,
        "major_id"      => $major_id
    ]
]);
