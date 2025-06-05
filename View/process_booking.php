<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: book_appointment.php');
    exit();
}

// Get form data
$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
$time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';

if (!$doctor_id || !$time_slot) {
    $_SESSION['error'] = 'Invalid booking data';
    header('Location: book_appointment.php');
    exit();
}

require_once("../Controller/BookingController.php");
require_once("../Model/Doctor.php");

try {
    $doctor = new Doctor();
    $booking = $doctor->bookAppointment($doctor_id, $_SESSION['user_id'], $time_slot, date('Y-m-d'));
    
    if ($booking['success']) {
        $_SESSION['success'] = 'Appointment booked successfully!';
    } else {
        $_SESSION['error'] = $booking['error'] ?? 'Failed to book appointment';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header('Location: book_appointment.php');
exit();

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit();
}

$booking = new BookingController();
$result = $booking->validateAndBook(
    $_SESSION['user_id'],
    $data['doctor_id'],
    $data['appointment_date'],
    $data['time_slot']
);

echo json_encode($result);
