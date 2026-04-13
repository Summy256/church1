<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Test Upload</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; }
    .success { color: green; padding: 10px; background: #d4edda; border-left: 4px solid green; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border-left: 4px solid red; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border-left: 4px solid blue; margin: 10px 0; }
    .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    .button:hover { background: #2980b9; }
    input[type=file] { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    input[type=submit] { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    input[type=submit]:hover { background: #229954; }
    .preview { margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
    img { max-width: 300px; max-height: 200px; margin-top: 10px; border-radius: 5px; }
    </style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Test Upload Functionality</h1>";

// Check if UPLOAD_PATH is defined
if (defined('UPLOAD_PATH')) {
    echo "<div class='info'>UPLOAD_PATH: " . UPLOAD_PATH . "</div>";
    echo "<div class='info'>Exists: " . (file_exists(UPLOAD_PATH) ? "Yes" : "No") . "</div>";
    echo "<div class='info'>Writable: " . (is_writable(UPLOAD_PATH) ? "Yes" : "No") . "</div>";
} else {
    echo "<div class='error'>UPLOAD_PATH not defined in config/database.php!</div>";
}

// Check uploads/events folder
$events_dir = dirname(__DIR__) . '/uploads/events/';
echo "<div class='info'>Events directory: " . $events_dir . "</div>";
echo "<div class='info'>Exists: " . (file_exists($events_dir) ? "Yes" : "No") . "</div>";

if (!file_exists($events_dir)) {
    echo "<div class='info'>Creating events directory...</div>";
    if (mkdir($events_dir, 0777, true)) {
        echo "<div class='success'>Directory created successfully!</div>";
    } else {
        echo "<div class='error'>Failed to create directory!</div>";
    }
}

// Test form
echo "<h2>Upload Test Image</h2>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='image/*' required>";
echo "<br><br>";
echo "<input type='submit' name='test_upload' value='Upload Test Image'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>Upload Result:</h3>";
    $result = uploadFile($_FILES['test_file'], 'events');
    if (isset($result['success'])) {
        echo "<div class='success'>✓ Success! File uploaded: " . $result['path'] . "</div>";
        $full_path = dirname(__DIR__) . '/' . $result['path'];
        echo "<div class='info'>Full path: " . $full_path . "</div>";
        echo "<div class='info'>File exists: " . (file_exists($full_path) ? "Yes" : "No") . "</div>";
        echo "<div class='preview'>";
        echo "<strong>Preview:</strong><br>";
        echo "<img src='" . $result['path'] . "' alt='Uploaded Image' onerror=\"this.onerror=null; this.src='assets/images/default-event.jpg'; alert('Image not found at: " . $result['path'] . "')\">";
        echo "</div>";
    } else {
        echo "<div class='error'>✗ Error: " . $result['error'] . "</div>";
    }
}

// List existing uploaded files
echo "<h2>Existing Uploaded Files</h2>";
if (file_exists($events_dir)) {
    $files = scandir($events_dir);
    $found_files = false;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $found_files = true;
            echo "<div class='info'>";
            echo "<strong>" . $file . "</strong><br>";
            echo "<img src='uploads/events/" . $file . "' style='max-width: 150px; max-height: 100px; margin-top: 5px;' onerror=\"this.style.display='none'\">";
            echo "</div>";
        }
    }
    if (!$found_files) {
        echo "<div class='info'>No files uploaded yet.</div>";
    }
} else {
    echo "<div class='error'>Uploads/events folder does not exist!</div>";
}

echo "<br><a href='index.php' class='button'>Go to Homepage</a>";
echo "&nbsp;&nbsp;";
echo "<a href='member/create-event.php' class='button'>Create Event with Image</a>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?>