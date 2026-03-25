<?php
// backend/get_items.php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

// Get all items with user info
$sql = "SELECT 
            i.id,
            i.item_name,
            i.category,
            i.location,
            i.item_date,
            i.item_time,
            i.type,
            i.status,
            i.image_path,
            i.user_id,
            i.reported_at,
            i.contact_name,
            i.contact_email,
            i.contact_phone,
            i.contact_note,
            u.username as reported_by,
            u.email as user_email
        FROM items i 
        LEFT JOIN users u ON i.user_id = u.id 
        ORDER BY i.reported_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed',
        'error' => $conn->error
    ]);
    exit;
}

$items = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Ensure status has a default value
        if (!isset($row['status']) || $row['status'] === null) {
            $row['status'] = 'pending';
        }
        $items[] = $row;
    }
}

// Return items DIRECTLY as an array (not wrapped in success/count/items)
echo json_encode($items);

$conn->close();
?>