
<?php
// logout.php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';

// Log the logout action if user was logged in
if(isset($_SESSION['voter_reg'])) {
    logAdminAction($_SESSION['voter_reg'], 'LOGOUT', 'session', 0, '', $_SERVER['REMOTE_ADDR']);
    
    // Remove from active sessions
    $stmt = $pdo->prepare("DELETE FROM active_sessions WHERE registration_number = ?");
    $stmt->execute([$_SESSION['voter_reg']]);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if(isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear remember me cookie
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time()-3600, '/');
}

// Redirect to landing page
header("Location: index.php?msg=logged_out");
exit();
?>