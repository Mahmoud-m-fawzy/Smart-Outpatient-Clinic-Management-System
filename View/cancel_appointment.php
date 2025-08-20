<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a patient
if (!isset($_SESSION['patient_id'])) {
    $_SESSION['error'] = 'You must be logged in to cancel an appointment';
    header('Location: /MVC/View/login.php');
    exit();
}

// Check if request is POST and has appointment_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['appointment_id'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/MVC/View/patient_dashboard.php'));
    exit();
}

$appointmentId = (int)$_POST['appointment_id'];

// Include necessary models
require_once("../Model/Patient.php");

try {
    $patientModel = new Patient();
    
    // Cancel the appointment
    $success = $patientModel->cancelAppointment($appointmentId, $_SESSION['patient_id']);
    
    if ($success) {
        $_SESSION['success'] = 'Appointment has been cancelled successfully.';
    } else {
        throw new Exception('Failed to cancel appointment. It may have already been cancelled or you do not have permission.');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to the previous page
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/MVC/View/patient_dashboard.php'));
exit();
