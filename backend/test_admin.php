<?php
// backend/test_admin.php
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session' => $_SESSION,
    'local_storage_check' => 'Check browser console for localStorage'
]);
?>