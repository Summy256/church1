<?php
require_once '../includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_events = $conn->prepare("SELECT e.*, (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count FROM events e WHERE e.created_by = ? ORDER BY e.created_at DESC LIMIT 5");
$user_events->bind_param("i", $_SESSION['user_id']);
$user_events->execute();
$my_events = $user_events->get_result();

$registered_events = $conn->prepare("SELECT e.* FROM events e JOIN event_registrations er ON e.id = er.event_id WHERE er.user_id = ? AND e.event_date >= CURDATE() ORDER BY e.event_date ASC LIMIT 5");
$registered_events->bind_param("i", $_SESSION['user_id']);
$registered_events->execute();
$my_registered = $registered_events->get_result();

// Get user statistics
$result1 = $conn->query("SELECT COUNT(*) as count FROM events WHERE created_by = " . $_SESSION['user_id']);
$stats_my_events = $result1->fetch_assoc();
$result2 = $conn->query("SELECT COUNT(*) as count FROM event_registrations WHERE user_id = " . $_SESSION['user_id']);
$stats_registered = $result2->fetch_assoc();
$result3 = $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = " . $_SESSION['user_id']);
$stats_comments = $result3->fetch_assoc();

$stats = array(
    'my_events' => $stats_my_events['count'],
    'registered' => $stats_registered['count'],
    'comments' => $stats_comments['count']
);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="dashboard-widget text-center">
            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
            <h3><?php echo $stats['my_events']; ?></h3>
            <p>Events Created</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-widget text-center" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
            <i class="fas fa-check-circle fa-3x mb-2"></i>
            <h3><?php echo $stats['registered']; ?></h3>
            <p>Events Registered</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-widget text-center" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <i class="fas fa-comments fa-3x mb-2"></i>
            <h3><?php echo $stats['comments']; ?></h3>
            <p>Comments Made</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Events</h5>
            </div>
            <div class="card-body">
                <?php if ($my_events && $my_events->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($event = $my_events->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                            <span class="badge bg-<?php echo $event['status'] == 'approved' ? 'success' : 'warning'; ?> ms-2">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </small>
                                    </div>
                                    <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <a href="my-events.php" class="btn btn-link mt-3">View all my events →</a>
                <?php else: ?>
                    <p class="text-muted">You haven't created any events yet.</p>
                    <a href="create-event.php" class="btn btn-primary">Create Your First Event</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Upcoming Events I'm Registered For</h5>
            </div>
            <div class="card-body">
                <?php if ($my_registered && $my_registered->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($event = $my_registered->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                            <i class="fas fa-clock ms-2"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                                        </small>
                                    </div>
                                    <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You haven't registered for any upcoming events yet.</p>
                    <a href="../events.php" class="btn btn-primary">Browse Events</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="create-event.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                            Create Event
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="my-events.php" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-list fa-2x d-block mb-2"></i>
                            My Events
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="profile.php" class="btn btn-outline-info btn-lg w-100">
                            <i class="fas fa-user-circle fa-2x d-block mb-2"></i>
                            My Profile
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../events.php" class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fas fa-calendar-alt fa-2x d-block mb-2"></i>
                            All Events
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>