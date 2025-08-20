<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Doctor.php';

class DoctorController {
    private $doctorModel;

    public function __construct() {
        $this->doctorModel = new Doctor();
        
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Check for AJAX requests for session actions
        if (isset($_GET['action']) && in_array($_GET['action'], ['deleteSession', 'updateSessionStatus', 'updateSession'])) {
            $this->handleAjaxRequest();
        }
    }
    
    /**
     * Handle AJAX requests for session actions
     */
    private function handleAjaxRequest() {
        // Set JSON header
        header('Content-Type: application/json');
        
        // Initialize response array
        $response = [
            'success' => false,
            'message' => '',
            'error' => null
        ];
        
        try {
            // Debug log
            error_log('AJAX Request: ' . print_r($_REQUEST, true));
            
            // Check if user is logged in and is a doctor
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
                error_log('Unauthorized access attempt - User not logged in or not a doctor');
                throw new Exception('Unauthorized access. Please log in as a doctor.');
            }
            
            // Get and validate action
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
            
            if (empty($action)) {
                throw new Exception('No action specified');
            }
            
            // Log the action
            error_log("Processing action: $action");
            
            // Handle the requested action
            switch ($action) {
                case 'deleteSession':
                    $response = $this->handleDeleteSession();
                    break;
                    
                case 'updateSessionStatus':
                    $response = $this->handleUpdateSessionStatus();
                    break;
                    
                case 'updateSession':
                    $response = $this->handleUpdateSession();
                    break;
                    
                default:
                    throw new Exception("Invalid action: $action");
            }
            
        } catch (Exception $e) {
            // Log the error
            error_log('Error in handleAjaxRequest: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Set error response
            $response = [
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
                'debug' => [
                    'session' => isset($_SESSION) ? 'Session exists' : 'No session',
                    'user_type' => $_SESSION['user_type'] ?? 'Not set',
                    'action' => $_GET['action'] ?? 'Not set'
                ]
            ];
        }
        // Ensure we have a valid JSON response
        if (!headers_sent()) {
            header_remove('X-Powered-By');
            header('Content-Type: application/json; charset=utf-8');
            
            // Add CORS headers if needed
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
        }
        
        // Output the JSON response
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Log the response for debugging
        error_log('AJAX Response: ' . json_encode($response, JSON_PRETTY_PRINT));
        
        // Terminate the script
        exit();
    }
    
    /**
     * Handle delete session request
     */
    private function handleDeleteSession() {
        if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
            throw new Exception('Invalid session ID');
        }
        
        $sessionId = (int)$_POST['session_id'];
        
        // Verify the session belongs to a treatment plan that belongs to this doctor
        $session = $this->doctorModel->getSessionById($sessionId);
        if (!$session) {
            throw new Exception('Session not found');
        }
        
        $appointment = $this->doctorModel->getAppointmentDetails($session['appointment_id']);
        if (!$appointment || $appointment['doctor_id'] != $_SESSION['user']['id']) {
            throw new Exception('Unauthorized to delete this session');
        }
        
        $result = $this->doctorModel->deleteSession($sessionId);
        
        return [
            'success' => $result,
            'message' => $result ? 'Session deleted successfully' : 'Failed to delete session'
        ];
    }
    
    /**
     * Handle update session status request
     */
    private function handleUpdateSessionStatus() {
        if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
            throw new Exception('Invalid session ID');
        }
        
        if (!isset($_POST['status']) || !in_array($_POST['status'], ['scheduled', 'completed', 'cancelled'])) {
            throw new Exception('Invalid status');
        }
        
        $sessionId = (int)$_POST['session_id'];
        $status = $_POST['status'];
        
        // Verify the session belongs to a treatment plan that belongs to this doctor
        $session = $this->doctorModel->getSessionById($sessionId);
        if (!$session) {
            throw new Exception('Session not found');
        }
        
        $appointment = $this->doctorModel->getAppointmentDetails($session['appointment_id']);
        if (!$appointment || $appointment['doctor_id'] != $_SESSION['user']['id']) {
            throw new Exception('Unauthorized to update this session');
        }
        
        $result = $this->doctorModel->updateSessionStatus($sessionId, $status);
        
        return [
            'success' => $result,
            'message' => $result ? 'Session status updated successfully' : 'Failed to update session status'
        ];
    }
    
    /**
     * Handle update session request
     */
    private function handleUpdateSession() {
        if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
            throw new Exception('Invalid session ID');
        }
        
        if (empty($_POST['session_date'])) {
            throw new Exception('Session date is required');
        }
        
        $sessionId = (int)$_POST['session_id'];
        $sessionDate = $_POST['session_date'];
        $notes = $_POST['notes'] ?? '';
        
        // Verify the session belongs to a treatment plan that belongs to this doctor
        $session = $this->doctorModel->getSessionById($sessionId);
        if (!$session) {
            throw new Exception('Session not found');
        }
        
        $appointment = $this->doctorModel->getAppointmentDetails($session['appointment_id']);
        if (!$appointment || $appointment['doctor_id'] != $_SESSION['user']['id']) {
            throw new Exception('Unauthorized to update this session');
        }
        
        $result = $this->doctorModel->updateSession($sessionId, [
            'session_date' => $sessionDate,
            'notes' => $notes
        ]);
        
        if ($result) {
            return [
                'success' => true,
                'session_id' => $sessionId,
                'session_date' => $sessionDate,
                'notes' => $notes,
                'message' => 'Session updated successfully'
            ];
        } else {
            throw new Exception('Failed to update session');
        }
    }

    public function getDoctorSchedule($doctor_id) {
        return $this->doctorModel->getScheduleByDoctor($doctor_id);
    }

    public function getScheduleBySpecialty($specialty) {
        return $this->doctorModel->getScheduleBySpecialty($specialty);
    }
    
    /**
     * View and manage a specific appointment
     * @param int $appointmentId Appointment ID
     * @return array Appointment data with treatment plan or error message
     */
    public function viewAppointment($appointmentId) {
        // Initialize response array
        $response = [
            'success' => false,
            'error' => '',
            'appointment' => null,
            'treatment_plan' => null,
            'current_date' => date('Y-m-d')
        ];

        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        error_log('=== Starting viewAppointment ===');
        error_log('Appointment ID: ' . $appointmentId);
        error_log('Session status: ' . session_status());
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            error_log('Session data: ' . print_r($_SESSION, true));
        } else {
            error_log('No active session');
        }

        try {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verify user is logged in as a doctor
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor' || !isset($_SESSION['user']['id'])) {
                $response['error'] = 'Unauthorized access. Please log in as a doctor.';
                error_log('Unauthorized access attempt to view appointment - User not logged in as doctor');
                return $response;
            }

            $doctorId = (int)$_SESSION['user']['id'];
            
            // Validate appointment ID
            if (!is_numeric($appointmentId) || $appointmentId <= 0) {
                $response['error'] = 'Invalid appointment ID.';
                error_log('Invalid appointment ID provided: ' . $appointmentId);
                return $response;
            }

            // Get appointment details
            $appointment = $this->doctorModel->getAppointmentDetails($appointmentId);
            
            if (!$appointment) {
                $response['error'] = 'Appointment not found.';
                error_log('Appointment not found with ID: ' . $appointmentId);
                return $response;
            }

            // Verify the appointment belongs to this doctor
            if (!isset($appointment['doctor_id'])) {
                $response['error'] = 'Invalid appointment data.';
                error_log('Appointment data is missing doctor_id');
                return $response;
            }
            
            if ((int)$appointment['doctor_id'] !== $doctorId) {
                $response['error'] = 'You do not have permission to view this appointment.';
                error_log(sprintf(
                    'Access denied - Doctor ID %s tried to access appointment ID %s',
                    $doctorId,
                    $appointmentId
                ));
                return $response;
            }

            // Get or create treatment plan
            error_log('Attempting to get or create treatment plan...');
            error_log('Patient ID: ' . ($appointment['patient_id'] ?? 'not set'));
            error_log('Appointment ID: ' . $appointmentId);
            error_log('Doctor ID: ' . $doctorId);
            
            try {
                // First try to get existing treatment plan
                $treatmentPlan = $this->doctorModel->getTreatmentPlanByAppointmentId($appointmentId);
                
                // If no treatment plan exists, create a new one
                if (!$treatmentPlan) {
                    error_log('No existing treatment plan found, creating new one...');
                    $treatmentPlan = [
                        'id' => null,
                        'patient_id' => $appointment['patient_id'] ?? 0,
                        'appointment_id' => $appointmentId,
                        'diagnosis' => '',
                        'duration' => '4 weeks',
                        'total_sessions' => 1,
                        'notes' => '',
                        'sessions' => []
                    ];
                    
                    // Save the new treatment plan to database
                    $treatmentPlanId = $this->doctorModel->createTreatmentPlan($treatmentPlan);
                    if ($treatmentPlanId) {
                        $treatmentPlan['id'] = $treatmentPlanId;
                    } else {
                        error_log('Failed to create new treatment plan');
                        $treatmentPlan = null;
                    }
                } else {
                    // Get sessions for existing treatment plan
                    $sessions = $this->doctorModel->getTreatmentSessions($treatmentPlan['id']);
                    $treatmentPlan['sessions'] = $sessions ?: [];
                }
                
                error_log('Treatment plan result: ' . print_r($treatmentPlan, true));
                
                if (!$treatmentPlan) {
                    $response['error'] = 'Failed to load or create treatment plan. No data returned.';
                    error_log('Failed to get or create treatment plan for appointment ID: ' . $appointmentId);
                    return $response;
                }
            } catch (Exception $e) {
                error_log('Exception in treatment plan handling: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                throw $e; // Re-throw to be caught by the outer try-catch
            }
            
            // Format the appointment data
            $formattedAppointment = [
                'id' => $appointment['id'] ?? 0,
                'date' => $appointment['appointment_date'] ?? '',
                'time' => $appointment['appointment_time'] ?? '',
                'status' => $appointment['status'] ?? 'scheduled',
                'visit_type' => $appointment['visit_type'] ?? 'consultation',
                'location' => $appointment['location'] ?? 'Clinic',
                'patient' => [
                    'id' => $appointment['patient_id'] ?? 0,
                    'name' => trim(($appointment['patient_first_name'] ?? '') . ' ' . ($appointment['patient_last_name'] ?? '')) ?: 'Patient',
                    'email' => $appointment['email'] ?? '',
                    'phone' => $appointment['phone'] ?? '',
                    'age' => $appointment['age'] ?? '',
                    'gender' => $appointment['gender'] ?? 'unknown',
                    'address' => $appointment['address'] ?? ''
                ],
                'doctor' => [
                    'id' => $doctorId,
                    'name' => $appointment['doctor_name'] ?? 'Doctor',
                    'specialty' => $appointment['specialty_name'] ?? 'General'
                ]
            ];
            
            // Ensure treatment plan has required fields
            $formattedTreatmentPlan = array_merge([
                'id' => null,
                'diagnosis' => '',
                'duration' => '4 weeks',
                'total_sessions' => 1,
                'notes' => '',
                'sessions' => []
            ], (array)$treatmentPlan);
            
            // If no sessions exist, ensure we have an empty array
            if (!isset($formattedTreatmentPlan['sessions']) || !is_array($formattedTreatmentPlan['sessions'])) {
                $formattedTreatmentPlan['sessions'] = [];
            }
            
            $response['success'] = true;
            $response['appointment'] = $formattedAppointment;
            $response['treatment_plan'] = $formattedTreatmentPlan;
            
            return $response;

        } catch (Exception $e) {
            // Log the full exception with more context
            $errorMsg = 'Error in viewAppointment: ' . $e->getMessage();
            error_log('=== CRITICAL ERROR ===');
            error_log('Error: ' . $errorMsg);
            error_log('File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Appointment ID: ' . $appointmentId);
            error_log('Doctor ID: ' . ($doctorId ?? 'not set'));
            error_log('Session data: ' . print_r($_SESSION, true));
            error_log('=== END ERROR ===');
            
            // More specific error message for debugging
            $response['error'] = 'An error occurred while loading the appointment. ';
            $response['error'] .= 'Error: ' . $e->getMessage();
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
            
            // Only show detailed error in development environment
            if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false) {
                $response['error'] = 'An error occurred while loading the appointment. Please try again later.';
                unset($response['debug']);
            }
        }
    }
    
    /**
     * Update treatment plan
     * @param array $data Form data
     * @return array Result of the operation
     */
    public function updateTreatmentPlan($data) {
        // Set JSON header for AJAX response
        header('Content-Type: application/json');
        
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Verify user is logged in as a doctor
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor' || !isset($_SESSION['user']['id'])) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in as a doctor.']);
                exit();
            }
            
            // Get form data
            $planId = (int)($data['treatment_plan_id'] ?? 0);
            $appointmentId = (int)($data['appointment_id'] ?? 0);
            $diagnosis = trim($data['diagnosis'] ?? '');
            $duration = trim($data['duration'] ?? '');
            $totalSessions = (int)($data['total_sessions'] ?? 1);
            $notes = trim($data['notes'] ?? '');
            
            // Validate required fields
            if (empty($diagnosis) || empty($duration)) {
                echo json_encode(['success' => false, 'error' => 'Diagnosis and duration are required.']);
                exit();
            }
            
            // Get appointment details to verify ownership
            $appointment = $this->doctorModel->getAppointmentDetails($appointmentId);
            if (!$appointment || $appointment['doctor_id'] != $_SESSION['user']['id']) {
                echo json_encode(['success' => false, 'error' => 'You do not have permission to update this treatment plan.']);
                exit();
            }
            
            // If no plan ID, create a new one
            if ($planId <= 0) {
                $planData = [
                    'patient_id' => $appointment['patient_id'],
                    'doctor_id' => $_SESSION['user']['id'],
                    'appointment_id' => $appointmentId,
                    'diagnosis' => $diagnosis,
                    'duration' => $duration,
                    'total_sessions' => $totalSessions,
                    'notes' => $notes,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $planId = $this->doctorModel->createTreatmentPlan($planData);
                
                if (!$planId) {
                    echo json_encode(['success' => false, 'error' => 'Failed to create treatment plan.']);
                    exit();
                }
                
                $response = [
                    'success' => true, 
                    'message' => 'Treatment plan created successfully.',
                    'plan_id' => $planId
                ];
                echo json_encode($response);
                exit();
                
            } else {
                // Update existing plan
                $updateData = [
                    'diagnosis' => $diagnosis,
                    'duration' => $duration,
                    'total_sessions' => $totalSessions,
                    'notes' => $notes
                ];
                
                $result = $this->doctorModel->updateTreatmentPlan($planId, $updateData);
                
                if ($result) {
                    $response = [
                        'success' => true, 
                        'message' => 'Treatment plan updated successfully.',
                        'plan_id' => $planId
                    ];
                    echo json_encode($response);
                    exit();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update treatment plan.']);
                    exit();
                }
            }
            
        } catch (Exception $e) {
            error_log("Error in updateTreatmentPlan: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'An error occurred while updating the treatment plan.']);
            exit();
        }
    }
    
    /**
     * Add a new treatment session
     * @param array $data Session data
     * @return array Result of the operation
     */
    public function addTreatmentSession($data) {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
                return ['success' => false, 'error' => 'Unauthorized access.'];
            }
            
            $planId = (int)($data['treatment_plan_id'] ?? 0);
            $sessionDate = $data['session_date'] ?? '';
            $notes = trim($data['session_notes'] ?? '');
            
            if (empty($sessionDate)) {
                return ['success' => false, 'error' => 'Session date is required.'];
            }
            
            $sessionId = $this->doctorModel->addTreatmentSession($planId, $sessionDate, $notes);
            
            if ($sessionId) {
                return [
                    'success' => true, 
                    'message' => 'Session added successfully.',
                    'session_id' => $sessionId
                ];
            } else {
                return ['success' => false, 'error' => 'Failed to add session.'];
            }
            
        } catch (Exception $e) {
            error_log("Error in addTreatmentSession: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred while adding the session.'];
        }
    }
    
    /**
     * Update treatment session status
     * @param array $data Session update data
     * @return array Result of the operation
     */
    public function updateSessionStatus($data) {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
                return ['success' => false, 'error' => 'Unauthorized access.'];
            }
            
            $sessionId = (int)($data['session_id'] ?? 0);
            $status = $data['status'] ?? '';
            
            if (empty($sessionId) || !in_array($status, ['scheduled', 'completed', 'cancelled'])) {
                return ['success' => false, 'error' => 'Invalid parameters.'];
            }
            
            $result = $this->doctorModel->updateSessionStatus($sessionId, $status);
            
            if ($result) {
                return ['success' => true, 'message' => 'Session status updated successfully.'];
            } else {
                return ['success' => false, 'error' => 'Failed to update session status.'];
            }
            
        } catch (Exception $e) {
            error_log("Error in updateSessionStatus: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred while updating the session status.'];
        }
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
