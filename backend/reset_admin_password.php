<?php
// backend/reset_admin_password.php
header('Content-Type: text/html; charset=utf-8');

require_once 'db_connect.php';

echo "<h2>🔧 Reset Admin Password</h2>";

$new_password = 'Admin@123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "✅ Admin password has been reset to: <strong>Admin@123</strong><br>";
    echo "<br>Try logging in with:<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>Admin@123</strong><br>";
} else {
    echo "❌ Failed to reset password: " . $conn->error . "<br>";
}

// Also show all users
echo "<br><h3>📊 Current Users:</h3>";
$result = $conn->query("SELECT id, username, email, is_admin FROM users");
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'>";
echo "<th>ID</th><th>Username</th><th>Email</th><th>Is Admin</th>";
echo "</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . ($row['is_admin'] ? "✅ Yes" : "❌ No") . "</td>";
    echo "</tr>";
}
echo "</table>";

$stmt->close();
$conn->close();
?>