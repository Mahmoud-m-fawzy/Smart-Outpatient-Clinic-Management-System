<?php
require_once("Database.php");

class Doctor {
    private $db;
    private $link;

    public function __construct() {
        try {
            $this->db = new Database();
            $this->link = $this->db->connectToDB();
        } catch (Exception $e) {
            error_log("Failed to initialize Doctor model: " . $e->getMessage());
            throw $e;
        }
    }
    

    public function getLink() {
        return $this->link;
    }

    public function addAvailableSlot($doctorId, $dayOfWeek, $startTime, $endTime, $location, $notes = '', $slot_fee = 0.00) {
        try {
            // Validate day of week
            $validDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            if (!in_array($dayOfWeek, $validDays)) {
                return ['success' => false, 'error' => 'Invalid day of week'];
            }
            
            // Validate time format
            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $endTime)) {
                return ['success' => false, 'error' => 'Invalid time format. Use HH:MM:SS'];
            }
            
            // Check if slot already exists
            $checkSql = "SELECT id FROM doctor_schedule 
                        WHERE doctor_id = ? AND day_of_week = ? 
                        AND (
                            (start_time <= ? AND end_time > ?) 
                            OR (start_time < ? AND end_time >= ?) 
                            OR (start_time >= ? AND end_time <= ?)
                        )";
            
            $stmt = mysqli_prepare($this->link, $checkSql);
            mysqli_stmt_bind_param($stmt, 'isssssss', 
                $doctorId, $dayOfWeek, 
                $startTime, $startTime,
                $endTime, $endTime,
                $startTime, $endTime
            );
            
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                return ['success' => false, 'error' => 'This time slot overlaps with an existing slot for ' . $dayOfWeek];
            }
            
            // Insert new slot
            $insertSql = "INSERT INTO doctor_schedule 
                         (doctor_id, day_of_week, start_time, end_time, location, notes, availability, slot_fee) 
                         VALUES (?, ?, ?, ?, ?, ?, 'Available', ?)";
            
            $stmt = mysqli_prepare($this->link, $insertSql);
            mysqli_stmt_bind_param($stmt, 'isssssd', 
                $doctorId, $dayOfWeek, $startTime, $endTime, $location, $notes, $slot_fee
            );
            
            if (mysqli_stmt_execute($stmt)) {
                return ['success' => true, 'id' => mysqli_insert_id($this->link)];
            } else {
                throw new Exception("Failed to add time slot: " . mysqli_error($this->link));
            }
            
        } catch (Exception $e) {
            error_log("Error adding time slot: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get treatment plan by appointment ID
     * @param int $appointmentId Appointment ID
     * @return array|null Treatment plan data or null if not found
     */
    public function getTreatmentPlanByAppointmentId($appointmentId) {
        $sql = "SELECT * FROM treatment_plans WHERE appointment_id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $appointmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($plan = mysqli_fetch_assoc($result)) {
            return $plan;
        }
        return null;
    }
    
    /**
     * Create a new treatment plan
     * @param array $planData Treatment plan data
     * @return int|bool The new plan ID or false on failure
     */
    public function createTreatmentPlan($planData) {
        $sql = "INSERT INTO treatment_plans (
            patient_id, appointment_id, diagnosis, duration, total_sessions, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'iissis',
            $planData['patient_id'],
            $planData['appointment_id'],
            $planData['diagnosis'],
            $planData['duration'],
            $planData['total_sessions'],
            $planData['notes']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->link);
        }
        
        error_log("Failed to create treatment plan: " . mysqli_error($this->link));
        return false;
    }
    
    /**
     * Get treatment sessions for a treatment plan
     * @param int $planId Treatment plan ID
     * @return array List of treatment sessions
     */
    public function getTreatmentSessions($planId) {
        $sessions = [];
        $sql = "SELECT * FROM treatment_sessions 
                WHERE treatment_plan_id = ? 
                ORDER BY session_date ASC, session_number ASC";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $planId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sessions[] = $row;
        }
        
        return $sessions;
    }
    public function loginDoctor($id_number, $password) {
        $sql = "SELECT id, FN, LN, password, ID_NUMBER FROM doctor WHERE ID_NUMBER = ?";
        $stmt = mysqli_prepare($this->link, $sql);
    
        if (!$stmt) {
            return ['success' => false, 'error' => 'Database error.'];
        }
    
        mysqli_stmt_bind_param($stmt, "s", $id_number);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $doctor = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    
        if (!$doctor) {
            return ['success' => false, 'error' => 'Doctor not found.'];
        }
    
        // Check if password matches hashed password or plain text (for testing)
        if (!password_verify($password, $doctor['password']) && $doctor['password'] !== $password) {
            return ['success' => false, 'error' => 'Incorrect password.'];
        }
        
        // If password was in plain text, hash it for security
        if ($doctor['password'] === $password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_prepare($this->link, "UPDATE doctor SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $hashedPassword, $doctor['id']);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
    
        // Update login flag
        $update = mysqli_prepare($this->link, "UPDATE doctor SET flag_login = 1 WHERE id = ?");
        mysqli_stmt_bind_param($update, "i", $doctor['id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    
        // Remove password and return doctor data
        unset($doctor['password']);
        $doctor['login_type'] = 'doctor';
    
        return ['success' => true, 'user' => $doctor];
    }
    
    public function getAllDoctors() {
        $sql = "SELECT id, FN, LN, title, specialty, photo, gender FROM doctor";
        $result = mysqli_query($this->link, $sql);
        
        $doctors = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Handle photo path
                if (!empty($row['photo'])) {
                    $filename = basename($row['photo']);
                    // Check if the file exists in uploads/doctors
                    $sourcePath = dirname(dirname(__FILE__)) . '/uploads/doctors/' . $filename;
                    $targetPath = dirname(dirname(__FILE__)) . '/View/images/' . $filename;
                    
                    // If the file exists in uploads but not in images, copy it
                    if (file_exists($sourcePath) && !file_exists($targetPath)) {
                        copy($sourcePath, $targetPath);
                    }
                    
                    // Set the relative path for the view
                    $row['photo'] = 'images/' . $filename;
                }
                $doctors[] = $row;
            }
            mysqli_free_result($result);
        }
        return $doctors;
    }


    public function getSpecialties() {
        $specialties = [];
        $result = mysqli_query($this->link, "SELECT DISTINCT specialty FROM doctor WHERE specialty IS NOT NULL AND specialty != ''");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $specialties[] = $row['specialty'];
            }
            mysqli_free_result($result);
        }
        return $specialties;
    }
    
    /**
     * Get or create a treatment plan for a patient
     * @param int $patientId Patient ID
     * @param int $appointmentId Appointment ID
     * @param int $doctorId Doctor ID
     * @return array|null Treatment plan data or null on failure
     */
    public function getOrCreateTreatmentPlan($patientId, $appointmentId, $doctorId) {
        // Check if treatment plan exists
        $sql = "SELECT * FROM treatment_plans WHERE patient_id = ? AND appointment_id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $patientId, $appointmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($plan = mysqli_fetch_assoc($result)) {
            // Get sessions for this plan
            $plan['sessions'] = [];
            $sessions_sql = "SELECT * FROM treatment_sessions WHERE treatment_plan_id = ? ORDER BY session_date ASC";
            $sessions_stmt = mysqli_prepare($this->link, $sessions_sql);
            mysqli_stmt_bind_param($sessions_stmt, 'i', $plan['id']);
            mysqli_stmt_execute($sessions_stmt);
            $sessions_result = mysqli_stmt_get_result($sessions_stmt);
            
            while ($session = mysqli_fetch_assoc($sessions_result)) {
                $plan['sessions'][] = $session;
            }
            
            return $plan;
        } else {
            // Create a new treatment plan if none exists
            $insert_sql = "INSERT INTO treatment_plans (patient_id, doctor_id, appointment_id, diagnosis, duration, notes, created_at) 
                          VALUES (?, ?, ?, 'Initial Diagnosis', '4 weeks', '', NOW())";
            $insert_stmt = mysqli_prepare($this->link, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, 'iii', $patientId, $doctorId, $appointmentId);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $plan_id = mysqli_insert_id($this->link);
                
                // Create initial session
                $session_date = date('Y-m-d', strtotime('+1 week'));
                $session_sql = "INSERT INTO treatment_sessions (treatment_plan_id, session_date, status, notes, created_at) 
                               VALUES (?, ?, 'scheduled', 'Initial session', NOW())";
                $session_stmt = mysqli_prepare($this->link, $session_sql);
                $status = 'scheduled';
                mysqli_stmt_bind_param($session_stmt, 'iss', $plan_id, $session_date, $status);
                mysqli_stmt_execute($session_stmt);
                
                // Return the newly created plan with session
                return [
                    'id' => $plan_id,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'appointment_id' => $appointmentId,
                    'diagnosis' => 'Initial Diagnosis',
                    'duration' => '4 weeks',
                    'notes' => '',
                    'sessions' => [
                        [
                            'id' => mysqli_insert_id($this->link),
                            'session_date' => $session_date,
                            'status' => 'scheduled',
                            'notes' => 'Initial session'
                        ]
                    ]
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Update treatment plan
     * @param int $planId Treatment plan ID
     * @param array $data Array of fields to update (diagnosis, duration, total_sessions, notes)
     * @return bool True on success, false on failure
     */
    public function updateTreatmentPlan($planId, $data) {
        $fields = [];
        $types = '';
        $values = [];
        
        // Build the SET clause dynamically based on provided fields
        if (isset($data['diagnosis'])) {
            $fields[] = 'diagnosis = ?';
            $types .= 's';
            $values[] = $data['diagnosis'];
        }
        
        if (isset($data['duration'])) {
            $fields[] = 'duration = ?';
            $types .= 's';
            $values[] = $data['duration'];
        }
        
        if (isset($data['total_sessions'])) {
            $fields[] = 'total_sessions = ?';
            $types .= 'i';
            $values[] = $data['total_sessions'];
        }
        
        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $types .= 's';
            $values[] = $data['notes'];
        }
        
        // Always update the updated_at timestamp
        $fields[] = 'updated_at = NOW()';
        
        if (empty($fields)) {
            return false; // No fields to update
        }
        
        // Add the plan ID to the values array
        $types .= 'i';
        $values[] = $planId;
        
        // Build and prepare the SQL query
        $sql = "UPDATE treatment_plans SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . mysqli_error($this->link));
            return false;
        }
        
        // Bind parameters dynamically
        $bindParams = [$types];
        $bindParams = array_merge($bindParams, $values);
        
        // Create references for bind_param
        $refs = [];
        foreach($bindParams as $key => $value) {
            $refs[$key] = &$bindParams[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $refs);
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    /**
     * Add a new treatment session
     * @param int $planId Treatment plan ID
     * @param string $sessionDate Session date (YYYY-MM-DD)
     * @param string $notes Session notes
     * @return int|bool The new session ID on success, false on failure
     */
    public function addTreatmentSession($planId, $sessionDate, $notes) {
        $sql = "INSERT INTO treatment_sessions (treatment_plan_id, session_date, status, notes, created_at) 
                VALUES (?, ?, 'scheduled', ?, NOW())";
        $stmt = mysqli_prepare($this->link, $sql);
        $status = 'scheduled';
        mysqli_stmt_bind_param($stmt, 'isss', $planId, $sessionDate, $status, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->link);
        }
        return false;
    }
    
    /**
     * Update treatment session status
     * @param int $sessionId Session ID
     * @param string $status New status (scheduled/completed/cancelled)
     * @return bool True on success, false on failure
     */
    /**
     * Get session by ID
     * @param int $sessionId Session ID
     * @return array|null Session data or null if not found
     */
    public function getSessionById($sessionId) {
        $sql = "SELECT ts.*, tp.appointment_id 
                FROM treatment_sessions ts
                JOIN treatment_plans tp ON ts.treatment_plan_id = tp.id
                WHERE ts.id = ?
                LIMIT 1";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $sessionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Delete a treatment session
     * @param int $sessionId Session ID
     * @return bool True on success, false on failure
     */
    public function deleteSession($sessionId) {
        $sql = "DELETE FROM treatment_sessions WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $sessionId);
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Update session status
     * @param int $sessionId Session ID
     * @param string $status New status (scheduled/completed/cancelled)
     * @return bool True on success, false on failure
     */
    public function updateSessionStatus($sessionId, $status) {
        $sql = "UPDATE treatment_sessions SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $status, $sessionId);
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Update session details
     * @param int $sessionId Session ID
     * @param array $data Session data to update (session_date, notes)
     * @return bool True on success, false on failure
     */
    public function updateSession($sessionId, $data) {
        $sql = "UPDATE treatment_sessions SET session_date = ?, notes = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            'ssi', 
            $data['session_date'],
            $data['notes'],
            $sessionId
        );
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Get appointment details with patient and doctor info
     * @param int $appointmentId Appointment ID
     * @return array|null Appointment data or null if not found
     */
    public function getAppointmentDetails($appointmentId) {
        error_log("getAppointmentDetails called with ID: " . $appointmentId);
        
        if (!is_numeric($appointmentId)) {
            error_log("Error: Invalid appointment ID format: " . $appointmentId);
            return null;
        }
        
        $sql = "SELECT a.*, 
                       p.id as patient_id, p.FN as patient_first_name, p.LN as patient_last_name, 
                       p.age, p.gender, p.phone, p.email, p.address,
                       d.FN as doctor_first_name, d.LN as doctor_last_name, d.specialty, d.id as doctor_id,
                       ds.start_time, ds.end_time
                FROM appointment a
                JOIN patient p ON a.patient_id = p.id
                JOIN doctor d ON a.doctor_id = d.id
                LEFT JOIN doctor_schedule ds ON a.doctor_id = ds.doctor_id AND a.day_of_week = ds.day_of_week
                WHERE a.id = ?
                LIMIT 1";
        
        error_log("SQL Query: " . $sql);
        error_log("Parameters: " . print_r([$appointmentId], true));
                
        $stmt = mysqli_prepare($this->link, $sql);
        if (!$stmt) {
            error_log("Error preparing statement: " . mysqli_error($this->link));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $appointmentId);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Error getting result set: " . mysqli_error($this->link));
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $appointment = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($appointment) {
            error_log("Successfully retrieved appointment: " . print_r($appointment, true));
            return $appointment;
        } else {
            error_log("No appointment found with ID: " . $appointmentId);
            return null;
        }
    }
    
    public function getScheduleBySpecialty($specialty) {
        // Get all doctors' schedules for this specialty with availability status
        $scheduleStmt = mysqli_prepare($this->link, "
            SELECT 
                ds.*, 
                d.specialty, 
                d.FN, 
                d.LN,
                ds.slot_fee,
                CASE 
                    WHEN ds.availability = 1 THEN 'available'
                    ELSE 'booked'
                END as status
            FROM doctor_schedule ds
            JOIN doctor d ON ds.doctor_id = d.id
            WHERE d.specialty = ?
            ORDER BY FIELD(ds.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday')
        ");
        mysqli_stmt_bind_param($scheduleStmt, "s", $specialty);
        mysqli_stmt_execute($scheduleStmt);
        $scheduleResult = mysqli_stmt_get_result($scheduleStmt);
        $schedule = [];
        
        // Process the schedule and set availability status
        while ($row = mysqli_fetch_assoc($scheduleResult)) {
            $row['doctor_name'] = "Dr. {$row['FN']} {$row['LN']}";
            $row['doctor_id'] = $row['doctor_id']; // Ensure doctor_id is included
            $schedule[] = $row;
        }
        
        mysqli_stmt_close($scheduleStmt);

        return $schedule;
    }

    public function getScheduleByDoctor($doctor_id) {
        // First get the doctor's schedule
        $scheduleStmt = mysqli_prepare($this->link, "
            SELECT * FROM doctor_schedule 
            WHERE doctor_id = ? 
            ORDER BY FIELD(day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday')
        ");
        mysqli_stmt_bind_param($scheduleStmt, "i", $doctor_id);
        mysqli_stmt_execute($scheduleStmt);
        $scheduleResult = mysqli_stmt_get_result($scheduleStmt);
        $schedule = mysqli_fetch_all($scheduleResult, MYSQLI_ASSOC);
        mysqli_stmt_close($scheduleStmt);

        // Then get all booked appointments for this doctor
        $bookedStmt = mysqli_prepare($this->link, "
            SELECT 
                day_of_week,
                TIME_FORMAT(appointment_time, '%H:%i:%s') as time_slot 
            FROM appointment
            WHERE doctor_id = ? AND status = 'Scheduled'
        ");
        mysqli_stmt_bind_param($bookedStmt, "i", $doctor_id);
        mysqli_stmt_execute($bookedStmt);
        $bookedResult = mysqli_stmt_get_result($bookedStmt);
        
        // Group booked slots by day
        $bookedSlots = [];
        while ($row = mysqli_fetch_assoc($bookedResult)) {
            if (!isset($bookedSlots[$row['day_of_week']])) {
                $bookedSlots[$row['day_of_week']] = [];
            }
            $bookedSlots[$row['day_of_week']][] = $row['time_slot'];
        }
        mysqli_stmt_close($bookedStmt);

        // Merge booked slots into schedule
        foreach ($schedule as &$slot) {
            $day = $slot['day_of_week'];
            $slot['booked_slots'] = isset($bookedSlots[$day]) ? $bookedSlots[$day] : [];
        }

        return $schedule;
    }

    /**
     * Get doctor by ID
     * @param int $doctorId Doctor's ID
     * @return array|null Doctor data or null if not found
     */
    public function getDoctorById($doctorId) {
        // First, get the actual column names from the database
        $columns = [];
        $result = mysqli_query($this->link, "SHOW COLUMNS FROM doctor");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $columns[] = $row['Field'];
            }
            mysqli_free_result($result);
        }
        
        // Define the columns we'd like to select if they exist
        $desiredColumns = ['id', 'FN', 'LN', 'title', 'specialty', 'photo', 'gender', 'email', 'phone', 'ID_NUMBER'];
        
        // Filter to only include columns that exist in the database
        $selectedColumns = array_intersect($desiredColumns, $columns);
        
        // Always include the required ID field
        if (!in_array('id', $selectedColumns)) {
            array_unshift($selectedColumns, 'id');
        }
        
        // Add created_at for member_since if it exists
        if (in_array('created_at', $columns)) {
            $selectedColumns[] = "DATE_FORMAT(created_at, '%M %Y') as member_since";
        }
        
        $sql = "SELECT " . implode(', ', $selectedColumns) . " FROM doctor WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . mysqli_error($this->link));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $doctorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $doctor = mysqli_fetch_assoc($result); // Fixed: Using $result instead of $stmt
        
        if (!$doctor) {
            mysqli_stmt_close($stmt);
            return null;
        }
        
        mysqli_stmt_close($stmt);
        
        // Handle photo path
        if (!empty($doctor['photo'])) {
            $filename = basename($doctor['photo']);
            $sourcePath = dirname(dirname(__FILE__)) . '/uploads/doctors/' . $filename;
            $targetPath = dirname(dirname(__FILE__)) . '/View/images/' . $filename;
            
            if (file_exists($sourcePath) && !file_exists($targetPath)) {
                @mkdir(dirname($targetPath), 0777, true);
                copy($sourcePath, $targetPath);
            }
            
            $doctor['photo'] = 'images/' . $filename;
        } else {
            // Set default photo if none exists
            $doctor['photo'] = 'images/doctor-avatar.png';
        }
        
        return $doctor;
    }
    
    /**
     * Get appointments by doctor ID and date
     * @param int $doctorId Doctor's ID
     * @param string $date Date in Y-m-d format
     * @return array Array of appointments
     */
    public function getAppointmentsByDate($doctorId, $date) {
        $sql = "SELECT a.*, p.FN as patient_FN, p.LN as patient_LN, p.phone as patient_phone
                FROM appointment a
                JOIN patient p ON a.patient_id = p.id
                WHERE a.doctor_id = ? AND DATE(a.appointment_time) = ?
                ORDER BY a.appointment_time ASC";
                
        $stmt = mysqli_prepare($this->link, $sql);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . mysqli_error($this->link));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "is", $doctorId, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $appointments;
    }
    
    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}
