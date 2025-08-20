<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a patient
if (!isset($_SESSION['patient_id'])) {
    $_SESSION['error'] = 'You must be logged in to delete an appointment';
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
$patientId = $_SESSION['patient_id'];

// Include the Patient model
require_once("../Model/Patient.php");

try {
    $patientModel = new Patient();
    
    // Get the appointment first to verify status and ownership
    $appointment = $patientModel->getAppointmentById($appointmentId);
    
    if (!$appointment || $appointment['patient_id'] != $patientId) {
        throw new Exception('Appointment not found or access denied');
    }
    
    // Only allow deleting completed appointments
    if (strtolower($appointment['status']) !== 'completed') {
        throw new Exception('Only completed appointments can be deleted');
    }
    
    // Delete the appointment
    $result = $patientModel->deleteAppointment($appointmentId, $patientId);
    
    if ($result) {
        $_SESSION['success'] = 'Appointment has been deleted successfully.';
    } else {
        throw new Exception('Failed to delete appointment');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to the previous page
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/MVC/View/patient_dashboard.php'));
exit();
