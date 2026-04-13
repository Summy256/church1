<?php
require_once 'includes/header.php';

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'approved';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Build query for public events (only show approved events)
$sql = "SELECT e.*, u.full_name as creator_name, 
        (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_count 
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.status = 'approved'";

// Add search filter
if (!empty($search)) {
    $sql .= " AND (e.title LIKE '%$search%' OR e.description LIKE '%$search%' OR e.location LIKE '%$search%')";
}

// Add category/type filter
if (!empty($category)) {
    $sql .= " AND e.category = '$category'";
}

$sql .= " ORDER BY e.event_date ASC, e.start_time ASC";

$events = $conn->query($sql);

// Get unique event categories/types for filter
$categories_sql = "SELECT DISTINCT category FROM events WHERE category IS NOT NULL AND category != '' AND status = 'approved'";
$categories = $conn->query($categories_sql);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Repository</h2>
        
        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="events.php" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Events</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by title, description, or location..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php if ($categories && $categories->num_rows > 0): ?>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Events Grid -->
        <div class="row">
            <?php if ($events && $events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card event-card h-100">
                            <?php if (isset($event['image']) && !empty($event['image']) && file_exists($event['image'])): ?>
                                <img src="<?php echo $event['image']; ?>" class="card-img-top event-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <?php else: ?>
                                <img src="assets/images/default-event.jpg" class="card-img-top event-image" alt="Default Event Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <?php if (isset($event['category']) && !empty($event['category'])): ?>
                                    <span class="badge bg-info mb-2"><?php echo htmlspecialchars($event['category']); ?></span>
                                <?php endif; ?>
                                <p class="card-text text-muted">
                                    <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($event['event_date'])); ?><br>
                                    <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?><br>
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?><br>
                                    <i class="fas fa-users"></i> <?php echo $event['registered_count']; ?> registered
                                </p>
                                <p class="card-text"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                            <?php if ($event['event_date'] == date('Y-m-d')): ?>
                                <div class="card-footer bg-warning text-dark">
                                    <small><i class="fas fa-calendar-day"></i> Happening Today!</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h4>No events found</h4>
                        <p>There are currently no upcoming events. Please check back later!</p>
                        <?php if (!$auth->isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-primary mt-2">Register to Create Events</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>