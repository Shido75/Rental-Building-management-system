<?php
require_once 'config.php';

try {
    // Check if bills table exists
    $result = $conn->query("SHOW TABLES LIKE 'bills'");
    
    if ($result->num_rows === 0) {
        // Create bills table if it doesn't exist
        $sql = "CREATE TABLE `bills` (
            `billID` INT PRIMARY KEY AUTO_INCREMENT,
            `tenantID` INT NOT NULL,
            `bill_type` ENUM('Rent', 'Water', 'Electricity', 'Maintenance', 'Other') NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `description` TEXT,
            `due_date` DATE NOT NULL,
            `status` ENUM('Pending', 'Paid', 'Overdue') NOT NULL DEFAULT 'Pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`tenantID`) REFERENCES `tenants`(`tenantID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if (!$conn->query($sql)) {
            throw new Exception("Error creating bills table: " . $conn->error);
        }
    } else {
        // Check if amount column exists
        $result = $conn->query("SHOW COLUMNS FROM bills LIKE 'amount'");
        if ($result->num_rows === 0) {
            // Add amount column if it doesn't exist
            if (!$conn->query("ALTER TABLE bills ADD COLUMN amount DECIMAL(10,2) NOT NULL AFTER bill_type")) {
                throw new Exception("Error adding amount column: " . $conn->error);
            }
        }
    }

    // Verify table structure
    $result = $conn->query("DESCRIBE bills");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Bills table verified and fixed',
        'data' => [
            'columns' => $columns
        ]
    ]);

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 