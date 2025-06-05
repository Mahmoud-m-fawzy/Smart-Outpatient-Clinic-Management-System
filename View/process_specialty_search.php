<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]));
}

require_once(__DIR__ . "/../Model/Doctor.php");

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $doctor = new Doctor();
        $specialty = isset($_POST['specialty']) ? $_POST['specialty'] : '';
        $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';
        
        // Get doctors based on filters
        if ($specialty && $doctorId) {
            // Both specialty and doctor ID provided
            $doctors = $doctor->searchBySpecialty($specialty);
            $doctors = array_filter($doctors, function($doc) use ($doctorId) {
                return $doc['id'] == $doctorId;
            });
        } elseif ($specialty) {
            // Only specialty provided
            $doctors = $doctor->searchBySpecialty($specialty);
        } elseif ($doctorId) {
            // Only doctor ID provided
            $doctors = array_filter($doctor->getAllDoctors(), function($doc) use ($doctorId) {
                return $doc['id'] == $doctorId;
            });
        } else {
            // No filters, get all doctors
            $doctors = $doctor->getAllDoctors();
        }
        
        // Get doctor IDs for schedule lookup
        $doctorIds = array_map(function($doc) {
            return $doc['id'];
        }, $doctors);
        
        // Get schedules for these doctors
        $schedules = $doctor->getScheduleByDoctorIds($doctorIds);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'doctors' => array_values($doctors), // Reset array keys
                'schedules' => $schedules
            ]
        ]);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log('Error in process_specialty_search.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
