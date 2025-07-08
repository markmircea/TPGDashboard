<?php
/**
 * Database setup script for TPG Dashboard
 * Run this script once to create the MySQL database
 */

// Database configuration
$host = 'localhost';
$user = 'aibrainl_tpg';
$pass = 'She-wolf11!!';
$dbname = 'aibrainl_tpg';

try {
    // Connect to MySQL server (without specifying database)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created successfully or already exists.\n";
    
    // Select the database
    $pdo->exec("USE `$dbname`");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Users table created successfully.\n";
    
    // Create scripts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scripts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        script_name VARCHAR(255) NOT NULL,
        script_type VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Scripts table created successfully.\n";
    
    // Create script_results table
    $pdo->exec("CREATE TABLE IF NOT EXISTS script_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        script_id INT,
        status ENUM('success', 'failure', 'warning', 'info') NOT NULL,
        message TEXT,
        detailed_message LONGTEXT,
        execution_time DECIMAL(10,2),
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (script_id) REFERENCES scripts(id)
    )");
    echo "Script_results table created successfully.\n";
    
    // Check if detailed_message column exists, add if missing
    $stmt = $pdo->query("SHOW COLUMNS FROM script_results LIKE 'detailed_message'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE script_results ADD COLUMN detailed_message LONGTEXT AFTER message");
        echo "Added detailed_message column to existing table.\n";
    }
    
    // Update status column to ENUM if it's not already
    $stmt = $pdo->query("SHOW COLUMNS FROM script_results WHERE Field = 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($column && strpos($column['Type'], 'enum') === false) {
        $pdo->exec("ALTER TABLE script_results MODIFY COLUMN status ENUM('success', 'failure', 'warning', 'info') NOT NULL");
        echo "Updated status column to support new status types.\n";
    }
    
    // Insert default admin user if not exists
    $adminUsername = 'admin';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$adminUsername, $adminPassword]);
    echo "Default admin user created (username: admin, password: admin123).\n";
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now access the dashboard at: http://localhost:8000\n";
    echo "Login with username: admin, password: admin123\n";
    
} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "1. MySQL server is running\n";
    echo "2. The connection credentials are correct\n";
    echo "3. The user has permission to create databases\n";
}
?>
