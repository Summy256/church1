<?php
require_once '../includes/header.php';
if (!$auth->isAdmin()) { header('Location: ../index.php'); exit; }

$stats = getEventStats();
$pending_events = getEvents('pending');
$recent_events = getEvents('approved', 5);
$members = getAllMembers();

// --- Data for Charts ---
// Events per month (last 6 months)
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count FROM events WHERE DATE_FORMAT(event_date, '%Y-%m') = '$month'";
    $result = $conn->query($sql);
    $count = $result->fetch_assoc()['count'];
    $monthly_data[] = ['month' => $month_name, 'count' => $count];
}
$months = json_encode(array_column($monthly_data, 'month'));
$month_counts = json_encode(array_column($monthly_data, 'count'));

// Event status counts
$status_sql = "SELECT 
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
              FROM events";
$status_result = $conn->query($status_sql);
$status_data = $status_result->fetch_assoc();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Admin Dashboard</h2>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-widget text-center">
            <i class="fas fa-calendar-alt fa-3x mb-2"></i>
            <h3><?php echo isset($stats['total_events']) ? $stats['total_events'] : 0; ?></h3>
            <p>Total Events</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget text-center" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <i class="fas fa-clock fa-3x mb-2"></i>
            <h3><?php echo isset($stats['pending_events']) ? $stats['pending_events'] : 0; ?></h3>
            <p>Pending Approval</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget text-center" style="background: linear-gradient(135deg, #27ae60, #229954);">
            <i class="fas fa-users fa-3x mb-2"></i>
            <h3><?php echo isset($stats['total_members']) ? $stats['total_members'] : 0; ?></h3>
            <p>Total Members</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget text-center" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <i class="fas fa-user-shield fa-3x mb-2"></i>
            <h3><?php echo isset($stats['total_admins']) ? $stats['total_admins'] : 0; ?></h3>
            <p>Administrators</p>
        </div>
    </div>
</div>

<!-- Prominent Reports Card -->
<div class="row mb-4">
    <div class="col-12">
        <a href="reports.php" class="text-decoration-none">
            <div class="card bg-gradient-reports text-white border-0 shadow-lg" style="background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb4d); transition: transform 0.3s;">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-chart-line fa-4x mb-3"></i>
                    <h2 class="card-title mb-2">Generate Event Reports</h2>
                    <p class="card-text lead">View detailed statistics, filter by date range, and export to CSV or PDF</p>
                    <span class="btn btn-light btn-lg mt-2"><i class="fas fa-arrow-right me-2"></i>Access Reports</span>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Events per Month (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Event Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Pending Events & Recent Members -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Pending Events for Approval</h5>
            </div>
            <div class="card-body">
                <?php if ($pending_events && $pending_events->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($event = $pending_events->fetch_assoc()): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                            <i class="fas fa-user ms-2"></i> <?php echo htmlspecialchars($event['creator_name']); ?>
                                        </small>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Review</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center my-3">No pending events.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Recent Members</h5>
            </div>
            <div class="card-body">
                <?php if ($members && $members->num_rows > 0): ?>
                    <div class="list-group">
                        <?php 
                        $cnt = 0;
                        while ($member = $members->fetch_assoc()): 
                            if ($cnt >= 5) break;
                            $cnt++;
                            // Build absolute URL for profile image with proper fallback
                            $profile_img_url = BASE_URL . 'uploads/profiles/default-avatar.png';
                            if (!empty($member['profile_image']) && $member['profile_image'] != 'default-avatar.png') {
                                $full_path = dirname(__DIR__) . '/' . $member['profile_image'];
                                if (file_exists($full_path)) {
                                    $profile_img_url = BASE_URL . $member['profile_image'];
                                }
                            }
                        ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $profile_img_url; ?>" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>uploads/profiles/default-avatar.png'">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($member['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($member['email']); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center my-3">No members registered yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly bar chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo $months; ?>,
            datasets: [{
                label: 'Number of Events',
                data: <?php echo $month_counts; ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: { beginAtZero: true, stepSize: 1, title: { display: true, text: 'Events Count' } },
                x: { title: { display: true, text: 'Month' } }
            }
        }
    });

    // Status pie chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Approved', 'Pending', 'Cancelled'],
            datasets: [{
                data: [<?php echo $status_data['approved']; ?>, <?php echo $status_data['pending']; ?>, <?php echo $status_data['cancelled']; ?>],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: function(context) { return context.label + ': ' + context.raw + ' events'; } } }
            }
        }
    });
});
</script>

<style>
    .dashboard-widget {
        color: white;
        padding: 15px;
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-widget:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .bg-gradient-reports {
        background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb4d);
    }
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        border-bottom: none;
        font-weight: 600;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .btn-light {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
    }
    a.text-decoration-none:hover .bg-gradient-reports {
        transform: translateY(-3px);
        transition: transform 0.3s;
    }
</style>

<?php require_once '../includes/footer.php'; ?>