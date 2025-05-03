<?php
require_once 'config.php';

// Check if user is logged in
$user_id = checkSession();

if (!isset($_POST['bill_id']) || !isset($_FILES['screenshot'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

$bill_id = sanitizeInput($_POST['bill_id']);
$file = $_FILES['screenshot'];

// Verify bill belongs to user
$stmt = $conn->prepare("SELECT id FROM bills WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $bill_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid bill ID']));
}

// Validate file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    die(json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed']));
}

if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
    die(json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']));
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/payments';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('payment_') . '.' . $extension;
$filepath = $upload_dir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    die(json_encode(['success' => false, 'message' => 'Failed to upload file']));
}

// Save file path to database
$relative_path = 'uploads/payments/' . $filename;
$stmt = $conn->prepare("INSERT INTO payment_screenshots (bill_id, file_path) VALUES (?, ?)");
$stmt->bind_param("is", $bill_id, $relative_path);

if (!$stmt->execute()) {
    // Delete uploaded file if database insert fails
    unlink($filepath);
    die(json_encode(['success' => false, 'message' => 'Failed to save file information']));
}

// Update bill status
$stmt = $conn->prepare("UPDATE bills SET status = 'paid' WHERE id = ?");
$stmt->bind_param("i", $bill_id);
$stmt->execute();

echo json_encode([
    'success' => true,
    'message' => 'Payment screenshot uploaded successfully',
    'file_path' => $relative_path
]);
?> 