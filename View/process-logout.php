<?php
session_start();
require_once("../Model/Patient.php");
require_once("../Model/Manager.php");
require_once("../Model/Staff.php");



if (isset($_SESSION['user_id'])) {
    $patient = new Patient();
    $result = $patient->logout($_SESSION['user_id']);
    
    if ($result['success']) {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
    }
}
if (isset($_SESSION['manager_id'])) {
    $manager = new Manager();
    $manager->logout($_SESSION['manager_id']);
}
$_SESSION = array();
session_destroy();
if (isset($_SESSION['staff_id'])) {
    $staff = new Staff();
    $result = $staff->logout($_SESSION['staff_id']);
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Clear any session cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

header("Location: staff_login.php");

// Redirect to login page
header("Location: manager_login.php");
// Redirect to login page
header("Location: login.php");
exit();
?>



