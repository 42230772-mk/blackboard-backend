<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if (!isset($_SESSION["user"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Not authenticated"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => $_SESSION["user"]
]);
