<?php
// backend/test_db.php
header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$username = "root";
$password = "";
$database = "lost_found_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Check</h2>";

// Check users table structure
echo "<h3>Users Table Structure:</h3>";
$result = $conn->query("DESCRIBE users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if is_admin column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ is_admin column EXISTS</p>";
} else {
    echo "<p style='color: red;'>❌ is_admin column MISSING! Run add_admin_field.php first</p>";
}

// Check users
echo "<h3>Current Users:</h3>";
$result = $conn->query("SELECT id, username, email, is_admin FROM users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>is_admin</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . ($row['is_admin'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>