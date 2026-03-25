<?php
// backend/setup_database.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .info { background: white; padding: 10px; margin: 5px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Lost & Found Database Setup</h1>
    
    <?php
    // Database connection
    $host = "localhost";
    $username = "root";
    $password = "";
    
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        echo "<div class='info error'>❌ Connection failed: " . $conn->connect_error . "</div>";
        exit;
    }
    
    echo "<div class='info success'>✅ Connected to MySQL</div>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS lost_found_db";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='info success'>✅ Database 'lost_found_db' created or already exists</div>";
    } else {
        echo "<div class='info error'>❌ Error creating database: " . $conn->error . "</div>";
    }
    
    // Select database
    $conn->select_db("lost_found_db");
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div class='info success'>✅ Users table created</div>";
    } else {
        echo "<div class='info error'>❌ Error creating users table: " . $conn->error . "</div>";
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div class='info success'>✅ Items table created</div>";
    } else {
        echo "<div class='info error'>❌ Error creating items table: " . $conn->error . "</div>";
    }
    
    // Create uploads directory
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0777, true)) {
            echo "<div class='info success'>✅ Uploads directory created</div>";
        }
    } else {
        echo "<div class='info success'>✅ Uploads directory already exists</div>";
    }
    
    // Create test user
    $testUsername = "testuser";
    $testEmail = "test@example.com";
    $testPassword = password_hash("password123", PASSWORD_DEFAULT);
    
    $check = $conn->query("SELECT id FROM users WHERE username = '$testUsername'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$testUsername', '$testEmail', '$testPassword')";
        if ($conn->query($sql)) {
            echo "<div class='info success'>✅ Test user created (username: testuser, password: password123)</div>";
        }
    }
    
    // Create admin user
    $adminCheck = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    if ($adminCheck->num_rows == 0) {
        $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES ('admin', 'admin@example.com', '$adminPass')";
        if ($conn->query($sql)) {
            echo "<div class='info success'>✅ Admin user created (username: admin, password: admin123)</div>";
        }
    }
    
    $conn->close();
    ?>
    
    <hr>
    <h2>✅ Setup Complete!</h2>
    <p>You can now:</p>
    <ul>
        <li><a href="test_connection.php">Test Database Connection</a></li>
        <li><a href="../frontend/login.html">Go to Login Page</a></li>
    </ul>
</body>
</html>