<?php
require_once '../includes/header.php';

if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user_events = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY created_at DESC");
$user_events->bind_param("i", $_SESSION['user_id']);
$user_events->execute();
$my_events = $user_events->get_result();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">My Created Events</h4>
            </div>
            <div class="card-body">
                <?php if ($my_events && $my_events->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                32<tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Registrations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($event = $my_events->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($event['start_time'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $event['status'] == 'approved' ? 'success' : 
                                                    ($event['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
                                            $stmt->bind_param("i", $event['id']);
                                            $stmt->execute();
                                            $count = $stmt->get_result()->fetch_assoc()['count'];
                                            echo $count;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You haven't created any events yet.</p>
                    <a href="create-event.php" class="btn btn-primary">Create Your First Event</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>