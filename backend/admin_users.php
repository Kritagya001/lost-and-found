<?php
// backend/admin_users.php - UPDATED to use user_id from request
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

// Get user_id from GET or POST
$userId = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['admin_id'] ?? $input['user_id'] ?? null;
}

// If no user_id provided, try session (fallback)
if (!$userId && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
}

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID required. Please login again.']);
    exit;
}

// Check if user is admin
$checkAdmin = $conn->query("SELECT is_admin FROM users WHERE id = $userId");
if (!$checkAdmin) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$adminRow = $checkAdmin->fetch_assoc();

if (!$adminRow || $adminRow['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

// Handle different actions
$action = $_GET['action'] ?? '';

// GET USERS - LIST ALL USERS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $sql = "SELECT id, username, email, is_admin, created_at FROM users ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    exit;
}

// DELETE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $targetUserId = $input['user_id'] ?? 0;
    $adminId = $input['admin_id'] ?? $userId;
    
    // Prevent admin from deleting themselves
    if ($targetUserId == $adminId) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
        exit;
    }
    
    // Check if target user exists
    $checkUser = $conn->query("SELECT id, username, is_admin FROM users WHERE id = $targetUserId");
    if ($checkUser->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $userToDelete = $checkUser->fetch_assoc();
    
    // Don't allow deleting other admins
    if ($userToDelete['is_admin'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete other admin users']);
        exit;
    }
    
    // Delete user's items first
    $conn->query("DELETE FROM items WHERE user_id = $targetUserId");
    
    // Delete user
    $sql = "DELETE FROM users WHERE id = $targetUserId";
    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully',
            'deleted_user' => $userToDelete['username']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
$conn->close();
?>