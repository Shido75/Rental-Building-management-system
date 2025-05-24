-- Create bills table
USE company;

CREATE TABLE IF NOT EXISTS `bills` (
    `billID` INT PRIMARY KEY AUTO_INCREMENT,
    `tenantID` INT NOT NULL,
    `bill_type` ENUM('Rent', 'Water', 'Electricity', 'Maintenance', 'Other') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('Pending', 'Paid', 'Overdue') NOT NULL DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenantID`) REFERENCES `tenants`(`tenantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 