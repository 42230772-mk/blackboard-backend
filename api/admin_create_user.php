<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

// Update the path to your db file
require_once __DIR__ . "/../test_db.php";

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    !isset($input["full_name"]) ||
    !isset($input["role"])
) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Split full name
$full_name = trim($input["full_name"]);
$nameParts = explode(" ", $full_name, 2);
$first_name = $nameParts[0];
$last_name = $nameParts[1] ?? "";

// Role
$role = strtolower($input["role"]);
$allowedRoles = ["student", "instructor", "admin"];
if (!in_array($role, $allowedRoles)) {
    echo json_encode(["success" => false, "error" => "Invalid role"]);
    exit;
}

// Generate email automatically
$email = strtolower(str_replace(" ", ".", $first_name . "." . $last_name)) . "@liu.edu.lb";

// Check if email exists
$check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo json_encode(["success" => false, "error" => "User already exists"]);
    exit;
}

// Generate password if not provided
$password = $input['password'] ?? bin2hex(random_bytes(4)); // 8-char random password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password_hash, role, department_id)
    VALUES (?, ?, ?, ?, ?, 1)
");
$stmt->execute([$first_name, $last_name, $email, $password_hash, $role]);

$userId = $pdo->lastInsertId();

echo json_encode([
    "success" => true,
    "user" => [
        "user_id" => (int)$userId,
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "role" => $role,
        "password" => $password // Optional: show generated password to admin
    ]
]);
