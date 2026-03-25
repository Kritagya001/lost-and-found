<?php
// create_tables.php - Run this once to set up your database

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "lost_found_db";

// Create connection without database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to MySQL<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$database' created or already exists<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Users table created<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create items table
$sql = "CREATE TABLE IF NOT EXISTS items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    item_date DATE NOT NULL,
    item_time TIME,
    type ENUM('Lost', 'Found') NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    user_id INT,
    image_path VARCHAR(500),
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    contact_note TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_type (type),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Items table created<br>";
} else {
    echo "Error creating items table: " . $conn->error . "<br>";
}

// Create uploads directory
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "✅ Uploads directory created<br>";
    } else {
        echo "❌ Failed to create uploads directory<br>";
    }
} else {
    echo "✅ Uploads directory already exists<br>";
}

// Create a test user
$testUsername = "testuser";
$testEmail = "test@example.com";
$testPassword = password_hash("password123", PASSWORD_DEFAULT);

// Check if test user exists
$check = $conn->query("SELECT id FROM users WHERE username = '$testUsername'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (username, email, password) VALUES ('$testUsername', '$testEmail', '$testPassword')";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Test user created (username: testuser, password: password123)<br>";
    }
}

// Create admin user
$adminCheck = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($adminCheck->num_rows == 0) {
    $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password) VALUES ('admin', 'admin@example.com', '$adminPass')";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Admin user created (username: admin, password: admin123)<br>";
    }
}

// Add sample items
$sampleCheck = $conn->query("SELECT COUNT(*) as count FROM items");
$row = $sampleCheck->fetch_assoc();
if ($row['count'] == 0) {
    // Get user_id for testuser
    $userResult = $conn->query("SELECT id FROM users WHERE username = 'testuser'");
    $userId = $userResult->fetch_assoc()['id'];
    
    $sampleItems = [
        ["iPhone 12", "Electronics", "Library", date('Y-m-d'), date('H:i:s'), "Lost", $userId, "Black iPhone with blue case", "John Doe", "john@example.com", "123-456-7890", ""],
        ["Student ID Card", "Documents", "Cafeteria", date('Y-m-d'), date('H:i:s'), "Found", $userId, "Student ID in black holder", "Jane Smith", "jane@example.com", "987-654-3210", "Found near counter"],
        ["Black Jacket", "Clothing", "Sports Complex", date('Y-m-d'), date('H:i:s'), "Lost", $userId, "Nike jacket size M", "Mike Johnson", "mike@example.com", "555-123-4567", ""]
    ];
    
    foreach ($sampleItems as $item) {
        $sql = "INSERT INTO items (item_name, category, location, item_date, item_time, type, user_id, description, contact_name, contact_email, contact_phone, contact_note) 
                VALUES ('{$item[0]}', '{$item[1]}', '{$item[2]}', '{$item[3]}', '{$item[4]}', '{$item[5]}', {$item[6]}, '{$item[7]}', '{$item[8]}', '{$item[9]}', '{$item[10]}', '{$item[11]}')";
        $conn->query($sql);
    }
    echo "✅ Sample items added<br>";
}

echo "<br><h3>✅ Setup Complete!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li><a href='test_connection.php'>Test Connection</a></li>";
echo "<li><a href='../Frontend/login.html'>Go to Login Page</a></li>";
echo "</ul>";

$conn->close();
?>