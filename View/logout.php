<?php
session_start();
require_once("../Model/Staff.php");
require_once("../Model/Manager.php");
require_once("../Model/Patient.php");

// Handle staff logout
if (isset($_SESSION['staff_id'])) {
    $staff = new Staff();
    $staff->logout($_SESSION['staff_id']);
}
// Handle manager logout
elseif (isset($_SESSION['manager_id'])) {
    $manager = new Manager();
    $manager->logout($_SESSION['manager_id']);
}
// Handle patient logout
elseif (isset($_SESSION['user_id'])) {
    $patient = new Patient();
    $patient->logout($_SESSION['user_id']);
}

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

// Redirect to login page
header("Location: login.php");
exit();
?>
