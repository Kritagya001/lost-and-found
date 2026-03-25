<?php
// backend/check_admin.php - Check if user is admin
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Credentials: true');

require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_admin' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

$sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'is_admin' => $user['is_admin'] == 1,
        'user_id' => $userId,
        'username' => $_SESSION['username']
    ]);
} else {
    echo json_encode(['is_admin' => false, 'message' => 'User not found']);
}

$conn->close();
?>