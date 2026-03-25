<?php

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Max-Age: 86400"); // 24 hours cache

// Handle OPTIONS request immediately 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'status_errors.log');

require_once 'db_connect.php';

// Check database connection
if (isset($GLOBALS['db_error'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $GLOBALS['db_error']
    ]);
    exit;
}

// Get POST data
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data',
        'raw' => $rawInput
    ]);
    exit;
}

if (!isset($input['item_id']) || !isset($input['status']) || !isset($input['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields',
        'received' => $input
    ]);
    exit;
}

$item_id = intval($input['item_id']);
$new_status = $conn->real_escape_string($input['status']);
$user_id = intval($input['user_id']);

// Verify item exists and check ownership
$check_sql = "SELECT user_id, item_name FROM items WHERE id = $item_id";
$check_result = $conn->query($check_sql);

if (!$check_result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

if ($check_result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found'
    ]);
    exit;
}

$item = $check_result->fetch_assoc();

if ($item['user_id'] != $user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'You can only update your own items'
    ]);
    exit;
}

// Update status
$sql = "UPDATE items SET status = '$new_status' WHERE id = $item_id";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true,
        'message' => "Item marked as $new_status",
        'item_name' => $item['item_name']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $conn->error
    ]);
}

$conn->close();
?>