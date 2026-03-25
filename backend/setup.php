<?php

echo "<h2>Lost & Found System - Database Setup</h2>";

// database connection
include 'db_connect.php';

// Check if we can connect to MySQL
if ($conn->connect_error) {
    die("❌ Cannot connect to MySQL: " . $conn->connect_error);
}
echo "✅ Connected to MySQL server<br>";

// 1. CREATE DATABASE
$sql = "CREATE DATABASE IF NOT EXISTS lost_found_db 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database 'lost_found_db' created/verified<br>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db("lost_found_db");

// 2. CREATE USERS TABLE
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'users' created/verified<br>";
} else {
    die("❌ Error creating users table: " . $conn->error);
}

// 3. CREATE ITEMS TABLE (UPDATED WITH CONTACT INFO)
$sql = "CREATE TABLE IF NOT EXISTS items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    item_date DATE NOT NULL,
    item_time TIME,
    type ENUM('Lost', 'Found') NOT NULL,
    user_id INT,
    image_path VARCHAR(500),
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    contact_note TEXT,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_type (type),
    INDEX idx_date (item_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'items' created/verified<br>";
} else {
    die("❌ Error creating items table: " . $conn->error);
}

// 4. ADD DEFAULT ADMIN USER
$check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password) 
            VALUES ('admin', 'admin@example.com', '$hashed_password')";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Default admin user created (username: admin, password: admin123)<br>";
    } else {
        echo "⚠️ Note: Admin user might already exist<br>";
    }
} else {
    echo "✅ Admin user already exists<br>";
}

// 5. ADD SAMPLE ITEMS (Optional - for demo)
$check_items = $conn->query("SELECT COUNT(*) as count FROM items");
$row = $check_items->fetch_assoc();
if ($row['count'] == 0) {
    $sample_items = [
        ["iPhone 12", "Electronics", "Library", "2024-03-15", "14:30:00", "Lost", "Black iPhone with blue case"],
        ["Student ID Card", "Documents", "Cafeteria", "2024-03-14", "12:00:00", "Found", "John Doe's student ID"],
        ["Black Jacket", "Clothing", "Sports Complex", "2024-03-13", "18:45:00", "Lost", "Nike jacket with hood"]
    ];
    
  foreach ($sample_items as $item) {
    $sql = "INSERT INTO items (item_name, category, location, item_date, item_time, type, description, contact_name, contact_email, contact_phone) 
            VALUES ('{$item[0]}', '{$item[1]}', '{$item[2]}', '{$item[3]}', '{$item[4]}', '{$item[5]}', '{$item[6]}', 'John Doe', 'john@example.com', '123-456-7890')";
    $conn->query($sql);
}
    echo "✅ Added sample items for demonstration<br>";
}

// 6. CREATE ADDITIONAL TABLES (Future Expansion)
$additional_tables = [
    "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) UNIQUE NOT NULL,
        icon VARCHAR(50),
        color VARCHAR(20)
    )",
    
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        message TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

foreach ($additional_tables as $table_sql) {
    if ($conn->query($table_sql) !== TRUE) {
        echo "⚠️ Note: Could not create additional table<br>";
    }
}

echo "<hr>";
echo "<h3>✅ Setup Complete!</h3>";
echo "<p>Database is ready to use. You can now:</p>";
echo "<ul>";
echo "<li><a href='../Frontend/index.html'>Go to Application</a></li>";
echo "<li><a href='test_connection.php'>Test Connection</a></li>";
echo "<li><a href='../phpmyadmin' target='_blank'>Check in phpMyAdmin</a></li>";
echo "</ul>";

// Show database summary
echo "<h4>Database Summary:</h4>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "Total Users: " . $row['count'] . "<br>";

$result = $conn->query("SELECT COUNT(*) as count FROM items");
$row = $result->fetch_assoc();
echo "Total Items: " . $row['count'] . "<br>";

$conn->close();
?>