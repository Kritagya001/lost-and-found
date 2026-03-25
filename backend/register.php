<?php
// backend/register.php - Updated with validation
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

// Validate input
if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

// Validate username - no numbers allowed
if (!preg_match('/^[A-Za-z]+$/', $username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username can only contain letters (no numbers or special characters)'
    ]);
    exit;
}

// Validate username length
if (strlen($username) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Username must be at least 3 characters'
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate password - must have at least 1 number and 1 special character
$hasNumber = preg_match('/[0-9]/', $password);
$hasSpecial = preg_match('/[^A-Za-z0-9]/', $password);

if (!$hasNumber || !$hasSpecial) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least 1 number and 1 special character (e.g., @, #, $, %, etc.)'
    ]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit;
}

// Check if user exists
$check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Username or email already exists'
    ]);
    exit;
}

// Hash password and insert (regular user, not admin)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! You can now login.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>