<?php
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Fix Permissions</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; }
    .success { color: green; padding: 10px; background: #d4edda; border-left: 4px solid green; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border-left: 4px solid red; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border-left: 4px solid blue; margin: 10px 0; }
    .button { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    .button:hover { background: #2980b9; }
    </style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>Setting up Upload Folders</h1>";

$folders = [
    'uploads',
    'uploads/events',
    'uploads/profiles',
    'uploads/videos'
];

foreach ($folders as $folder) {
    $full_path = __DIR__ . '/' . $folder;
    echo "<h3>$folder</h3>";
    
    if (!file_exists($full_path)) {
        if (mkdir($full_path, 0777, true)) {
            echo "<div class='success'>✓ Created folder: $folder</div>";
        } else {
            echo "<div class='error'>✗ Failed to create folder: $folder</div>";
        }
    } else {
        echo "<div class='success'>✓ Folder exists: $folder</div>";
    }
    
    // Try to set permissions (works on some Windows configurations)
    if (is_writable($full_path)) {
        echo "<div class='success'>✓ Folder is writable</div>";
    } else {
        echo "<div class='error'>⚠ Folder is NOT writable. Please check permissions.</div>";
        echo "<div class='info'>On Windows, right-click the folder → Properties → Security → Add 'Everyone' with Write permission</div>";
    }
}

echo "<h2>What to do if folders are not writable:</h2>";
echo "<ol>";
echo "<li>Navigate to: <strong>C:\\wamp64\\www\\church-scheduler\\uploads</strong></li>";
echo "<li>Right-click on the 'uploads' folder</li>";
echo "<li>Select 'Properties'</li>";
echo "<li>Click on 'Security' tab</li>";
echo "<li>Click 'Edit' to change permissions</li>";
echo "<li>Select 'Users' or 'Everyone'</li>";
echo "<li>Check 'Full control' or 'Write' permission</li>";
echo "<li>Click 'Apply' and 'OK'</li>";
echo "</ol>";

echo "<br><a href='test-upload.php' class='button'>Test Upload</a>";
echo "&nbsp;&nbsp;";
echo "<a href='index.php' class='button'>Go to Homepage</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>