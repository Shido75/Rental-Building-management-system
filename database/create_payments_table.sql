-- Create payments table if it doesn't exist
CREATE TABLE IF NOT EXISTS `payments` (
    `payment_id` INT PRIMARY KEY AUTO_INCREMENT,
    `tenant_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(100) NOT NULL,
    `payment_date` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`tenantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;