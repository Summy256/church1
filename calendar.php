<?php
require_once 'includes/header.php';

// Function to calculate Easter Sunday for a given year (Gregorian)
function getGregorianEaster($year) {
    // Anonymous Gregorian algorithm
    $a = $year % 19;
    $b = floor($year / 100);
    $c = $year % 100;
    $d = floor($b / 4);
    $e = $b % 4;
    $f = floor(($b + 8) / 25);
    $g = floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = floor(($a + 11 * $h + 22 * $l) / 451);
    $month = floor(($h + $l - 7 * $m + 114) / 31);
    $day = (($h + $l - 7 * $m + 114) % 31) + 1;
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}

$current_year = date('Y');
$next_year = $current_year + 1;

$special_dates = [];

// Try to get Easter from database first (manual override)
$easter_db = $conn->query("SELECT event_date FROM church_special_dates WHERE title = 'Easter Sunday' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1");
if ($easter_db && $easter_db->num_rows > 0) {
    $row = $easter_db->fetch_assoc();
    $easter_current = $row['event_date'];
    // Also get next year's Easter if needed
    $next_year_easter = getGregorianEaster($next_year);
} else {
    $easter_current = getGregorianEaster($current_year);
    $next_year_easter = getGregorianEaster($next_year);
}

// Add Easter for current and next year (if not already in DB)
$special_dates[] = ['title' => 'Easter Sunday', 'date' => $easter_current, 'description' => 'Resurrection of Jesus Christ'];
if (!isset($next_year_easter)) $next_year_easter = getGregorianEaster($next_year);
$special_dates[] = ['title' => 'Easter Sunday', 'date' => $next_year_easter, 'description' => 'Resurrection of Jesus Christ'];

// Good Friday (2 days before Easter)
$special_dates[] = ['title' => 'Good Friday', 'date' => date('Y-m-d', strtotime($easter_current . ' -2 days')), 'description' => 'Commemoration of the crucifixion'];
$special_dates[] = ['title' => 'Good Friday', 'date' => date('Y-m-d', strtotime($next_year_easter . ' -2 days')), 'description' => 'Commemoration of the crucifixion'];

// Ascension Day (39 days after Easter)
$special_dates[] = ['title' => 'Ascension Day', 'date' => date('Y-m-d', strtotime($easter_current . ' +39 days')), 'description' => 'Ascension of Jesus into heaven'];
$special_dates[] = ['title' => 'Ascension Day', 'date' => date('Y-m-d', strtotime($next_year_easter . ' +39 days')), 'description' => 'Ascension of Jesus into heaven'];

// Pentecost (49 days after Easter)
$special_dates[] = ['title' => 'Pentecost', 'date' => date('Y-m-d', strtotime($easter_current . ' +49 days')), 'description' => 'Descent of the Holy Spirit'];
$special_dates[] = ['title' => 'Pentecost', 'date' => date('Y-m-d', strtotime($next_year_easter . ' +49 days')), 'description' => 'Descent of the Holy Spirit'];

// Fixed annual dates from database (e.g., Christmas, New Year)
$fixed_sql = "SELECT title, event_date, description FROM church_special_dates WHERE is_recurring = 1";
$fixed_result = $conn->query($fixed_sql);
if ($fixed_result && $fixed_result->num_rows > 0) {
    while ($row = $fixed_result->fetch_assoc()) {
        $date_parts = explode('-', $row['event_date']);
        if (count($date_parts) == 3) {
            $month_day = $date_parts[1] . '-' . $date_parts[2];
            $current_date = $current_year . '-' . $month_day;
            $next_date = $next_year . '-' . $month_day;
            $special_dates[] = ['title' => $row['title'], 'date' => $current_date, 'description' => $row['description']];
            $special_dates[] = ['title' => $row['title'], 'date' => $next_date, 'description' => $row['description']];
        }
    }
}

// Non-recurring special dates from database (e.g., Easter 2026 override)
$manual_sql = "SELECT title, event_date, description FROM church_special_dates WHERE is_recurring = 0 AND event_date >= CURDATE()";
$manual_result = $conn->query($manual_sql);
if ($manual_result && $manual_result->num_rows > 0) {
    while ($row = $manual_result->fetch_assoc()) {
        // Avoid duplicate Easter if already added
        if ($row['title'] == 'Easter Sunday' && $row['event_date'] == $easter_current) continue;
        $special_dates[] = ['title' => $row['title'], 'date' => $row['event_date'], 'description' => $row['description']];
    }
}

// Get approved regular events
$sql = "SELECT id, title, description, event_date, start_time, end_time, location 
        FROM events 
        WHERE status = 'approved' AND event_date >= CURDATE()
        ORDER BY event_date ASC";
$events_result = $conn->query($sql);

$calendar_events = [];

// Add regular events
while ($row = $events_result->fetch_assoc()) {
    $start_datetime = $row['event_date'] . 'T' . date('H:i:s', strtotime($row['start_time']));
    $end_datetime = $row['event_date'] . 'T' . date('H:i:s', strtotime($row['end_time']));
    $calendar_events[] = [
        'id'          => $row['id'],
        'title'       => htmlspecialchars($row['title']),
        'start'       => $start_datetime,
        'end'         => $end_datetime,
        'url'         => BASE_URL . 'event-details.php?id=' . $row['id'],
        'description' => htmlspecialchars(substr($row['description'], 0, 150)),
        'type'        => 'event'
    ];
}

// Add special dates
foreach ($special_dates as $sd) {
    if ($sd['date'] >= date('Y-m-d')) {
        $calendar_events[] = [
            'id'          => null,
            'title'       => htmlspecialchars($sd['title']),
            'start'       => $sd['date'] . 'T00:00:00',
            'end'         => $sd['date'] . 'T23:59:59',
            'url'         => null,
            'description' => htmlspecialchars($sd['description']),
            'type'        => 'special'
        ];
    }
}

// Sort events by date
usort($calendar_events, function($a, $b) {
    return strcmp($a['start'], $b['start']);
});

// Prepare near events alert
$today = date('Y-m-d');
$near_events = [];
foreach ($calendar_events as $ev) {
    $event_date = substr($ev['start'], 0, 10);
    $diff = (strtotime($event_date) - strtotime($today)) / (60 * 60 * 24);
    if ($diff >= 0 && $diff <= 3) {
        $near_events[] = [
            'title' => $ev['title'],
            'date' => date('l, F j', strtotime($event_date)),
            'diff' => $diff,
            'url' => $ev['url'] ?: '#',
            'is_special' => ($ev['type'] == 'special')
        ];
    }
}
?>

<!-- Same HTML and JavaScript as before (unchanged except for event data) -->
<div class="row">
    <div class="col-12">
        <?php if (!empty($near_events)): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4 text-center" style="background: linear-gradient(135deg, #fff3cd, #ffeeba); border-left: 5px solid #daa520; max-width: 700px; margin-left: auto; margin-right: auto;">
                <strong><i class="fas fa-bell me-2"></i> Upcoming Events!</strong>
                <ul class="mb-0 mt-2 text-start d-inline-block">
                    <?php foreach ($near_events as $ne): ?>
                        <li>
                            <?php if ($ne['diff'] == 0): ?>
                                <strong>🔥 TODAY:</strong>
                            <?php elseif ($ne['diff'] == 1): ?>
                                <strong>⭐ TOMORROW:</strong>
                            <?php else: ?>
                                <strong>📅 In <?php echo round($ne['diff']); ?> days:</strong>
                            <?php endif; ?>
                            <?php if ($ne['url'] != '#'): ?>
                                <a href="<?php echo $ne['url']; ?>" class="alert-link"><?php echo htmlspecialchars($ne['title']); ?></a>
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($ne['title']); ?></strong>
                            <?php endif; ?>
                            (<?php echo $ne['date']; ?>)
                            <?php if ($ne['is_special']): ?>
                                <span class="badge bg-warning text-dark ms-1">Church Date</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-4 text-center" style="background: #e9ecef; border-left: 5px solid #6c757d; max-width: 600px; margin-left: auto; margin-right: auto;">
                <i class="fas fa-info-circle me-2"></i> No upcoming events in the next 3 days. Check back soon!
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-dark text-white rounded-top-4">
                <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Church Events Calendar</h4>
                <p class="mb-0 small text-white-50">Regular events in grey, church dates in gold</p>
            </div>
            <div class="card-body p-3 p-md-4">
                <div id="calendar" style="min-height: 600px;"></div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: <?php echo json_encode($calendar_events); ?>,
        eventClick: function(info) {
            if (info.event.url) {
                window.open(info.event.url, '_self');
                info.jsEvent.preventDefault();
            } else {
                alert(info.event.title + '\n' + (info.event.extendedProps.description || 'No description available.'));
            }
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: 'short' },
        height: 'auto',
        firstDay: 1,
        locale: 'en',
        buttonText: { today: 'Today', month: 'Month' },
        eventDidMount: function(info) {
            if (info.event.extendedProps.description) {
                info.el.setAttribute('title', info.event.extendedProps.description);
            }
            if (info.event.extendedProps.type === 'special') {
                info.el.style.backgroundColor = '#daa520';
                info.el.style.borderColor = '#b8860b';
                info.el.style.fontWeight = 'bold';
            }
            var today = new Date().toISOString().slice(0,10);
            if (info.event.startStr.slice(0,10) === today && info.event.extendedProps.type !== 'special') {
                info.el.style.backgroundColor = '#daa520';
                info.el.style.borderColor = '#b8860b';
            }
        }
    });
    calendar.render();
});
</script>

<style>
    .fc { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .fc .fc-toolbar-title { font-size: 1.4rem; font-weight: 600; color: #2c2f33; }
    .fc .fc-button-primary { background-color: #5a626e; border-color: #4a4e54; color: white; }
    .fc .fc-button-primary:hover { background-color: #3b3f46; border-color: #2c2f33; }
    .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:focus { background-color: #2c2f33; border-color: #2c2f33; }
    .fc-day-today { background-color: rgba(90, 98, 110, 0.1) !important; }
    .fc-daygrid-day-number { color: #2c2f33; font-weight: 500; }
    .fc-event { cursor: pointer; border-radius: 6px; font-size: 0.85rem; padding: 2px 5px; transition: all 0.2s; }
    .fc-event:hover { transform: scale(1.02); }
    .fc-event:not([style*="background-color: rgb(218, 165, 32)"]) { background-color: #5a626e; border-color: #3b3f46; }
    @media (max-width: 768px) {
        .fc .fc-toolbar { flex-direction: column; gap: 10px; }
        .fc-toolbar-chunk { display: flex; justify-content: center; }
    }
    .alert-warning, .alert-info { border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; }
    .alert-warning ul { display: inline-block; text-align: left; margin-bottom: 0; }
    .alert-warning li { margin-bottom: 5px; }
    .alert-warning a, .alert-info a { text-decoration: none; font-weight: 500; }
    .alert-warning a:hover, .alert-info a:hover { text-decoration: underline; }
</style>

<?php require_once 'includes/footer.php'; ?>