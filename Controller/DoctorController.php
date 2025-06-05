<?php
require_once __DIR__ . '/../model/Doctor.php';

class DoctorController {
    private $doctorModel;

    public function __construct() {
        $this->doctorModel = new Doctor();
    }

    public function getDoctorSchedule($doctor_id) {
        return $this->doctorModel->getScheduleByDoctor($doctor_id);
    }

    public function getScheduleBySpecialty($specialty) {
        return $this->doctorModel->getScheduleBySpecialty($specialty);
    }
    
    /**
     * Handle doctor login
     * @param string $idNumber Doctor's ID number
     * @param string $password Doctor's password
     * @return array Login result with status and user data or error message
     */
    public function login($idNumber, $password) {
        try {
            if (empty($idNumber) || empty($password)) {
                return ['success' => false, 'error' => 'ID Number and password are required'];
            }

            $result = $this->doctorModel->loginDoctor($idNumber, $password);

            if ($result['success']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user'] = $result['user'];
                $_SESSION['user_type'] = 'doctor';
                return ['success' => true, 'user' => $result['user']];
            }
            return $result;
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred during login.'];
        }
    }
    
    /**
     * Log out the current doctor
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        header('Location: /doctor-login');
        exit;
    }
    
    /**
     * Get dashboard data for the doctor
     * @return array Dashboard data including doctor info and appointments
     */
    public function dashboard() {
        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            error_log('Session data in dashboard: ' . print_r($_SESSION, true));
            
            // Check if user is logged in and is a doctor
            if (!isset($_SESSION['user_type'])) {
                error_log('User type not set in session');
                return ['error' => 'Not logged in. Please log in again.'];
            }
            
            if ($_SESSION['user_type'] !== 'doctor') {
                error_log('User is not a doctor. User type: ' . $_SESSION['user_type']);
                return ['error' => 'Access denied. Doctor privileges required.'];
            }
            
            if (!isset($_SESSION['user']['id'])) {
                error_log('Doctor ID not found in session');
                return ['error' => 'Session error. Please log in again.'];
            }
            
            $doctorId = (int)$_SESSION['user']['id'];
            $currentDate = date('Y-m-d');
            
            error_log('Fetching doctor with ID: ' . $doctorId);
            
            // Get doctor details
            $doctor = $this->doctorModel->getDoctorById($doctorId);
            
            if (!$doctor) {
                error_log('Doctor not found with ID: ' . $doctorId);
                return ['error' => 'Doctor profile not found. Please contact support.'];
            }
            
            error_log('Doctor found: ' . print_r($doctor, true));
            
            // Get today's appointments
            error_log('Fetching appointments for doctor ' . $doctorId . ' on ' . $currentDate);
            $appointments = $this->doctorModel->getAppointmentsByDate($doctorId, $currentDate);
            error_log('Appointments found: ' . print_r($appointments, true));
            
            return [
                'doctor' => $doctor,
                'appointments' => is_array($appointments) ? $appointments : [],
                'currentDate' => $currentDate
            ];
            
        } catch (Exception $e) {
            $errorMsg = 'Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            error_log($errorMsg);
            error_log('Stack trace: ' . $e->getTraceAsString());
            return ['error' => 'An error occurred while loading the dashboard. ' . $errorMsg];
        }
    }
}
