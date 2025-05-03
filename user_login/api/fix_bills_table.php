<?php
require_once 'config.php';

try {
    // Drop existing bills table if it exists
    $conn->query("DROP TABLE IF EXISTS bills");
    
    // Create bills table with correct structure
    $sql = "CREATE TABLE IF NOT EXISTS `bills` (
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

    if ($conn->query($sql)) {
        // Verify the table was created correctly
        $result = $conn->query("DESCRIBE bills");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Bills table created successfully',
            'data' => [
                'columns' => $columns
            ]
        ]);
    } else {
        throw new Exception("Error creating bills table: " . $conn->error);
    }

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => $e->getMessage()]));
}
?> 