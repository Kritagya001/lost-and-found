<?php
// backend/add_admin_field.php - Run this once to add admin column
header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$username = "root";
$password = "";
$database = "lost_found_db";

echo "<h2>🔧 Adding Admin Field to Database</h2>";
echo "<hr>";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to database<br><br>";

// Check if is_admin column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($check->num_rows == 0) {
    // Add is_admin column
    $sql = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
    if ($conn->query($sql)) {
        echo "✅ Added 'is_admin' column to users table<br>";
    } else {
        echo "❌ Failed to add column: " . $conn->error . "<br>";
    }
} else {
    echo "✅ 'is_admin' column already exists<br>";
}

// Make existing admin user (if exists)
$sql = "UPDATE users SET is_admin = 1 WHERE username = 'admin'";
if ($conn->query($sql)) {
    echo "✅ Set admin privileges for 'admin' user<br>";
}

// Create admin user if doesn't exist
$check = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($check->num_rows == 0) {
    $hashed = password_hash("Admin@123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, is_admin) VALUES ('admin', 'admin@example.com', '$hashed', 1)";
    if ($conn->query($sql)) {
        echo "✅ Created admin user (username: admin, password: Admin@123)<br>";
    } else {
        echo "❌ Failed to create admin user: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Admin user already exists<br>";
}

// Show all users with admin status
$result = $conn->query("SELECT id, username, email, is_admin FROM users ORDER BY id DESC");
echo "<br><h3>📊 Current Users:</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; background: white;'>";
echo "<tr style='background: #667eea; color: white;'>";
echo "<th>ID</th><th>Username</th><th>Email</th><th>Is Admin</th>";
echo "</tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . ($row['is_admin'] ? "✅ Yes (Admin)" : "❌ No (Regular User)") . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No users found</td></tr>";
}
echo "</table>";

echo "<br><h3>✅ Setup Complete!</h3>";
echo "<div style='background: #d1fae5; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<strong>📝 Login Credentials:</strong><br>";
echo "• <strong>Admin User:</strong> username: <code>admin</code>, password: <code>Admin@123</code><br>";
echo "• <strong>Test User:</strong> username: <code>testuser</code>, password: <code>password123</code><br>";
echo "</div>";

echo "<p>";
echo "<a href='../frontend/login.html' style='display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Go to Login Page</a>";
echo "<a href='../frontend/register.html' style='display: inline-block; background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📝 Register New User</a>";
echo "</p>";

$conn->close();
?>