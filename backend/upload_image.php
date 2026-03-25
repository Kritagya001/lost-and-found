<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Check if image was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

// Validate file
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
$maxFileSize = 2 * 1024 * 1024; // 2MB

$file = $_FILES['image'];

// Check file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF allowed']);
    exit;
}

// Check file size
if ($file['size'] > $maxFileSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 2MB']);
    exit;
}

// Generate unique filename
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('item_', true) . '.' . $fileExtension;
$filePath = $uploadDir . $fileName;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded',
        'image_path' => $filePath,
        'file_name' => $fileName
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Upload failed']);
}
?>