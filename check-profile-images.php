<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Check Profile Images</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
    .success { color: green; }
    .error { color: red; }
    img { max-width: 100px; max-height: 100px; border-radius: 50%; margin: 5px; object-fit: cover; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
    .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    .button:hover { background: #2980b9; }
    </style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Profile Images Check</h1>";

// Get all users
$users = $conn->query("SELECT id, username, full_name, profile_image FROM users ORDER BY id");
if ($users && $users->num_rows > 0) {
    echo "户<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Database Path</th><th>File Exists?</th><th>Preview</th></tr>";
    while ($user = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>" . ($user['profile_image'] ? $user['profile_image'] : 'NULL') . "</td>";
        
        $file_exists = false;
        if ($user['profile_image']) {
            $full_path = __DIR__ . '/' . $user['profile_image'];
            if (file_exists($full_path)) {
                $file_exists = true;
                echo "<td class='success'>✓ Yes</td>";
                echo "<td><img src='{$user['profile_image']}' onerror=\"this.style.display='none'\"></td>";
            } else {
                echo "<td class='error'>✗ No (File missing)</td>";
                echo "<td>File not found</td>";
            }
        } else {
            echo "<td>No image set</td>";
            echo "<td>Default avatar</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

echo "<h2>Uploads/Profiles Folder Contents</h2>";
$profiles_dir = __DIR__ . '/uploads/profiles/';
if (file_exists($profiles_dir)) {
    $files = scandir($profiles_dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file - " . filesize($profiles_dir . $file) . " bytes</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p class='error'>Uploads/profiles folder does not exist!</p>";
}

echo "<br><a href='member/profile.php' class='button'>Go to Profile Page</a>";
echo "&nbsp;&nbsp;<a href='index.php' class='button'>Go to Homepage</a>";
echo "</div>";
echo "</body>";
echo "</html>";
?>