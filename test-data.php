<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Database Data Test</h1>";

// Check users
$users = $conn->query("SELECT * FROM users");
echo "<h2>Users (" . $users->num_rows . ")</h2>";
while ($user = $users->fetch_assoc()) {
    echo "ID: {$user['id']}, Name: {$user['full_name']}, Role: {$user['role']}, Status: {$user['status']}<br>";
}

// Check events
$events = $conn->query("SELECT * FROM events");
echo "<h2>Events (" . $events->num_rows . ")</h2>";
while ($event = $events->fetch_assoc()) {
    echo "ID: {$event['id']}, Title: {$event['title']}, Status: {$event['status']}<br>";
}

// Check if default admin exists
$admin = $conn->query("SELECT * FROM users WHERE username = 'admin' AND role = 'owner'");
if ($admin->num_rows > 0) {
    echo "<br>✓ Default admin account exists<br>";
} else {
    echo "<br>✗ Default admin account missing - Run the SQL script<br>";
}

echo "<br><a href='index.php'>Go to Homepage</a>";
?>