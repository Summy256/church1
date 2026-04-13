<?php
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_video'])) {
    $result = uploadFile($_FILES['test_video'], 'videos');
    echo '<pre>'; print_r($result); echo '</pre>';
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_video" accept="video/*">
    <button type="submit">Upload</button>
</form>