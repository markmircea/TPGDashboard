<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'aibrainl_tpg');
define('DB_USER', 'aibrainl_tpg');
define('DB_PASS', 'She-wolf11!!');


// Initialize session (only if not already started)
// PHP_SESSION_NONE means no session exists yet
if (session_status() == PHP_SESSION_NONE) {
    // Set session to last 1 year (31536000 seconds)
    ini_set('session.gc_maxlifetime', 31536000);
    session_set_cookie_params(31536000);
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

?>
