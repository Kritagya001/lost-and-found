<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (!$input || !isset($input['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$item_id = (int)$input['item_id'];

// ==================== DETERMINE WHAT TO UPDATE ====================
$update_fields = [];
$update_values = [];
$param_types = '';

// Check for image path update
if (isset($input['image_path'])) {
    $update_fields[] = 'image_path = ?';
    $update_values[] = $input['image_path'];
    $param_types .= 's';
}

// Check for contact information updates
if (isset($input['contact_name'])) {
    $update_fields[] = 'contact_name = ?';
    $update_values[] = $input['contact_name'];
    $param_types .= 's';
}

if (isset($input['contact_email'])) {
    $update_fields[] = 'contact_email = ?';
    $update_values[] = $input['contact_email'];
    $param_types .= 's';
}

if (isset($input['contact_phone'])) {
    $update_fields[] = 'contact_phone = ?';
    $update_values[] = $input['contact_phone'];
    $param_types .= 's';
}

if (isset($input['contact_note'])) {
    $update_fields[] = 'contact_note = ?';
    $update_values[] = $input['contact_note'];
    $param_types .= 's';
}

// Check for other item field updates
$other_fields = ['item_name', 'category', 'location', 'item_date', 'item_time', 'type', 'description'];
foreach ($other_fields as $field) {
    if (isset($input[$field])) {
        $update_fields[] = "$field = ?";
        $update_values[] = $input[$field];
        $param_types .= 's';
    }
}

// If nothing to update, return error
if (empty($update_fields)) {
    echo json_encode(['success' => false, 'message' => 'No update data provided']);
    exit;
}

// ==================== BUILD AND EXECUTE UPDATE QUERY ====================
// Add item_id to values for WHERE clause
$update_values[] = $item_id;
$param_types .= 'i';

// Build SQL query
$sql = "UPDATE items SET " . implode(', ', $update_fields) . " WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

// Bind parameters
$stmt->bind_param($param_types, ...$update_values);

if ($stmt->execute()) {
    // Get updated item for response
    $select_sql = "SELECT * FROM items WHERE id = ?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("i", $item_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $updated_item = $result->fetch_assoc();
    $select_stmt->close();
    
    // Prepare success response
    $response = [
        'success' => true,
        'message' => 'Item updated successfully',
        'item_id' => $item_id,
        'updated_fields' => array_map(function($field) {
            return str_replace(' = ?', '', $field);
        }, $update_fields)
    ];
    
    // Add updated contact info if relevant
    if (isset($input['contact_name']) || isset($input['contact_email'])) {
        $response['contact_info'] = [
            'name' => $input['contact_name'] ?? $updated_item['contact_name'] ?? '',
            'email' => $input['contact_email'] ?? $updated_item['contact_email'] ?? '',
            'phone' => $input['contact_phone'] ?? $updated_item['contact_phone'] ?? '',
            'note' => $input['contact_note'] ?? $updated_item['contact_note'] ?? ''
        ];
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>