<?php
session_start();
require_once("../Model/Patient.php");
require_once("../Model/Patient.php");


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
    require_once("../Model/Manager.php");
    $manager = new Manager();
    $manager->logout($_SESSION['manager_id']);
}
$_SESSION = array();
session_destroy();

// Redirect to login page
header("Location: manager_login.php");
// Redirect to login page
header("Location: login.php");
exit();
?>


