<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if PDF export is requested (Dompdf 0.6.2)
if (isset($_GET['pdf'])) {
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');
    
    // Fetch data for PDF
    $sql = "SELECT e.id, e.title, e.event_date, e.start_time, e.end_time, e.location, e.venue, e.status, 
                   u.full_name as organizer, 
                   (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registrations
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.event_date BETWEEN '$from_date' AND '$to_date'
            ORDER BY e.event_date DESC, e.start_time ASC";
    $result = $conn->query($sql);
    
    $stats_sql = "SELECT 
                    COUNT(*) as total_events,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
                    SUM(CASE WHEN event_date < CURDATE() THEN 1 ELSE 0 END) as past_events,
                    SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_events,
                    SUM((SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id)) as total_registrations
                  FROM events e
                  WHERE event_date BETWEEN '$from_date' AND '$to_date'";
    $stats = $conn->query($stats_sql)->fetch_assoc();
    
    // Build HTML content for PDF
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Event Report</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 20px; }
            h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
            .report-header { text-align: center; margin-bottom: 20px; }
            .report-header h1 { color: #2c3e50; margin: 0; }
            .report-header p { color: #7f8c8d; margin: 5px 0; }
            .summary-cards { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
            .summary-cards td { border: 1px solid #ddd; padding: 10px; text-align: center; background-color: #f8f9fa; }
            .card-value { font-size: 24px; font-weight: bold; color: #3498db; }
            .card-label { font-size: 12px; color: #6c757d; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #2c3e50; color: white; }
            .status-approved { color: #28a745; font-weight: bold; }
            .status-pending { color: #ffc107; font-weight: bold; }
            .status-cancelled { color: #dc3545; font-weight: bold; }
            .total-row { background-color: #f8f9fa; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #7f8c8d; border-top: 1px solid #ddd; padding-top: 10px; }
        </style>
    </head>
    <body>
        <div class="report-header">
            <h1>Smart Church Event Scheduler</h1>
            <p>Event Report | From <?php echo date('M j, Y', strtotime($from_date)); ?> to <?php echo date('M j, Y', strtotime($to_date)); ?></p>
            <p>Generated on <?php echo date('F j, Y g:i A'); ?></p>
        </div>

        <table class="summary-cards">
            <tr>
                <td><div class="card-value"><?php echo $stats['total_events']; ?></div><div class="card-label">Total Events</div></td>
                <td><div class="card-value"><?php echo $stats['approved_events']; ?></div><div class="card-label">Approved</div></td>
                <td><div class="card-value"><?php echo $stats['pending_events']; ?></div><div class="card-label">Pending</div></td>
                <td><div class="card-value"><?php echo $stats['upcoming_events']; ?></div><div class="card-label">Upcoming</div></td>
            </tr>
        </table>

        <h2>Event Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Organizer</th>
                    <th>Registrations</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_reg = 0;
                while ($row = $result->fetch_assoc()):
                    $total_reg += $row['registrations'];
                    $status_class = '';
                    if ($row['status'] == 'approved') $status_class = 'status-approved';
                    elseif ($row['status'] == 'pending') $status_class = 'status-pending';
                    elseif ($row['status'] == 'cancelled') $status_class = 'status-cancelled';
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['event_date'])); ?></td>
                    <td><?php echo date('g:i A', strtotime($row['start_time'])); ?> - <?php echo date('g:i A', strtotime($row['end_time'])); ?></td>
                    <td><?php echo htmlspecialchars($row['location'] . ($row['venue'] ? ' - ' . $row['venue'] : '')); ?></td>
                    <td class="<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['organizer']); ?></td>
                    <td><?php echo $row['registrations']; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="7" style="text-align: right;"><strong>Total Registrations:</strong></td>
                    <td><strong><?php echo $total_reg; ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Smart Church Scheduler &copy; <?php echo date('Y'); ?> - All rights reserved.</p>
        </div>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    
    // Dompdf 0.6.2 specific code
    require_once '../dompdf/dompdf_config.inc.php';
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->set_paper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("events_report_{$from_date}_to_{$to_date}.pdf");
    exit;
}

// Check if CSV export is requested
if (isset($_GET['export'])) {
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');
    
    $sql = "SELECT e.id, e.title, e.event_date, e.start_time, e.end_time, e.location, e.venue, e.status, 
                   u.full_name as organizer, 
                   (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registrations
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.event_date BETWEEN '$from_date' AND '$to_date'
            ORDER BY e.event_date DESC, e.start_time ASC";
    $result = $conn->query($sql);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="events_report_'.$from_date.'_to_'.$to_date.'.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, array('ID', 'Title', 'Date', 'Start Time', 'End Time', 'Location', 'Venue', 'Status', 'Organizer', 'Registrations'));
    $total_registrations = 0;
    while ($row = $result->fetch_assoc()) {
        $total_registrations += $row['registrations'];
        fputcsv($output, array(
            $row['id'],
            $row['title'],
            date('Y-m-d', strtotime($row['event_date'])),
            date('g:i A', strtotime($row['start_time'])),
            date('g:i A', strtotime($row['end_time'])),
            $row['location'],
            $row['venue'],
            $row['status'],
            $row['organizer'],
            $row['registrations']
        ));
    }
    fputcsv($output, array('', '', '', '', '', '', '', 'TOTAL:', $total_registrations, ''));
    fclose($output);
    exit;
}

// If not exporting, include header and proceed normally
require_once '../includes/header.php';
if (!$auth->isAdmin()) { header('Location: ../index.php'); exit; }

// Set default dates if not provided
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

// Summary statistics
$stats_sql = "SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
                SUM(CASE WHEN event_date < CURDATE() THEN 1 ELSE 0 END) as past_events,
                SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_events,
                SUM((SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id)) as total_registrations
              FROM events e
              WHERE event_date BETWEEN '$from_date' AND '$to_date'";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Events list for on-screen display
$events_sql = "SELECT e.id, e.title, e.event_date, e.start_time, e.end_time, e.location, e.venue, e.status, 
                      u.full_name as organizer, 
                      (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registrations
               FROM events e
               LEFT JOIN users u ON e.created_by = u.id
               WHERE e.event_date BETWEEN '$from_date' AND '$to_date'
               ORDER BY e.event_date DESC, e.start_time ASC";
$events = $conn->query($events_sql);
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Event Reports</h4>
            </div>
            <div class="card-body">
                <!-- Filter Form with Clear Button -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?php echo $from_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?php echo $to_date; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="reports.php" class="btn btn-secondary w-100">Clear</a>
                    </div>
                </form>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="dashboard-widget text-center bg-info">
                            <h3><?php echo $stats['total_events']; ?></h3>
                            <p>Total Events</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-widget text-center bg-success">
                            <h3><?php echo $stats['approved_events']; ?></h3>
                            <p>Approved</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-widget text-center bg-warning">
                            <h3><?php echo $stats['pending_events']; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dashboard-widget text-center bg-secondary">
                            <h3><?php echo $stats['upcoming_events']; ?></h3>
                            <p>Upcoming</p>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="mb-3 text-end">
                    <a href="?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&export=1" class="btn btn-success me-2">
                        <i class="fas fa-file-csv me-2"></i> Export CSV
                    </a>
                    <a href="?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&pdf=1" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i> Export PDF
                    </a>
                </div>

                <!-- Events Table (on-screen) -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Organizer</th>
                                <th>Registrations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($events->num_rows > 0): ?>
                                <?php while ($row = $events->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($row['event_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($row['start_time'])); ?> - <?php echo date('g:i A', strtotime($row['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['location'] . ($row['venue'] ? ' - ' . $row['venue'] : '')); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status']=='approved'?'success':($row['status']=='pending'?'warning':'danger'); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['organizer']); ?></td>
                                        <td><?php echo $row['registrations']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-info fw-bold">
                                    <td colspan="7" class="text-end">TOTAL REGISTRATIONS:</td>
                                    <td><?php echo $stats['total_registrations']; ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No events found for this period.<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-widget {
        color: white;
        padding: 15px;
        border-radius: 10px;
    }
    .bg-info { background: linear-gradient(135deg, #17a2b8, #138496); }
    .bg-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
    .bg-secondary { background: linear-gradient(135deg, #6c757d, #5a6268); }
    .table td, .table th { vertical-align: middle; }
</style>

<?php require_once '../includes/footer.php'; ?>