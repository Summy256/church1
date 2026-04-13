<?php
require_once 'config/database.php';

echo "<h1>Adding video_file column to events table</h1>";

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM events LIKE 'video_file'");
if ($check->num_rows == 0) {
    $alter = $conn->query("ALTER TABLE events ADD COLUMN video_file VARCHAR(255) AFTER video_url");
    if ($alter) {
        echo "<p style='color:green'>✓ Column 'video_file' added successfully.</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:blue'>✓ Column 'video_file' already exists.</p>";
}
?>