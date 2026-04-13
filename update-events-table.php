<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Update Events Table</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; }
    .success { color: green; padding: 10px; background: #d4edda; border-left: 4px solid green; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border-left: 4px solid red; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border-left: 4px solid blue; margin: 10px 0; }
    .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    .button:hover { background: #2980b9; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
    </style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Update Events Table - Add Image and Video Columns</h1>";

// Check if image column exists
$check_image = $conn->query("SHOW COLUMNS FROM events LIKE 'image'");
if ($check_image->num_rows == 0) {
    echo "<div class='info'>Adding 'image' column to events table...</div>";
    $add_image = $conn->query("ALTER TABLE events ADD COLUMN image VARCHAR(255) AFTER capacity");
    if ($add_image) {
        echo "<div class='success'>✓ Successfully added 'image' column to events table</div>";
    } else {
        echo "<div class='error'>✗ Failed to add image column: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='success'>✓ 'image' column already exists in events table</div>";
}

// Check if video_url column exists
$check_video = $conn->query("SHOW COLUMNS FROM events LIKE 'video_url'");
if ($check_video->num_rows == 0) {
    echo "<div class='info'>Adding 'video_url' column to events table...</div>";
    $add_video = $conn->query("ALTER TABLE events ADD COLUMN video_url VARCHAR(500) AFTER image");
    if ($add_video) {
        echo "<div class='success'>✓ Successfully added 'video_url' column to events table</div>";
    } else {
        echo "<div class='error'>✗ Failed to add video_url column: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='success'>✓ 'video_url' column already exists in events table</div>";
}

// Check if category column exists (optional)
$check_category = $conn->query("SHOW COLUMNS FROM events LIKE 'category'");
if ($check_category->num_rows == 0) {
    echo "<div class='info'>Adding 'category' column to events table...</div>";
    $add_category = $conn->query("ALTER TABLE events ADD COLUMN category VARCHAR(100) DEFAULT NULL AFTER video_url");
    if ($add_category) {
        echo "<div class='success'>✓ Successfully added 'category' column to events table</div>";
    } else {
        echo "<div class='error'>✗ Failed to add category column: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='success'>✓ 'category' column already exists in events table</div>";
}

// Show current table structure
echo "<h2>Current Events Table Structure</h2>";
$columns = $conn->query("SHOW COLUMNS FROM events");
if ($columns && $columns->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($column = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ? $column['Default'] : 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for any existing events without image
$events_without_image = $conn->query("SELECT COUNT(*) as count FROM events WHERE image IS NULL OR image = ''");
if ($events_without_image && $events_without_image->num_rows > 0) {
    $count = $events_without_image->fetch_assoc();
    echo "<div class='info'>ℹ️ There are " . $count['count'] . " events without images. They will use the default image.</div>";
}

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Make sure the uploads/events folder exists and is writable</li>";
echo "<li>Test image upload by creating a new event with an image</li>";
echo "<li>After creating an event, check if the image appears in the event details</li>";
echo "</ol>";

echo "<a href='test-upload.php' class='button'>Test Upload</a>";
echo "&nbsp;&nbsp;";
echo "<a href='index.php' class='button'>Go to Homepage</a>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?>