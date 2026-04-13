<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'church_scheduler');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL - Update this according to your setup
define('BASE_URL', 'http://localhost/church-scheduler/');

// Define upload path - absolute path for file operations
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

// Define upload URL for web access
define('UPLOAD_URL', BASE_URL . 'uploads/');
?>