<?php

// debug_items.php
header('Content-Type: application/json');

require_once 'db_connect.php';

$sql = "SELECT 
            i.*, 
            u.username as reported_by,
            CONCAT('http://localhost/lost-found-project/backend/', i.image_path) as full_image_url
        FROM items i
        LEFT JOIN users u ON i.user_id = u.id
        ORDER BY i.id DESC";

$result = $conn->query($sql);

$items = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode([
    'total_items' => count($items),
    'items' => $items,
    'database_error' => $conn->error ?? null
], JSON_PRETTY_PRINT);

$conn->close();
?>