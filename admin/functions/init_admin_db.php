<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Create connection
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS Company";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db('Company');

    // Create admin table
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating admin table: " . $conn->error);
    }

    // Create default admin user
    $email = 'admin@admin.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $name = 'Administrator';

    $stmt = $conn->prepare("INSERT INTO admin (email, password, name) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $email, $password, $name);
    
    if (!$stmt->execute()) {
        // If user already exists, this is fine
        if ($conn->errno !== 1062) { // 1062 is the error code for duplicate entry
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    }

    echo "Admin database initialized successfully!<br>";
    echo "You can now log in to the admin panel with:<br>";
    echo "Email: admin@admin.com<br>";
    echo "Password: admin123<br>";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "<br>");
}
?> 