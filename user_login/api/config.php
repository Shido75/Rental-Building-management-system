<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Company');

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}

// Set headers for JSON response
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to validate session
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Unauthorized']));
    }
    return $_SESSION['user_id'];
}

// Function to sanitize input
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}
?> 