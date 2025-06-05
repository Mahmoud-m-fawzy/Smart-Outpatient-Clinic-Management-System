    <?php
require_once __DIR__ . '/../model/Staff.php';
require_once __DIR__ . '/../model/Doctor.php';
require_once __DIR__ . '/../model/Patient.php';
require_once __DIR__ . '/../model/Appointment.php';

class StaffController {
    private $staffModel;
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;

    public function __construct() {
        $this->staffModel = new Staff();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
        $this->appointmentModel = new Appointment();
    }
    
    /**
     * Handle staff login
     * @param string $idNumber Staff's ID number
     * @param string $password Staff's password
     * @return array Login result with status and user data or error message
     */
    public function login($idNumber, $password) {
        try {
            if (empty($idNumber) || empty($password)) {
                return ['success' => false, 'error' => 'ID Number and password are required'];
            }

            $result = $this->staffModel->login($idNumber, $password);

            if ($result['success']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                // Store relevant staff data in session
                $_SESSION['user'] = [
                    'id' => $result['staff']['id'],
                    'id_number' => $result['staff']['ID_NUMBER'],
                    'first_name' => $result['staff']['first_name'],
                    'last_name' => $result['staff']['last_name'],
                    'email' => $result['staff']['email'],
                    'phone' => $result['staff']['phone'],
                    'role' => $result['staff']['role']
                ];
                $_SESSION['user_type'] = 'staff';
                // Return the full path to the staff dashboard
                return ['success' => true, 'redirect' => '/MVC/View/staff_dashboard.php'];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred during login.'];
        }
    }
    
    /**
     * Log out the current staff member
     * @return array Result of the logout operation
     */
    public function logout($staffId) {
        try {
            // Update login status to logged out
            $this->staffModel->updateLoginStatus($staffId, 0);
            
            // Clear session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_destroy();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Staff logout error: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred during logout.'];
        }
    }

    /**
     * Get doctor's schedule
     */
    public function getDoctorSchedules($doctorId = null) {
        try {
            return $this->doctorModel->getSchedules($doctorId);
        } catch (Exception $e) {
            error_log("Error getting doctor schedules: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to fetch doctor schedules'];
        }
    }

    /**
     * Add/Update doctor schedule
     */
    public function saveDoctorSchedule($scheduleData) {
        try {
            // Validate required fields
            $required = ['doctor_id', 'day_of_week', 'start_time', 'end_time'];
            foreach ($required as $field) {
                if (empty($scheduleData[$field])) {
                    return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }

            if (empty($scheduleData['id'])) {
                // Add new schedule
                $result = $this->doctorModel->addSchedule($scheduleData);
            } else {
                // Update existing schedule
                $result = $this->doctorModel->updateSchedule($scheduleData);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error saving doctor schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to save schedule'];
        }
    }

    /**
     * Delete doctor schedule
     */
    public function deleteDoctorSchedule($scheduleId) {
        try {
            return $this->doctorModel->deleteSchedule($scheduleId);
        } catch (Exception $e) {
            error_log("Error deleting doctor schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete schedule'];
        }
    }

    /**
     * Book appointment for patient
     */
    public function bookAppointment($appointmentData) {
        try {
            // Validate required fields
            $required = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time'];
            foreach ($required as $field) {
                if (empty($appointmentData[$field])) {
                    return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }

            return $this->appointmentModel->bookAppointment($appointmentData);
        } catch (Exception $e) {
            error_log("Error booking appointment: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to book appointment'];
        }
    }

    /**
     * Search patients
     */
    public function searchPatients($searchTerm) {
        try {
            return $this->patientModel->search($searchTerm);
        } catch (Exception $e) {
            error_log("Error searching patients: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to search patients'];
        }
    }

    /**
     * Update patient data
     */
    public function updatePatient($patientId, $updateData) {
        try {
            if (empty($patientId) || empty($updateData)) {
                return ['success' => false, 'error' => 'Invalid patient data'];
            }
            return $this->patientModel->updatePatient($patientId, $updateData);
        } catch (Exception $e) {
            error_log("Error updating patient: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update patient'];
        }
    }

    /**
     * Generate daily appointment report
     */
    public function generateDailyReport($date = null) {
        try {
            if (!$date) {
                $date = date('Y-m-d');
            }
            return $this->appointmentModel->getDailyReport($date);
        } catch (Exception $e) {
            error_log("Error generating daily report: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to generate daily report'];
        }
    }
}
