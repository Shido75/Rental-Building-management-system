-- Add password column to tenants table if it doesn't exist
ALTER TABLE `tenants`
ADD COLUMN IF NOT EXISTS `password` VARCHAR(255) NOT NULL DEFAULT ''; 