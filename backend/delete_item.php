<?php

// delete_item.php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$itemId = $input['item_id'] ?? $_POST['item_id'] ?? null;
$userId = $input['user_id'] ?? $_POST['user_id'] ?? null;

// Validate
if (!$itemId || !is_numeric($itemId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

if (!$userId || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User authentication required']);
    exit;
}

// First, check if item exists and belongs to user
$checkSql = "SELECT i.*, u.username FROM items i 
             LEFT JOIN users u ON i.user_id = u.id 
             WHERE i.id = ? AND i.user_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $itemId, $userId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Item not found or you do not have permission']);
    exit;
}

$item = $result->fetch_assoc();
$checkStmt->close();

// Delete associated image file if exists
if ($item['image_path'] && file_exists($item['image_path'])) {
    unlink($item['image_path']);
}

// Delete item from database
$deleteSql = "DELETE FROM items WHERE id = ? AND user_id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $itemId, $userId);

if ($deleteStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully',
        'deleted_item' => $item['item_name']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete item: ' . $conn->error]);
}

$deleteStmt->close();
$conn->close();
?>