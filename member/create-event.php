<?php
require_once '../includes/header.php';
if (!$auth->isLoggedIn()) { header('Location: ../login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST) && !empty($_FILES)) {
        $error = 'File too large. Maximum allowed size: 100MB.';
    } elseif (isset($_POST['title']) && !empty(trim($_POST['title']))) {
        $event_data = [
            'title'       => sanitize($_POST['title']),
            'description' => sanitize($_POST['description']),
            'event_date'  => sanitize($_POST['event_date']),
            'start_time'  => sanitize($_POST['start_time']),
            'end_time'    => sanitize($_POST['end_time']),
            'location'    => sanitize($_POST['location']),
            'venue'       => sanitize($_POST['venue']),
            'capacity'    => (int)sanitize($_POST['capacity'])
        ];

        // --- Check for event conflict (any event on same date) ---
        $conflict = hasEventConflict($event_data['event_date']);
        
        if ($conflict['conflict']) {
            // USING DOUBLE QUOTES so variables are parsed correctly
            $error = "⚠️ Event conflict! There is already an event scheduled on this date:<br>
                      <strong>{$conflict['title']}</strong> from {$conflict['start_time']} to {$conflict['end_time']}<br>
                      Location: {$conflict['location']}<br>
                      Please choose a different date.";
        } else {
            // --- Image upload ---
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['event_image'], 'events');
                if (isset($upload['success'])) {
                    $event_data['image'] = $upload['path'];
                } else {
                    $error = $upload['error'];
                }
            }

            // --- Video file upload ---
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['video_file'], 'videos');
                if (isset($upload['success'])) {
                    $event_data['video_file'] = $upload['path'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] != UPLOAD_ERR_NO_FILE) {
                $error = 'Video upload error code: ' . $_FILES['video_file']['error'];
            }

            // --- Video URL ---
            if (!empty($_POST['video_url'])) {
                $event_data['video_url'] = sanitize($_POST['video_url']);
            }

            if (empty($error)) {
                $event_id = createEvent($event_data, $_SESSION['user_id']);
                if ($event_id) {
                    $success = 'Event created successfully! It will be reviewed.';
                } else {
                    $error = 'Failed to create event. Please try again.';
                }
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Create New Event</h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Main Church Building" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="venue" class="form-label">Venue</label>
                            <input type="text" class="form-control" id="venue" name="venue" placeholder="e.g., Main Hall">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity (0 for unlimited)</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="event_image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                        <small class="text-muted">Optional. JPG, PNG, GIF</small>
                        <div id="imagePreview" class="mt-2" style="display:none;">
                            <img id="preview" src="#" style="max-width:200px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="video_file" class="form-label">Upload Video File (MP4, WebM, OGG)</label>
                        <input type="file" class="form-control" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg">
                        <small class="text-muted">Max 100MB. The video will appear after event approval.</small>
                    </div>
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Or Video URL (YouTube/Vimeo)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">Optional link instead of upload.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('event_image').onchange = function(e) {
    if (e.target.files.length) {
        var fr = new FileReader();
        fr.onload = function() {
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('preview').src = fr.result;
        };
        fr.readAsDataURL(e.target.files[0]);
    }
};
</script>
<?php require_once '../includes/footer.php'; ?>