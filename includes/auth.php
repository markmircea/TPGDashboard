<?php
require_once 'config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Login function
function login($username, $password) {
    $pdo = getDatabase();
    
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_time'] = time();
        return true;
    }
    
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Check session timeout
function checkSessionTimeout() {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            logout();
        }
    }
}

// Require login (redirect to login page if not logged in)
function requireLogin() {
    checkSessionTimeout();
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// Handle logout request
if (isset($_GET['logout'])) {
    logout();
}
?>
