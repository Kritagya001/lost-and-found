<?php

session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");

// Only allow admins or specific users to clear all items
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

require_once 'db_connect.php';

// You might want to add admin check here
// if ($_SESSION['user_role'] !== 'admin') { ... }

// Clear all items (use with caution!)
$sql = "DELETE FROM items";
if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'All items cleared']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to clear items']);
}

$conn->close();
?>