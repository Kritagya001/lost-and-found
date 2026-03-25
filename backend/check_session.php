<?php

// check_session.php
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$logged_in = isset($_SESSION['user_id']);

echo json_encode([
    'success' => true,
    'logged_in' => $logged_in,
    'user_id' => $logged_in ? $_SESSION['user_id'] : null,
    'username' => $logged_in ? ($_SESSION['username'] ?? null) : null,
    'session_id' => session_id(),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>