<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($input === false) {
    die(json_encode(['success' => false, 'message' => 'Failed to read input data']));
}

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()]));
}

if (!isset($data['username']) || !isset($data['password'])) {
    die(json_encode(['success' => false, 'message' => 'Email and password are required']));
}

$email = sanitizeInput($data['username']);
$password = $data['password'];

// Query tenants table with all necessary information
$stmt = $conn->prepare("SELECT 
    t.tenantID,
    t.tenant_name,
    t.email,
    t.ID_number,
    t.profession,
    t.phone_number,
    t.dateAdmitted,
    t.account,
    t.houseNumber,
    t.password,
    h.house_name,
    h.rent_amount,
    h.house_status
    FROM tenants t
    LEFT JOIN houses h ON t.houseNumber = h.houseID
    WHERE t.email = ?");

if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]));
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    die(json_encode(['success' => false, 'message' => 'Query error: ' . $stmt->error]));
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid email or password']));
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid email or password']));
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing session data
$_SESSION = array();

// Set session variables with tenant information
$_SESSION['tenant_id'] = $user['tenantID'];
$_SESSION['tenant_name'] = $user['tenant_name'];
$_SESSION['tenant_email'] = $user['email'];
$_SESSION['tenant_phone'] = $user['phone_number'];
$_SESSION['tenant_profession'] = $user['profession'];
$_SESSION['tenant_house_id'] = $user['houseNumber'];
$_SESSION['tenant_house_name'] = $user['house_name'];
$_SESSION['tenant_rent'] = $user['rent_amount'];
$_SESSION['tenant_balance'] = $user['account'];
$_SESSION['tenant_admission_date'] = $user['dateAdmitted'];
$_SESSION['logged_in'] = true;

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'tenantID' => $user['tenantID'],
        'name' => $user['tenant_name'],
        'email' => $user['email'],
        'phone' => $user['phone_number'],
        'profession' => $user['profession'],
        'house_id' => $user['houseNumber'],
        'house_name' => $user['house_name'],
        'rent_amount' => $user['rent_amount'],
        'account_balance' => $user['account'],
        'admission_date' => $user['dateAdmitted'],
        'house_status' => $user['house_status']
    ]
]);
?> 