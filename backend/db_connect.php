<?php
// backend/db_connect.php
$host = "localhost";
$username = "root";
$password = "";
$database = "lost_found_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection - DON'T output anything here
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset("utf8mb4");
?>