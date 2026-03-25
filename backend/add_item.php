<?php
// ==================== ERROR HANDLING ====================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'add_item_errors.log');

// Start output buffering
ob_start();

// ==================== CORS HEADERS ====================
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==================== SESSION ====================
session_start();
require_once 'db_connect.php';

// ==================== CREATE UPLOADS FOLDER ====================
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ==================== GET USER ID ====================
$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} elseif (isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
}

if (!$userId || $userId <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'User authentication required']);
    exit;
}

// ==================== CHECK IF IT'S FORM DATA OR JSON ====================
$isFormData = false;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'multipart/form-data') !== false) {
    $isFormData = true;
}

// ==================== GET DATA BASED ON REQUEST TYPE ====================
$itemName = '';
$category = '';
$location = '';
$itemDate = date('Y-m-d');
$itemTime = date('H:i:s');
$type = 'Lost';
$contactName = '';
$contactEmail = '';
$contactPhone = '';
$contactNote = '';
$imagePath = null;

if ($isFormData) {
    // Handle FormData (with file upload)
    $itemName = $_POST['item_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $location = $_POST['location'] ?? '';
    $itemDate = $_POST['item_date'] ?? date('Y-m-d');
    $itemTime = $_POST['item_time'] ?? date('H:i:s');
    $type = $_POST['type'] ?? 'Lost';
    $contactName = $_POST['contact_name'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $contactPhone = $_POST['contact_phone'] ?? '';
    $contactNote = $_POST['contact_note'] ?? '';
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = handleImageUpload($_FILES['image'], $uploadDir);
    }
} else {
    // Handle JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    $itemName = $input['item_name'] ?? '';
    $category = $input['category'] ?? '';
    $location = $input['location'] ?? '';
    $itemDate = $input['item_date'] ?? date('Y-m-d');
    $itemTime = $input['item_time'] ?? date('H:i:s');
    $type = $input['type'] ?? 'Lost';
    $contactName = $input['contact_name'] ?? '';
    $contactEmail = $input['contact_email'] ?? '';
    $contactPhone = $input['contact_phone'] ?? '';
    $contactNote = $input['contact_note'] ?? '';
    $imagePath = $input['image_path'] ?? null;
}

// ==================== VALIDATE REQUIRED FIELDS ====================
if (empty($itemName) || empty($category) || empty($location) || empty($contactName) || empty($contactEmail)) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Please fill all required fields',
        'received' => [
            'item_name' => $itemName,
            'category' => $category,
            'location' => $location,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail
        ]
    ]);
    exit;
}

// Validate email
if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// ==================== INSERT INTO DATABASE ====================
$sql = "INSERT INTO items (item_name, category, location, item_date, item_time, type, user_id, image_path, contact_name, contact_email, contact_phone, contact_note, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssssisssss", 
    $itemName, $category, $location, $itemDate, $itemTime, $type, 
    $userId, $imagePath, $contactName, $contactEmail, $contactPhone, $contactNote
);

if ($stmt->execute()) {
    $itemId = $stmt->insert_id;
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Item reported successfully!',
        'item_id' => $itemId,
        'image_path' => $imagePath,
        'request_type' => $isFormData ? 'form-data' : 'json'
    ]);
} else {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

// ==================== HELPER FUNCTION ====================
function handleImageUpload($file, $uploadDir) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log('Upload error code: ' . $file['error']);
        return null;
    }
    
    // Validate file type - including webp
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    // Get file mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($fileType, $allowedTypes)) {
        error_log('Invalid file type: ' . $fileType);
        return null;
    }
    
    // Validate file size (2MB)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        error_log('File too large: ' . $file['size']);
        return null;
    }
    
    // Generate filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        chmod($targetPath, 0644);
        return $targetPath;
    }
    
    error_log('Failed to move uploaded file');
    return null;
}
?>