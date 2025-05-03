<?php
require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is an admin (you'll need to implement admin authentication)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['tenant_id']) || !isset($data['bill_type']) || !isset($data['amount']) || !isset($data['due_date'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    // Validate bill type
    $valid_bill_types = ['Rent', 'Water', 'Electricity', 'Maintenance', 'Other'];
    if (!in_array($data['bill_type'], $valid_bill_types)) {
        throw new Exception('Invalid bill type');
    }

    // Validate amount
    if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
        throw new Exception('Invalid amount');
    }

    // Validate due date
    $due_date = date('Y-m-d', strtotime($data['due_date']));
    if ($due_date === false) {
        throw new Exception('Invalid due date');
    }

    // Check if tenant exists
    $stmt = $conn->prepare("SELECT tenantID FROM tenants WHERE tenantID = ?");
    $stmt->bind_param("i", $data['tenant_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Tenant not found');
    }

    // Insert new bill
    $stmt = $conn->prepare("INSERT INTO bills (tenant_id, bill_type, amount, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $data['tenant_id'], $data['bill_type'], $data['amount'], $due_date);

    if (!$stmt->execute()) {
        throw new Exception("Error adding bill: " . $stmt->error);
    }

    $bill_id = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Bill added successfully',
        'data' => [
            'bill_id' => $bill_id
        ]
    ]);

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 