<?php
require_once 'config/database.php';

echo "<h1>Checking Admin Account</h1>";

// Check if admin exists
$result = $conn->query("SELECT id, username, email, password, role FROM users WHERE username = 'admin'");

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<h2>Admin account found:</h2>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Role: " . $admin['role'] . "<br>";
    echo "Password hash: " . $admin['password'] . "<br>";
    
    // Test password
    $test_password = 'Admin@123';
    if (password_verify($test_password, $admin['password'])) {
        echo "<span style='color: green'>✓ Password 'Admin@123' is correct!</span><br>";
    } else {
        echo "<span style='color: red'>✗ Password 'Admin@123' is incorrect!</span><br>";
        echo "We need to reset the password.<br>";
    }
} else {
    echo "<span style='color: red'>✗ No admin account found!</span><br>";
    echo "We need to create an admin account.<br>";
}

echo "<br><a href='reset-admin.php'>Click here to reset/create admin account</a>";
?>