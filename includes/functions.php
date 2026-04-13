<?php
require_once __DIR__ . '/../config/database.php';

function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}

function uploadFile($file, $type = 'event') {
    $target_dir = UPLOAD_PATH . $type . '/';
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            return array('error' => 'Failed to create upload directory');
        }
    }
    if (!is_writable($target_dir)) {
        return array('error' => 'Upload directory is not writable');
    }
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'mov', 'ogg');
    if (!in_array($file_extension, $allowed_types)) {
        return array('error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, mp4, webm, ogg');
    }
    if ($file['size'] > 100000000) { // 100MB max
        return array('error' => 'File too large. Max 100MB');
    }
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return array('success' => true, 'path' => 'uploads/' . $type . '/' . $new_filename);
    }
    return array('error' => 'Upload failed. Check folder permissions.');
}

function createEvent($data, $user_id) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, venue, capacity, image, video_url, video_file, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $image = isset($data['image']) ? $data['image'] : null;
    $video_url = isset($data['video_url']) ? $data['video_url'] : null;
    $video_file = isset($data['video_file']) ? $data['video_file'] : null;
    $stmt->bind_param("sssssssissis", 
        $data['title'], $data['description'], $data['event_date'], $data['start_time'], $data['end_time'], 
        $data['location'], $data['venue'], $data['capacity'], $image, $video_url, $video_file, $user_id);
    if ($stmt->execute()) {
        $event_id = $conn->insert_id;
        // Notify admins
        $stmt2 = $conn->prepare("SELECT id FROM users WHERE role IN ('owner', 'admin')");
        $stmt2->execute();
        $admins = $stmt2->get_result();
        while ($admin = $admins->fetch_assoc()) {
            addNotification($admin['id'], 'New Event Pending', $data['title'] . ' needs your approval', 'warning');
        }
        return $event_id;
    }
    return false;
}

function addComment($event_id, $user_id, $comment) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $event_id, $user_id, $comment);
    return $stmt->execute();
}

function getComments($event_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT c.*, u.full_name, u.profile_image FROM comments c JOIN users u ON c.user_id = u.id WHERE c.event_id = ? ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    return $stmt->get_result();
}

function registerForEvent($event_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT capacity, (SELECT COUNT(*) FROM event_registrations WHERE event_id = ?) as registered FROM events WHERE id = ?");
    $stmt->bind_param("ii", $event_id, $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    if ($event['capacity'] > 0 && $event['registered'] >= $event['capacity']) {
        return array('error' => 'Event is full');
    }
    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $event_id, $user_id);
    if ($stmt->execute()) {
        addNotification($user_id, 'Registration Successful', 'You have successfully registered for the event', 'success');
        return array('success' => true);
    }
    return array('error' => 'Already registered or registration failed');
}

function addNotification($user_id, $title, $message, $type = 'info') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

function getUnreadNotifications($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function markNotificationRead($notification_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

function getEvents($status = null, $limit = null) {
    global $conn;
    $sql = "SELECT e.*, u.full_name as creator_name FROM events e LEFT JOIN users u ON e.created_by = u.id";
    if ($status) $sql .= " WHERE e.status = '$status'";
    $sql .= " ORDER BY e.event_date DESC, e.start_time DESC";
    if ($limit) $sql .= " LIMIT $limit";
    return $conn->query($sql);
}

function getUpcomingEvents($limit = 5) {
    global $conn;
    $sql = "SELECT e.*, u.full_name as creator_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.event_date >= CURDATE() AND e.status = 'approved' ORDER BY e.event_date ASC, e.start_time ASC LIMIT $limit";
    return $conn->query($sql);
}

function approveEvent($event_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        $stmt2 = $conn->prepare("SELECT created_by, title FROM events WHERE id = ?");
        $stmt2->bind_param("i", $event_id);
        $stmt2->execute();
        $event = $stmt2->get_result()->fetch_assoc();
        addNotification($event['created_by'], 'Event Approved', 'Your event "' . $event['title'] . '" has been approved', 'success');
        return true;
    }
    return false;
}

function isEventCreator($event_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM events WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getEventById($event_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT e.*, u.full_name as creator_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function isUserRegistered($event_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getEventAttendees($event_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT u.id, u.full_name, u.email, u.phone, er.registered_at FROM event_registrations er JOIN users u ON er.user_id = u.id WHERE er.event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getAllMembers() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, full_name, profile_image, phone, status, created_at FROM users WHERE role = 'member'");
    $stmt->execute();
    return $stmt->get_result();
}

function getAllUsers() {
    global $conn;
    return $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}

function updateUserRole($user_id, $role) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    return $stmt->execute();
}

function deactivateUser($user_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function activateUser($user_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function getEventStats() {
    global $conn;
    $stats = array();
    $result = $conn->query("SELECT COUNT(*) as total FROM events"); $stats['total_events'] = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM events WHERE status = 'approved'"); $stats['approved_events'] = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM events WHERE status = 'pending'"); $stats['pending_events'] = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'"); $stats['total_members'] = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role IN ('owner', 'admin')"); $stats['total_admins'] = $result->fetch_assoc()['total'];
    return $stats;
}

// ==================== CORRECTED getEventImage (ABSOLUTE URL) ====================
function getEventImage($image_path) {
    if (empty($image_path)) {
        return BASE_URL . 'assets/images/default-event.jpg';
    }
    $full_path = dirname(__DIR__) . '/' . $image_path;
    if (file_exists($full_path)) {
        return BASE_URL . $image_path;
    }
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    return BASE_URL . 'assets/images/default-event.jpg';
}

function getVideoEmbedUrl($url) {
    if (empty($url)) return '';
    if (strpos($url, 'youtube.com/watch') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $video_id = isset($params['v']) ? $params['v'] : '';
        if ($video_id) return 'https://www.youtube.com/embed/' . $video_id;
    } elseif (strpos($url, 'youtu.be') !== false) {
        $video_id = substr($url, strrpos($url, '/') + 1);
        if ($video_id) return 'https://www.youtube.com/embed/' . $video_id;
    } elseif (strpos($url, 'vimeo.com') !== false) {
        $video_id = substr($url, strrpos($url, '/') + 1);
        if ($video_id) return 'https://player.vimeo.com/video/' . $video_id;
    }
    return $url;
}

function getProfileImage($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && !empty($user['profile_image']) && file_exists(dirname(__DIR__) . '/' . $user['profile_image'])) {
        return $user['profile_image'];
    }
    return 'uploads/profiles/default-avatar.png';
}

function notifyAdmins($title, $message) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE role IN ('owner', 'admin')");
    $stmt->execute();
    $admins = $stmt->get_result();
    while ($admin = $admins->fetch_assoc()) {
        addNotification($admin['id'], $title, $message, 'info');
    }
}

function addGuestComment($event_id, $name, $email, $comment) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO public_comments (event_id, name, email, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $event_id, $name, $email, $comment);
    return $stmt->execute();
}

function registerGuest($event_id, $name, $email, $phone) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO event_guests (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $event_id, $name, $email, $phone);
    return $stmt->execute();
}

function getAllAttendees($event_id) {
    global $conn;
    $members = getEventAttendees($event_id);
    $guests = $conn->query("SELECT name, email, phone, registered_at FROM event_guests WHERE event_id = $event_id");
    if (!$guests) {
        $guests = new stdClass();
        $guests->num_rows = 0;
    }
    return ['members' => $members, 'guests' => $guests];
}

function getAllComments($event_id) {
    global $conn;
    $member_comments = getComments($event_id);
    $guest_comments = $conn->query("SELECT name, email, comment, created_at FROM public_comments WHERE event_id = $event_id ORDER BY created_at DESC");
    if (!$guest_comments) {
        $guest_comments = new stdClass();
        $guest_comments->num_rows = 0;
    }
    return ['members' => $member_comments, 'guests' => $guest_comments];
}

// ==================== CORRECTED CONFLICT DETECTION ====================
function hasEventConflict($event_date, $start_time = null, $end_time = null, $location = null, $venue = null, $exclude_event_id = null) {
    global $conn;
    $sql = "SELECT id, title, start_time, end_time, location, venue 
            FROM events 
            WHERE status IN ('approved', 'pending')
            AND event_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $event_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $conflict = $result->fetch_assoc();
        $start_time_formatted = date('g:i A', strtotime($conflict['start_time']));
        $end_time_formatted = date('g:i A', strtotime($conflict['end_time']));
        $location_str = $conflict['location'];
        if (!empty($conflict['venue'])) {
            $location_str .= " - " . $conflict['venue'];
        }
        return array(
            'conflict' => true,
            'title' => $conflict['title'],
            'start_time' => $start_time_formatted,
            'end_time' => $end_time_formatted,
            'location' => $location_str
        );
    }
    return array('conflict' => false);
}
?>