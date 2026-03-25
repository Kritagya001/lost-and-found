<?php
// backend/test_connection.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Connection</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #f0f0f0; padding: 10px; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔌 Connection Test</h1>
    
    <?php
    require_once 'db_connect.php';
    
    echo "<div class='info'>";
    if (isset($conn) && !$conn->connect_error) {
        echo "<p class='success'>✅ Database connected successfully!</p>";
        
        // Test query
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>📊 Users in database: " . $row['count'] . "</p>";
        }
        
        $result = $conn->query("SELECT COUNT(*) as count FROM items");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>📊 Items in database: " . $row['count'] . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Database connection failed</p>";
    }
    echo "</div>";
    ?>
    
    <h2>Test Login Credentials:</h2>
    <ul>
        <li><strong>testuser</strong> / password123</li>
        <li><strong>admin</strong> / admin123</li>
    </ul>
    
    <p>
        <a href="setup_database.php">Run Database Setup</a> | 
        <a href="../frontend/login.html">Go to Login Page</a>
    </p>
</body>
</html>