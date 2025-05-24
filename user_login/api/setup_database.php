<?php
require_once 'config.php';

try {
    // Create payments table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `payments` (
        `payment_id` INT PRIMARY KEY AUTO_INCREMENT,
        `tenantID` INT NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `payment_method` VARCHAR(50) NOT NULL,
        `transaction_id` VARCHAR(100) NOT NULL,
        `payment_date` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`tenantID`) REFERENCES `tenants`(`tenantID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql)) {
        echo "Payments table created successfully\n";
    } else {
        throw new Exception("Error creating payments table: " . $conn->error);
    }

    // Create bills table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `bills` (
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
        echo "Bills table created successfully";
    } else {
        throw new Exception("Error creating bills table: " . $conn->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 