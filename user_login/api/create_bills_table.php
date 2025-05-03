<?php
require_once 'config.php';

try {
    // Select the company database
    if (!$conn->select_db('company')) {
        throw new Exception("Could not select company database");
    }

    // Read the SQL file
    $sql = file_get_contents('create_bills_table.sql');
    
    // Execute the SQL
    if ($conn->multi_query($sql)) {
        do {
            // Store or discard the result
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo json_encode([
            'success' => true,
            'message' => 'Bills table created successfully'
        ]);
    } else {
        throw new Exception("Error creating bills table: " . $conn->error);
    }
} catch (Exception $e) {
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}
?> 