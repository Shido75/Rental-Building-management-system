-- Add password column to tenants table
ALTER TABLE `tenants`
ADD COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '';

-- Update existing tenants with a default hashed password (their ID number)
UPDATE `tenants` 
SET `password` = SHA2(CAST(`ID_number` AS CHAR), 256)
WHERE `password` = ''; 