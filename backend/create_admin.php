<?php
// backend/create_admin.php
error_reporting(0);
header('Content-Type: application/json');

require_once 'db_connect.php';

$username = 'admin';
$email = 'admin@example.com';
$password = password_hash('Admin@123', PASSWORD_DEFAULT);

// Check if user exists
$check = $conn->query("SELECT id FROM users WHERE username = '$username'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Admin user created successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'Admin user already exists']);
}

$conn->close();
?>