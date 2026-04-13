<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

if (!isset($auth)) $auth = new Auth($conn);
$current_user = null;
if ($auth->isLoggedIn()) {
    $current_user = $auth->getCurrentUser();
    $unread_notifications = getUnreadNotifications($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Church Event Scheduler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <i class="fas fa-church me-2"></i>Smart Church Scheduler
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>calendar.php"><i class="fas fa-calendar-alt me-1"></i> Calendar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>upcoming-events.php"><i class="fas fa-list-ul me-1"></i> Upcoming</a>
                    </li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php if ($auth->isAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-tachometer-alt"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/events.php">Manage Events</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/members.php">Members</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/users.php">Users</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="memberDropdown" role="button" data-bs-toggle="dropdown">
                                <?php
                                // Build absolute URL for profile image
                                $profile_img = BASE_URL . 'uploads/profiles/default-avatar.png';
                                if (isset($current_user['profile_image']) && !empty($current_user['profile_image'])) {
                                    $full_path = __DIR__ . '/../' . $current_user['profile_image'];
                                    if (file_exists($full_path)) {
                                        $profile_img = BASE_URL . $current_user['profile_image'];
                                    }
                                }
                                ?>
                                <img src="<?php echo $profile_img; ?>" class="rounded-circle me-1" width="30" height="30" style="object-fit: cover;" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>uploads/profiles/default-avatar.png'">
                                <?php echo htmlspecialchars($current_user['full_name']); ?>
                                <?php if (isset($unread_notifications) && $unread_notifications && $unread_notifications->num_rows > 0): ?>
                                    <span class="badge bg-danger"><?php echo $unread_notifications->num_rows; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>member/dashboard.php">My Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>member/create-event.php">Create Event</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>member/my-events.php">My Events</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>member/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mt-4">