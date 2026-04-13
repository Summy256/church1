<?php
echo "<h1>System Test</h1>";

echo "<h2>PHP Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<h2>Required Extensions</h2>";
$extensions = ['mysqli', 'gd', 'fileinfo', 'json'];
foreach ($extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "✓ Installed" : "✗ Missing") . "<br>";
}

echo "<h2>Database Connection Test</h2>";
require_once 'config/database.php';

if ($conn->ping()) {
    echo "✓ Database connection successful!<br>";
    
    // Check tables
    $tables = ['users', 'events', 'comments', 'event_registrations', 'notifications'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' missing<br>";
        }
    }
} else {
    echo "✗ Database connection failed: " . $conn->connect_error . "<br>";
}

echo "<h2>Folder Permissions</h2>";
$folders = ['uploads', 'uploads/events', 'uploads/profiles', 'uploads/videos', 'assets/images'];
foreach ($folders as $folder) {
    if (file_exists($folder)) {
        echo "✓ Folder '$folder' exists - Permissions: " . substr(sprintf('%o', fileperms($folder)), -4) . "<br>";
    } else {
        echo "✗ Folder '$folder' missing<br>";
    }
}

echo "<h2>Login Test</h2>";
echo "Default Admin Login:<br>";
echo "Username: admin<br>";
echo "Password: Admin@123<br>";
echo "<br><a href='login.php'>Go to Login Page</a>";
?>