<?php
require_once '../includes/header.php';

if (!$auth->isAdmin()) {
    header('Location: ../index.php');
    exit;
}

$events = getEvents();

// Handle event approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $event_id = (int)$_POST['event_id'];
        if (approveEvent($event_id)) {
            echo '<div class="alert alert-success">Event approved successfully!</div>';
            $events = getEvents();
        }
    } elseif (isset($_POST['reject'])) {
        $event_id = (int)$_POST['event_id'];
        $stmt = $conn->prepare("UPDATE events SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            echo '<div class="alert alert-warning">Event rejected/cancelled!</div>';
            $events = getEvents();
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Manage All Events</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Organizer</th>
                                <th>Status</th>
                                <th>Registrations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($events && $events->num_rows > 0): ?>
                                <?php while ($event = $events->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $event['id']; ?></td>
                                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($event['start_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($event['creator_name']); ?></td>
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
                                            $count_result = $stmt->get_result();
                                            $count = $count_result->fetch_assoc();
                                            echo isset($count['count']) ? $count['count'] : 0;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            <?php if ($event['status'] == 'pending'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" name="approve" class="btn btn-sm btn-success">Approve</button>
                                                    <button type="submit" name="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject this event?')">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No events found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>