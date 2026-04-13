<?php
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$event_id = (int)$_GET['id'];
$event = getEventById($event_id);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Only show approved events to public
if (!$auth->isLoggedIn() && $event['status'] != 'approved') {
    header('Location: index.php');
    exit;
}

$is_registered = false;
$is_creator = false;
$is_admin = false;

if ($auth->isLoggedIn()) {
    $is_registered = isUserRegistered($event_id, $_SESSION['user_id']);
    $is_creator = isEventCreator($event_id, $_SESSION['user_id']);
    $is_admin = $auth->isAdmin();
}

$success_msg = '';
$error_msg = '';

// Handle member registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register']) && $auth->isLoggedIn()) {
    $result = registerForEvent($event_id, $_SESSION['user_id']);
    if (isset($result['success'])) {
        $success_msg = 'Successfully registered for the event!';
        $is_registered = true;
    } else {
        $error_msg = $result['error'];
    }
}

// Handle guest registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guest_register'])) {
    $name = sanitize($_POST['guest_reg_name']);
    $email = sanitize($_POST['guest_reg_email']);
    $phone = sanitize($_POST['guest_reg_phone']);
    if (registerGuest($event_id, $name, $email, $phone)) {
        $success_msg = "You have successfully registered for this event!";
    } else {
        $error_msg = "Registration failed. You may already be registered.";
    }
}

// Handle member comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && $auth->isLoggedIn()) {
    $comment = sanitize($_POST['comment']);
    if (addComment($event_id, $_SESSION['user_id'], $comment)) {
        $success_msg = "Comment added successfully!";
    } else {
        $error_msg = "Failed to add comment.";
    }
}

// Handle guest comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guest_comment'])) {
    $name = sanitize($_POST['guest_name']);
    $email = sanitize($_POST['guest_email']);
    $comment = sanitize($_POST['guest_comment_text']);
    if (addGuestComment($event_id, $name, $email, $comment)) {
        $success_msg = "Comment posted successfully!";
    } else {
        $error_msg = "Failed to post comment.";
    }
}

// Handle event approval (admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve']) && $is_admin) {
    if (approveEvent($event_id)) {
        $success_msg = "Event approved successfully!";
        $event = getEventById($event_id);
    }
}

// Get all attendees and comments
$attendees = getAllAttendees($event_id);
$all_comments = getAllComments($event_id);
?>

<div class="row">
    <div class="col-md-8">
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <img src="<?php echo getEventImage(isset($event['image']) ? $event['image'] : ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="max-height: 400px; object-fit: cover;">
            <div class="card-body">
                <div class="position-relative">
                    <?php if ($event['status'] !== 'approved' && $auth->isAdmin()): ?>
                        <span class="event-status status-<?php echo $event['status']; ?>"><?php echo ucfirst($event['status']); ?></span>
                    <?php endif; ?>
                    <h1 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h1>
                </div>
                
                <div class="event-details mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fas fa-calendar-alt text-primary"></i> <strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></p>
                            <p><i class="fas fa-clock text-primary"></i> <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></p>
                            <p><i class="fas fa-map-marker-alt text-primary"></i> <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fas fa-building text-primary"></i> <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue'] ?: 'Main Church Hall'); ?></p>
                            <p><i class="fas fa-user text-primary"></i> <strong>Organizer:</strong> <?php echo htmlspecialchars($event['creator_name']); ?></p>
                            <?php if ($event['capacity'] > 0): ?>
                                <p><i class="fas fa-users text-primary"></i> <strong>Capacity:</strong> <?php echo $attendees['members']->num_rows + $attendees['guests']->num_rows; ?> / <?php echo $event['capacity']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="event-description mb-4">
                    <h4>About this Event</h4>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>

                <!-- Video Section - using absolute URLs -->
                <?php
                $video_html = '';
                if (!empty($event['video_file'])) {
                    // Use absolute URL for uploaded video
                    $video_url = (strpos($event['video_file'], 'http') === 0) ? $event['video_file'] : BASE_URL . $event['video_file'];
                    $ext = strtolower(pathinfo($event['video_file'], PATHINFO_EXTENSION));
                    $type = 'video/mp4';
                    if ($ext == 'webm') $type = 'video/webm';
                    elseif ($ext == 'ogg') $type = 'video/ogg';
                    $video_html = '<video controls style="width:100%; max-height:400px;"><source src="'.$video_url.'" type="'.$type.'">Your browser does not support video.</video>';
                } elseif (!empty($event['video_url'])) {
                    $embed_url = getVideoEmbedUrl($event['video_url']);
                    if ($embed_url) {
                        $video_html = '<iframe src="'.$embed_url.'" frameborder="0" allowfullscreen style="width:100%; height:400px;"></iframe>';
                    }
                }
                if ($video_html): ?>
                    <div class="event-video mb-4">
                        <h4>Event Video</h4>
                        <div class="ratio ratio-16x9"><?php echo $video_html; ?></div>
                    </div>
                <?php endif; ?>

                <!-- Registration Section -->
                <?php if ($event['status'] == 'approved'): ?>
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php if (!$is_registered): ?>
                            <form method="POST" class="mb-4">
                                <button type="submit" name="register" class="btn btn-success btn-lg">Register for this Event</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success">You are registered for this event!</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Guest Registration Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Register as Guest</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="guest_register" value="1">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" name="guest_reg_name" class="form-control" placeholder="Full Name" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="email" name="guest_reg_email" class="form-control" placeholder="Email">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="tel" name="guest_reg_phone" class="form-control" placeholder="Phone">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success">Register as Guest</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($is_admin && $event['status'] == 'pending'): ?>
                    <form method="POST" class="mb-4">
                        <button type="submit" name="approve" class="btn btn-warning">Approve Event</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comment-section">
            <h4>Comments (<?php echo $all_comments['members']->num_rows + $all_comments['guests']->num_rows; ?>)</h4>

            <?php if ($auth->isLoggedIn()): ?>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your thoughts about this event..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Leave a Comment as Guest</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="guest_comment" value="1">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <input type="text" name="guest_name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <input type="email" name="guest_email" class="form-control" placeholder="Your Email">
                                </div>
                            </div>
                            <textarea name="guest_comment_text" rows="3" class="form-control mb-2" placeholder="Your comment..." required></textarea>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="comments-list">
                <?php
                $has_comments = false;
                if ($all_comments['members']->num_rows > 0) {
                    while ($comment = $all_comments['members']->fetch_assoc()) {
                        $has_comments = true;
                        $profile_img = isset($comment['profile_image']) && !empty($comment['profile_image']) ? $comment['profile_image'] : 'default-avatar.png';
                        ?>
                        <div class="comment">
                            <div class="d-flex">
                                <img src="uploads/profiles/<?php echo $profile_img; ?>" class="comment-avatar me-3" alt="Avatar" onerror="this.src='uploads/profiles/default-avatar.png'">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="comment-author"><?php echo htmlspecialchars($comment['full_name']); ?> (Member)</strong>
                                        <small class="comment-time"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                if ($all_comments['guests']->num_rows > 0) {
                    while ($guest_comment = $all_comments['guests']->fetch_assoc()) {
                        $has_comments = true;
                        ?>
                        <div class="comment">
                            <div class="d-flex">
                                <img src="uploads/profiles/default-avatar.png" class="comment-avatar me-3" alt="Avatar">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="comment-author"><?php echo htmlspecialchars($guest_comment['name']); ?> (Guest)</strong>
                                        <small class="comment-time"><?php echo date('M j, Y g:i A', strtotime($guest_comment['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($guest_comment['comment'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                if (!$has_comments) {
                    echo '<div class="text-muted text-center py-4"><i class="fas fa-comments fa-2x mb-2"></i><p>No comments yet. Be the first to comment!</p></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Attendees List -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Registered Attendees (<?php echo $attendees['members']->num_rows + $attendees['guests']->num_rows; ?>)</h5>
            </div>
            <div class="card-body">
                <?php
                $has_attendees = false;
                if ($attendees['members']->num_rows > 0) {
                    echo '<h6>Members:</h6><ul class="list-unstyled">';
                    while ($attendee = $attendees['members']->fetch_assoc()) {
                        $has_attendees = true;
                        echo '<li><i class="fas fa-user-circle me-2"></i> ' . htmlspecialchars($attendee['full_name']) . ' <small class="text-muted">(Member)</small></li>';
                    }
                    echo '</ul>';
                }
                if ($attendees['guests']->num_rows > 0) {
                    echo '<h6>Guests:</h6><ul class="list-unstyled">';
                    while ($guest = $attendees['guests']->fetch_assoc()) {
                        $has_attendees = true;
                        echo '<li><i class="fas fa-user me-2"></i> ' . htmlspecialchars($guest['name']) . ' <small class="text-muted">(Guest)</small></li>';
                    }
                    echo '</ul>';
                }
                if (!$has_attendees) {
                    echo '<p class="text-muted text-center mb-0">No attendees registered yet.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Event Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Event Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong> 
                    <span class="badge bg-<?php echo $event['status'] == 'approved' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($event['status']); ?>
                    </span>
                </p>
                <?php if ($event['capacity'] > 0): ?>
                    <?php $total = $attendees['members']->num_rows + $attendees['guests']->num_rows; ?>
                    <p><strong>Remaining Spots:</strong> <?php echo max(0, $event['capacity'] - $total); ?> / <?php echo $event['capacity']; ?></p>
                    <div class="progress mb-3">
                        <div class="progress-bar" style="width: <?php echo ($total / $event['capacity']) * 100; ?>%"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Share Event -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-share-alt"></i> Share This Event</h5>
            </div>
            <div class="card-body text-center">
                <p class="text-muted">Share with your friends and family!</p>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-primary m-1"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($event['title']); ?>" target="_blank" class="btn btn-outline-info m-1"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="https://wa.me/?text=<?php echo urlencode($event['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-success m-1"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>