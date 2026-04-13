<?php
require_once 'config/database.php';

echo "<h1>Video File Updater</h1>";

// Get all video files in the uploads/videos folder
$video_dir = __DIR__ . '/uploads/videos/';
$videos = scandir($video_dir);
$video_files = array_filter($videos, function($file) {
    return !in_array($file, ['.', '..']) && preg_match('/\.(mp4|webm|ogg|mov)$/i', $file);
});

if (empty($video_files)) {
    echo "<p>No video files found in uploads/videos/</p>";
} else {
    echo "<h2>Found video files:</h2>";
    echo "<form method='POST'>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Video File</th><th>Assign to Event</th><th>Action</th></tr>";
    foreach ($video_files as $video) {
        echo "<tr>";
        echo "<td>$video</td>";
        echo "<td><select name='event_id_$video'>";
        echo "<option value=''>-- Select Event --</option>";
        
        // Fetch events that don't have a video yet
        $events = $conn->query("SELECT id, title FROM events WHERE video_file IS NULL OR video_file = '' ORDER BY id DESC");
        while ($event = $events->fetch_assoc()) {
            echo "<option value='{$event['id']}'>{$event['id']} - " . htmlspecialchars($event['title']) . "</option>";
        }
        echo "</select></td>";
        echo "<td><button type='submit' name='assign' value='$video'>Assign</button></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
}

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign'])) {
    $video_file = $_POST['assign'];
    $event_id = $_POST["event_id_$video_file"];
    if ($event_id) {
        $path = 'uploads/videos/' . $video_file;
        $stmt = $conn->prepare("UPDATE events SET video_file = ? WHERE id = ?");
        $stmt->bind_param("si", $path, $event_id);
        if ($stmt->execute()) {
            echo "<p style='color:green'>✓ Assigned $video_file to event ID $event_id</p>";
        } else {
            echo "<p style='color:red'>✗ Failed: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red'>Please select an event.</p>";
    }
}
?>