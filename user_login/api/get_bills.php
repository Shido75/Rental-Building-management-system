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

$tenant_id = $_SESSION['tenant_id'];

try {
    // Get tenant bills with house information
    $stmt = $conn->prepare("SELECT 
        b.billID,
        b.bill_type,
        b.amount,
        b.due_date,
        b.status,
        b.created_at,
        t.tenant_name,
        t.account as balance,
        h.house_name,
        h.rent_amount
        FROM bills b
        JOIN tenants t ON b.tenant_id = t.tenantID
        JOIN houses h ON t.houseNumber = h.houseID
        WHERE b.tenant_id = ?
        ORDER BY b.due_date DESC");

    if (!$stmt) {
        throw new Exception("Error preparing query: " . $conn->error);
    }

    $stmt->bind_param("i", $tenant_id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $bills = [];

    while ($row = $result->fetch_assoc()) {
        $bills[] = [
            'bill_id' => $row['billID'],
            'bill_type' => $row['bill_type'],
            'amount' => $row['amount'],
            'due_date' => $row['due_date'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'house_name' => $row['house_name'],
            'rent_amount' => $row['rent_amount']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'bills' => $bills,
            'tenant_balance' => $_SESSION['tenant_balance']
        ]
    ]);

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 