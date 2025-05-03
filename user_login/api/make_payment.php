<?php
require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($input === false || json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['success' => false, 'message' => 'Invalid input data']));
}

if (!isset($data['amount']) || !isset($data['payment_method']) || !isset($data['transaction_id'])) {
    die(json_encode(['success' => false, 'message' => 'Required fields are missing']));
}

$tenant_id = $_SESSION['tenant_id'];
$amount = floatval($data['amount']);
$payment_method = sanitizeInput($data['payment_method']);
$transaction_id = sanitizeInput($data['transaction_id']);
$payment_date = date('Y-m-d H:i:s');

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO payments (tenant_id, amount, payment_method, transaction_id, payment_date) 
                           VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Error preparing payment insert: " . $conn->error);
    }

    $stmt->bind_param("idsss", $tenant_id, $amount, $payment_method, $transaction_id, $payment_date);
    if (!$stmt->execute()) {
        throw new Exception("Error inserting payment: " . $stmt->error);
    }

    // Update tenant account balance
    $stmt = $conn->prepare("UPDATE tenants SET account = account + ? WHERE tenantID = ?");
    if (!$stmt) {
        throw new Exception("Error preparing balance update: " . $conn->error);
    }

    $stmt->bind_param("di", $amount, $tenant_id);
    if (!$stmt->execute()) {
        throw new Exception("Error updating balance: " . $stmt->error);
    }

    // Get updated tenant information
    $stmt = $conn->prepare("SELECT 
        t.tenantID,
        t.tenant_name,
        t.email,
        t.phone_number,
        t.account,
        t.houseNumber,
        h.house_name,
        h.rent_amount
        FROM tenants t
        LEFT JOIN houses h ON t.houseNumber = h.houseID
        WHERE t.tenantID = ?");

    if (!$stmt) {
        throw new Exception("Error preparing tenant select: " . $conn->error);
    }

    $stmt->bind_param("i", $tenant_id);
    if (!$stmt->execute()) {
        throw new Exception("Error fetching tenant info: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();

    // Update session with new balance
    $_SESSION['tenant_balance'] = $tenant['account'];

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'data' => [
            'payment_amount' => $amount,
            'new_balance' => $tenant['account'],
            'payment_date' => $payment_date,
            'transaction_id' => $transaction_id
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 