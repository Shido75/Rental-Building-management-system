<?php
require_once 'config.php';

try {
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    
    if (!$result) {
        throw new Exception("Error getting tables: " . $conn->error);
    }

    $tables = [];
    while ($row = $result->fetch_array()) {
        $table_name = $row[0];
        $table_info = $conn->query("DESCRIBE $table_name");
        
        $columns = [];
        while ($col = $table_info->fetch_assoc()) {
            $columns[] = $col;
        }
        
        $tables[$table_name] = $columns;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'tables' => $tables
        ]
    ]);

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 