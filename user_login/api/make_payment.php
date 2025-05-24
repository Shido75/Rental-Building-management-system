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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount']) || !isset($data['payment_method']) || !isset($data['transaction_id'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    $amount = floatval($data['amount']);
    $payment_method = $data['payment_method'];
    $transaction_id = $data['transaction_id'];
    $payment_date = date('Y-m-d H:i:s');
    $tenant_id = $_SESSION['tenant_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (tenantID, amount, payment_method, transaction_id, payment_date)
                               VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Error preparing payment insert: " . $conn->error);
        }

        $stmt->bind_param("idsss", $tenant_id, $amount, $payment_method, $transaction_id, $payment_date);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting payment: " . $stmt->error);
        }

        // Update tenant account balance
        $stmt = $conn->prepare("UPDATE tenants 
                               SET account = account + ? 
                               WHERE tenantID = ?");

        if (!$stmt) {
            throw new Exception("Error preparing balance update: " . $conn->error);
        }

        $stmt->bind_param("di", $amount, $tenant_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating balance: " . $stmt->error);
        }

        // Get updated balance
        $stmt = $conn->prepare("SELECT account FROM tenants WHERE tenantID = ?");
        if (!$stmt) {
            throw new Exception("Error preparing balance select: " . $conn->error);
        }

        $stmt->bind_param("i", $tenant_id);
        if (!$stmt->execute()) {
            throw new Exception("Error getting updated balance: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $new_balance = $row['account'];

        // Commit transaction
        $conn->commit();

        // Update session balance
        $_SESSION['tenant_balance'] = $new_balance;

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'new_balance' => $new_balance
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 