<?php
$dir = __DIR__ . '/uploads/videos/';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
    echo "Folder created: $dir<br>";
} else {
    echo "Folder exists: $dir<br>";
}
echo "Writable: " . (is_writable($dir) ? "Yes" : "No");
?>