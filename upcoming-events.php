<?php
require_once 'includes/header.php';

// Get all approved events that are today or in the future
$sql = "SELECT id, title, description, event_date, start_time, end_time, location, venue 
        FROM events 
        WHERE status = 'approved' AND event_date >= CURDATE()
        ORDER BY event_date ASC, start_time ASC";
$result = $conn->query($sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-dark text-white rounded-top-4">
                <h4 class="mb-0"><i class="fas fa-list-ul me-2"></i> Upcoming Events</h4>
                <p class="mb-0 small text-white-50">All future church events – click to see details</p>
            </div>
            <div class="card-body p-4">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($event = $result->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>event-details.php?id=<?php echo $event['id']; ?>" class="list-group-item list-group-item-action mb-3 rounded-3 shadow-sm" style="border-left: 5px solid #daa520;">
                                <div class="d-flex w-100 justify-content-between align-items-center flex-wrap">
                                    <h5 class="mb-1 text-dark"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo date('l, F j, Y', strtotime($event['event_date'])); ?>
                                    </small>
                                </div>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></span>
                                    <span class="badge bg-secondary"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <?php if (!empty($event['venue'])): ?>
                                        <span class="badge bg-secondary"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($event['venue']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-2 mb-1 text-muted"><?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 120))) . (strlen($event['description']) > 120 ? '...' : ''); ?></p>
                                <small class="text-primary">Click to view full details →</small>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                        <h5>No upcoming events at the moment</h5>
                        <p class="mb-0">Check back soon for exciting church activities!</p>
                        <?php if (!$auth->isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-primary mt-3">Register to Create Events</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .list-group-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background-color: #fef9e6;
    }
    .badge {
        font-size: 0.85rem;
        padding: 6px 12px;
        font-weight: normal;
    }
    @media (max-width: 768px) {
        .list-group-item {
            margin-bottom: 15px;
        }
        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .d-flex.w-100 {
            flex-direction: column;
            align-items: flex-start;
        }
        .text-muted {
            margin-top: 5px;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>