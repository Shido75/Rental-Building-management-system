<?php
require_once 'config.php';

try {
    // Check if bills table exists
    $result = $conn->query("SHOW TABLES LIKE 'bills'");
    
    if ($result->num_rows === 0) {
        throw new Exception("Bills table does not exist");
    }

    // Get bills table structure
    $result = $conn->query("DESCRIBE bills");
    
    if (!$result) {
        throw new Exception("Error describing bills table: " . $conn->error);
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'columns' => $columns
        ]
    ]);

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 