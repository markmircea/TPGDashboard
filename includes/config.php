<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tpg_dashboard');
define('DB_USER', 'root');
define('DB_PASS', '');

// Authentication configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Change this in production

// Application configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection function
function getDatabase() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Initialize database tables if they don't exist
function initializeDatabase() {
    $pdo = getDatabase();
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create scripts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scripts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        script_name VARCHAR(255) NOT NULL,
        script_type VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create script_results table
    $pdo->exec("CREATE TABLE IF NOT EXISTS script_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        script_id INT,
        status VARCHAR(50) NOT NULL,
        message TEXT,
        execution_time DECIMAL(10,2),
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (script_id) REFERENCES scripts(id)
    )");
    
    // Insert default admin user if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([ADMIN_USERNAME, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT)]);
}

// Initialize database on first load
initializeDatabase();
?>
