<?php
require_once 'config.php';

// Check if user is logged in
$user_id = checkSession();

// Get user profile
$stmt = $conn->prepare("SELECT name, email, mobile_number, upi_id, phone_number FROM user_profiles WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get bills for 2025
$bills = [];
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.bill_type,
        b.month,
        b.amount,
        b.status,
        b.due_date,
        ps.file_path as payment_screenshot
    FROM bills b
    LEFT JOIN payment_screenshots ps ON ps.bill_id = b.id
    WHERE b.user_id = ? AND b.year = 2025
    ORDER BY b.month ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $month = $row['month'];
    if (!isset($bills[$month])) {
        $bills[$month] = [
            'month' => date('F', mktime(0, 0, 0, $month, 1)),
            'rent' => null,
            'maintenance' => null
        ];
    }
    
    if ($row['bill_type'] === 'rent') {
        $bills[$month]['rent'] = [
            'id' => $row['id'],
            'amount' => $row['amount'],
            'status' => $row['status'],
            'due_date' => $row['due_date'],
            'payment_screenshot' => $row['payment_screenshot']
        ];
    } else {
        $bills[$month]['maintenance'] = [
            'id' => $row['id'],
            'amount' => $row['amount'],
            'status' => $row['status'],
            'due_date' => $row['due_date'],
            'payment_screenshot' => $row['payment_screenshot']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => [
        'profile' => $profile,
        'bills' => array_values($bills)
    ]
]);
?> 