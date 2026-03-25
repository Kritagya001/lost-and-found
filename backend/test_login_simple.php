<?php
// backend/test_login_simple.php
error_reporting(0);
header('Content-Type: application/json');

require_once 'db_connect.php';

$username = 'admin';
$password = 'Admin@123';

$sql = "SELECT id, username, email, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'message' => 'Login works!', 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$conn->close();
?>