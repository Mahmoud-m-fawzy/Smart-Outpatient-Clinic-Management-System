    <?php
require_once __DIR__ . '/../model/Staff.php';
require_once __DIR__ . '/../model/Doctor.php';
require_once __DIR__ . '/../model/Patient.php';

class StaffController {
    private $staffModel;
    
    public function getUnavailableSlotsByDay($dayOfWeek) {
        $slots = $this->staffModel->getUnavailableSlotsByDay($dayOfWeek);
        
        // Enhance each slot with patient information if available
        foreach ($slots as $location => &$locationSlots) {
            foreach ($locationSlots as &$slot) {
                $patientInfo = $this->staffModel->getPatientForAppointmentSlot(
                    $dayOfWeek,
                    $slot['start'],
                    $slot['doctor']
                );
                
                if ($patientInfo) {
                    $slot['patient_id'] = $patientInfo['patient_id'];
                    $slot['patient_name'] = trim($patientInfo['FN'] . ' ' . $patientInfo['LN']);
                    $slot['patient_phone'] = $patientInfo['phone'];
                    $slot['appointment_id'] = $patientInfo['appointment_id'];
                    $slot['visit_type'] = $patientInfo['visit_type'];
                    $slot['location'] = $patientInfo['location'];
                    $slot['appointment_status'] = $patientInfo['appointment_status'];
                }
            }
        }
        
        return $slots;
    }
    private $doctorModel;
    private $patientModel;
    private $appointmentModel;

    public function __construct() {
        $this->staffModel = new Staff();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
    }
    
    /**
     * Get all appointments for a specific date
     * @param string $date Date in YYYY-MM-DD format
     * @return array Array of appointments
     */
    public function getAppointmentsByDate($date) {
        try {
            error_log("StaffController::getAppointmentsByDate called with date: " . $date);
            $appointments = $this->staffModel->getAppointmentsByDate($date);
            error_log("StaffController::getAppointmentsByDate returning " . count($appointments) . " appointments");
            return $appointments;
        } catch (Exception $e) {
            $error = "Error getting appointments by date: " . $e->getMessage();
            error_log($error);
            throw new Exception("Failed to retrieve appointments: " . $e->getMessage());
        }
    }
    
    public function addAvailableSlot($data) {
        try {
            $formData = [
                'doctor_id' => $data['doctor_id'] ?? null,
                'day_of_week' => $data['appointment_date'] ?? null,
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'location' => $data['location'] ?? null,
                'fee' => $data['fee'] ?? '0.00',
                'notes' => $data['notes'] ?? ''
            ];
            
            // Validate required fields
            $required = ['doctor_id', 'day_of_week', 'start_time', 'end_time', 'location'];
            foreach ($required as $field) {
                if (empty($formData[$field])) {
                    return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Format times to HH:MM:SS
            $startTime = date('H:i:s', strtotime($formData['start_time']));
            $endTime = date('H:i:s', strtotime($formData['end_time']));
            
            // Validate time order
            if (strtotime($startTime) >= strtotime($endTime)) {
                return ['success' => false, 'error' => 'End time must be after start time'];
            }
            
            // Validate fee
            $fee = filter_var($formData['fee'], FILTER_VALIDATE_FLOAT, [
                'options' => ['min_range' => 0]
            ]);
            
            if ($fee === false) {
                return ['success' => false, 'error' => 'Please enter a valid fee amount'];
            }
            
            // Format fee to 2 decimal places
            $slot_fee = number_format((float)$fee, 2, '.', '');
            
            // Add the slot
            $result = $this->doctorModel->addAvailableSlot(
                (int)$formData['doctor_id'],
                $formData['day_of_week'],
                $startTime,
                $endTime,
                $formData['location'],
                $formData['notes'],
                $slot_fee
            );
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'Time slot added successfully'];
            } else {
                return $result; // Return the error from the model
            }
            
        } catch (Exception $e) {
            error_log("Error in addAvailableSlot: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred while adding the time slot'];
        }
    }
    


    
    /**
     * Get status board data
     * @return array Status board data
     */
    /**
     * Show the add patient form
     */
    public function showAddPatientForm() {
        // Just include the view, no data needed
        require_once __DIR__ . '/../View/add_patient.php';
    }
    
    /**
     * Process patient creation
     * @param array $data Patient data from form
     * @return array Result of the operation
     */
    /**
     * Show the add slot form
     */
    public function showAddSlotForm() {
        // The view now handles getting doctors directly
        require_once __DIR__ . '/../View/add_slot.php';
    }
    
    /**
     * Create a new appointment slot
     * @param array $data Slot data
     * @return array Result of the operation
     */
    public function createAppointmentSlot($data) {
        try {
            // Validate required fields
            $required = ['doctor_id', 'appointment_date', 'start_time', 'end_time', 'location'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Convert date and time to datetime format
            $startTime = $data['appointment_date'] . ' ' . $data['start_time'] . ':00';
            $endTime = $data['appointment_date'] . ' ' . $data['end_time'] . ':00';
            
            // Get day of week
            $dayOfWeek = date('l', strtotime($data['appointment_date']));
            
            // Calculate duration in minutes
            $startTimestamp = strtotime($startTime);
            $endTimestamp = strtotime($endTime);
            $durationMinutes = round(($endTimestamp - $startTimestamp) / 60);
            
            // Create the appointment slot
            $sql = "INSERT INTO appointment (
                doctor_id, appointment_time, end_time, day_of_week, 
                location, notes, status, created_at, duration_minutes
            ) VALUES (?, ?, ?, ?, ?, ?, 'Scheduled', NOW(), ?)";
            
            // Use the database connection from the model
            $stmt = mysqli_prepare($this->doctorModel->getLink(), $sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . mysqli_error($this->doctorModel->getLink()));
            }
            
            $notes = $data['notes'] ?? '';
            
            mysqli_stmt_bind_param(
                $stmt, 
                'isssssi',
                $data['doctor_id'],
                $startTime,
                $endTime,
                $dayOfWeek,
                $data['location'],
                $notes,
                $durationMinutes
            );
            
            if (mysqli_stmt_execute($stmt)) {
                return [
                    'success' => true,
                    'message' => 'Appointment slot created successfully',
                    'slot_id' => mysqli_insert_id($this->doctorModel->getLink())
                ];
            }
            
            return [
                'success' => false,
                'error' => mysqli_error($this->doctorModel->getLink())
            ];
            
        } catch (Exception $e) {
            error_log("Error creating appointment slot: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create appointment slot. ' . $e->getMessage()
            ];
        }
    }
    
    public function createPatient($data) {
        try {
            // Map Arabic gender to English
            $genderMap = [
                'ذكر' => 'male',
                'أنثى' => 'female',
            ];
            
            // Map Arabic marital status to English
            $maritalMap = [
                'أعزب' => 'single',
                'متزوج' => 'married',
            ];
            
            // Set default values for optional fields with mapping
            $data['gender'] = $genderMap[$data['gender'] ?? ''] ?? '';
            $data['marital_status'] = $maritalMap[$data['marital_status'] ?? ''] ?? '';
            $data['address'] = $data['address'] ?? '';
            $data['job'] = $data['job'] ?? '';
            $data['national_id'] = $data['national_id'] ?? '';
            
            // Validate required fields
            $required = ['first_name', 'last_name', 'phone', 'age', 'id_number', 'gender'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Use phone number as password
            $data['password'] = $data['phone'];
            
            // Validate email format if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Please enter a valid email address'];
            }
            
            // Check if email or phone already exists
            $existing = $this->patientModel->findByEmailOrPhone($data['email'] ?? '', $data['phone']);
            if ($existing) {
                return ['success' => false, 'error' => 'A patient with this email or phone already exists'];
            }
            
            // Create the patient
            try {
                $result = $this->patientModel->createPatient($data);
                
                if ($result['success']) {
                    return [
                        'success' => true,
                        'message' => 'Patient created successfully',
                        'patient_id' => $result['patient_id']
                    ];
                }
                
                // Log the error from the model
                error_log("Error creating patient: " . ($result['error'] ?? 'Unknown error'));
                return [
                    'success' => false,
                    'error' => 'Failed to create patient. ' . ($result['error'] ?? 'Please try again.')
                ];
            } catch (Exception $e) {
                error_log("Exception in createPatient: " . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Database error: ' . $e->getMessage()
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error creating patient: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create patient. Please try again.'
            ];
        }
    }
    
    public function getStatusBoardData($day = null) {
        try {
            // If no day provided, use current day (0 = Sunday, 6 = Saturday)
            $day = $day !== null ? (int)$day : (int)date('w');
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
            $dayName = $dayNames[$day] ?? $dayNames[0];
            
            // Get all active rooms with their current appointments for the selected day
            $rooms = $this->staffModel->getRoomStatus($dayName);
            
            // If no rooms found, return default rooms
            if (empty($rooms)) {
                $roomNames = ['Clinic A', 'Clinic B', 'Clinic C', 'Clinic D'];
                foreach ($roomNames as $roomName) {
                    $rooms[] = [
                        'name' => $roomName,
                        'status' => 'Free',
                        'patient' => '',
                        'therapist' => '',
                        'end_time' => null,
                        'appointment_status' => null,
                        'appointment_number' => '',
                        'next_appointment' => null
                    ];
                }
            }
            
            return [
                'success' => true,
                'rooms' => $rooms,
                'day' => $dayName
            ];
            
        } catch (Exception $e) {
            error_log("Error getting status board data: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => 'Failed to load status board data: ' . $e->getMessage()
            ];
        }
    }
    
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



}
