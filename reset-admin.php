<?php
require_once 'config/database.php';

echo "<h1>Reset Admin Account</h1>";

// First, let's see all users
echo "<h2>Current Users:</h2>";
$users = $conn->query("SELECT id, username, email, role FROM users");
if ($users && $users->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while ($user = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found.<br>";
}

// Reset/Create admin account
$new_password = 'Admin@123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $conn->query("SELECT id FROM users WHERE username = 'admin'");

if ($check && $check->num_rows > 0) {
    // Update existing admin
    $admin = $check->fetch_assoc();
    $stmt = $conn->prepare("UPDATE users SET password = ?, role = 'owner', status = 'active' WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $admin['id']);
    
    if ($stmt->execute()) {
        echo "<p style='color: green'>✓ Admin password updated successfully!</p>";
    } else {
        echo "<p style='color: red'>✗ Failed to update admin: " . $conn->error . "</p>";
    }
} else {
    // Create new admin
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'owner', 'active')");
    $admin_email = 'admin@church.com';
    $admin_name = 'System Administrator';
    $stmt->bind_param("ssss", $admin_username, $admin_email, $hashed_password, $admin_name);
    $admin_username = 'admin';
    
    if ($stmt->execute()) {
        echo "<p style='color: green'>✓ New admin account created successfully!</p>";
    } else {
        echo "<p style='color: red'>✗ Failed to create admin: " . $conn->error . "</p>";
    }
}

echo "<h2>Test Login with these credentials:</h2>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> Admin@123</li>";
echo "</ul>";

echo "<a href='login.php'>Go to Login Page</a><br>";
echo "<a href='index.php'>Go to Homepage</a>";
?>