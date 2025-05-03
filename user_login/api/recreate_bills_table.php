<?php
require_once 'config.php';

try {
    // Drop existing bills table if it exists
    $conn->query("DROP TABLE IF EXISTS bills");
    
    // Create bills table with correct structure
    $sql = "CREATE TABLE `bills` (
        `billID` INT PRIMARY KEY AUTO_INCREMENT,
        `tenantID` INT NOT NULL,
        `bill_type` ENUM('Rent', 'Water', 'Electricity', 'Maintenance', 'Other') NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `due_date` DATE NOT NULL,
        `status` ENUM('Pending', 'Paid', 'Overdue') NOT NULL DEFAULT 'Pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`tenantID`) REFERENCES `tenants`(`tenantID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Bills table recreated successfully'
        ]);
    } else {
        throw new Exception("Error creating bills table: " . $conn->error);
    }

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 