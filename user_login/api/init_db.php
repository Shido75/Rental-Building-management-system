<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $sql = "CREATE DATABASE IF NOT EXISTS rental_house_management";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db('rental_house_management');

    // Read and execute the SQL file
    $sql_file = __DIR__ . '/../../database/user_login.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found at: " . $sql_file);
    }

    $sql = file_get_contents($sql_file);
    if ($sql === false) {
        throw new Exception("Could not read SQL file");
    }

    if (!$conn->multi_query($sql)) {
        throw new Exception("Error creating tables: " . $conn->error);
    }

    // Clear results
    while ($conn->more_results() && $conn->next_result());

    // Create test admin user
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $name = 'Administrator';
    $email = 'admin@example.com';

    $stmt = $conn->prepare("INSERT INTO user_profiles (username, password, name, email) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $password, $name, $email);
    
    if (!$stmt->execute()) {
        // If user already exists, this is fine
        if ($conn->errno !== 1062) { // 1062 is the error code for duplicate entry
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    }

    echo "Database initialized successfully!<br>";
    echo "You can now log in with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "<br>");
}
?> 