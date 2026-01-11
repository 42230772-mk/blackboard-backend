<?php
// test_db.php

$host = "127.0.0.1"; // or "localhost"
$db_name = "blackboard_system";
$username = "root"; // XAMPP default username
$password = "";     // XAMPP default password

try {
    // Use PDO for secure DB connection
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    // Set PDO error mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Return JSON error if connection fails
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
