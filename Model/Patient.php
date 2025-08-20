<?php
require_once ("Database.php");

class Patient {
    private $db;
    private $link;

    public function __construct() {
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
    }
    
    /**
     * Get the database connection link
     * @return mysqli Database connection
     */
    public function getLink() {
        return $this->link;
    }
    
    /**
     * Send a new chat message
     * @param int $appointmentId The ID of the appointment
     * @param string $senderType Type of sender (patient/doctor)
     * @param string $message The message content
     * @return array Result of the operation
     */
    /**
     * Create a new patient
     * @param array $patientData Array containing patient information
     * @return array Result of the operation
     */
    /**
     * Find a patient by email or phone
     * @param string $email
     * @param string $phone
     * @return array|false Patient data if found, false otherwise
     */
    public function findByEmailOrPhone($email, $phone) {
        if (!empty($email)) {
            $sql = "SELECT id FROM patient WHERE email = ? OR phone = ?";
            $stmt = mysqli_prepare($this->link, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $email, $phone);
        } else {
            $sql = "SELECT id FROM patient WHERE phone = ?";
            $stmt = mysqli_prepare($this->link, $sql);
            mysqli_stmt_bind_param($stmt, "s", $phone);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * Create a new patient
     * @param array $patientData Array containing patient information
     * @return array Result of the operation
     */
    public function createPatient($patientData) {
        // Hash the password
        $hashedPassword = password_hash($patientData['password'], PASSWORD_DEFAULT);
        
        // Check if email is provided
        $hasEmail = !empty($patientData['email']);
        
        // Build the SQL query based on whether email is provided
        if ($hasEmail) {
            $sql = "INSERT INTO patient (
                FN, LN, email, phone, password, age, idnumber, NN, 
                address, job, gender, marital, plain_password
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->link, $sql);
            
            // Bind parameters with email
            mysqli_stmt_bind_param(
                $stmt, 
                "sssssisssssss",
                $patientData['first_name'],
                $patientData['last_name'],
                $patientData['email'],
                $patientData['phone'],
                $hashedPassword,
                $patientData['age'],
                $patientData['id_number'],
                $patientData['national_id'],
                $patientData['address'],
                $patientData['job'],
                $patientData['gender'],
                $patientData['marital_status'],
                $patientData['password']
            );
        } else {
            $sql = "INSERT INTO patient (
                FN, LN, phone, password, age, idnumber, NN, 
                address, job, gender, marital, plain_password
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->link, $sql);
            
            // Bind parameters without email
            mysqli_stmt_bind_param(
                $stmt, 
                "ssssisssssss",
                $patientData['first_name'],
                $patientData['last_name'],
                $patientData['phone'],
                $hashedPassword,
                $patientData['age'],
                $patientData['id_number'],
                $patientData['national_id'],
                $patientData['address'],
                $patientData['job'],
                $patientData['gender'],
                $patientData['marital_status'],
                $patientData['password']
            );
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $patientId = mysqli_insert_id($this->link);
            if ($patientId) {
                return [
                    'success' => true,
                    'patient_id' => $patientId
                ];
            }
        }
        
        $error = mysqli_error($this->link);
        error_log("Database error in createPatient: " . $error);
        error_log("SQL: " . $sql);
        error_log("Data: " . print_r($patientData, true));
        
        return [
            'success' => false,
            'error' => $error ?: 'Unknown database error'
        ];
    }
    
    public function sendChatMessage($appointmentId, $senderType, $message) {
        $sql = "INSERT INTO chat_messages (appointment_id, sender_type, message) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $appointmentId, $senderType, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            return [
                'success' => true,
                'message_id' => mysqli_insert_id($this->link)
            ];
        }
        return ['success' => false, 'error' => mysqli_error($this->link)];
    }

    /**
     * Get chat history for an appointment
     * @param int $appointmentId The ID of the appointment
     * @return array Array of chat messages
     */
    public function getChatHistory($appointmentId) {
        $sql = "SELECT * FROM chat_messages 
                WHERE appointment_id = ? 
                ORDER BY created_at ASC";
        
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $appointmentId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $messages = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        return $messages;
    }

    /**
     * Check for new messages
     * @param int $appointmentId The ID of the appointment
     * @param int $lastSeenMessageId The ID of the last seen message
     * @return int Number of unread messages
     */
    public function getUnreadMessageCount($appointmentId, $lastSeenMessageId) {
        $sql = "SELECT COUNT(*) as unread_count 
                FROM chat_messages 
                WHERE appointment_id = ? AND id > ?";
        
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $appointmentId, $lastSeenMessageId);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result)['unread_count'] ?? 0;
    }
 
    public function register($FN, $LN, $email, $phone, $password, $plain_password, $age, $idnumber, $NN, $address, $job, $gender, $marital) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "INSERT INTO patient (FN, LN, email, phone, password, plain_password, age, idnumber, NN, address, job, gender, marital) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssissssss", 
                $FN, 
                $LN, 
                $email, 
                $phone, 
                $hashed_password, 
                $plain_password, 
                $age, 
                $idnumber, 
                $NN, 
                $address, 
                $job, 
                $gender, 
                $marital
            );
    
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return true;
            } else {
                error_log("SQL Error: " . mysqli_error($this->link));
            }
        }
    
        mysqli_stmt_close($stmt);
        return false;
    }

    /**
     * Get patient by ID
     * @param int $id Patient ID
     * @return array|null Patient data or null if not found
     */
    public function getPatientById($id) {
        $sql = "SELECT * FROM patient WHERE id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 1) {
                    $patient = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($stmt);
                    return $patient;
                }
            }
            mysqli_stmt_close($stmt);
        }
        return null;
    }
    
    /**
     * Update patient's profile photo
     * @param int $patientId Patient ID
     * @param string $photoFilename Name of the photo file
     * @return bool True on success, false on failure
     */
    public function updateProfilePhoto($patientId, $photoFilename) {
        // First, check if photo column exists in the table
        $checkColumn = "SHOW COLUMNS FROM patient LIKE 'photo'";
        $result = mysqli_query($this->link, $checkColumn);
        
        if (mysqli_num_rows($result) === 0) {
            // Add the photo column if it doesn't exist
            $alterTable = "ALTER TABLE patient ADD COLUMN photo VARCHAR(255) DEFAULT NULL AFTER id";
            if (!mysqli_query($this->link, $alterTable)) {
                error_log("Failed to add photo column: " . mysqli_error($this->link));
                return false;
            }
        }
        
        // Update the photo filename in the database
        $sql = "UPDATE patient SET photo = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $photoFilename, $patientId);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return true;
            } else {
                error_log("Failed to update profile photo: " . mysqli_error($this->link));
            }
            mysqli_stmt_close($stmt);
        }
        return false;
    }
    
    /**
     * Get all appointments for a specific patient
     * @param int $patientId The ID of the patient
     * @param string $status Optional status filter (e.g., 'upcoming', 'completed', 'cancelled')
     * @param int $limit Optional limit for number of appointments to return
     * @return array Array of appointments
     */
    /**
     * Cancel an appointment
     * @param int $appointmentId The ID of the appointment to cancel
     * @param int $patientId The ID of the patient (for security)
     * @return bool True on success, false on failure
     */
    public function cancelAppointment($appointmentId, $patientId) {
        $sql = "UPDATE appointment SET status = 'cancelled' WHERE id = ? AND patient_id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $appointmentId, $patientId);
            
            $result = mysqli_stmt_execute($stmt);
            $affectedRows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $result && $affectedRows > 0;
        }
        
        return false;
    }
    
    /**
     * Get all appointments for a specific patient
     * @param int $patientId The ID of the patient
     * @param string $status Optional status filter (e.g., 'upcoming', 'completed', 'cancelled')
     * @param int $limit Optional limit for number of appointments to return
     * @return array Array of appointments
     */
    /**
     * Get a single appointment by ID with security check
     * @param int $appointmentId The ID of the appointment
     * @return array|null Appointment data or null if not found
     */
    public function getAppointmentById($appointmentId) {
        $sql = "SELECT a.*, 
                       d.FN as doctor_first_name, d.LN as doctor_last_name,
                       d.specialty as doctor_specialty
                FROM appointment a
                LEFT JOIN doctor d ON a.doctor_id = d.id
                WHERE a.id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $appointmentId);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            } else {
                error_log("Error fetching appointment: " . mysqli_error($this->link));
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return null;
    }
    
    /**
     * Delete an appointment
     * @param int $appointmentId The ID of the appointment to delete
     * @param int $patientId The ID of the patient (for security)
     * @return bool True on success, false on failure
     */
    public function deleteAppointment($appointmentId, $patientId) {
        // First verify the appointment exists and belongs to the patient
        $appointment = $this->getAppointmentById($appointmentId);
        
        if (!$appointment || $appointment['patient_id'] != $patientId) {
            return false;
        }
        
        // Only allow deleting completed appointments
        if (strtolower($appointment['status']) !== 'completed') {
            return false;
        }
        
        $sql = "DELETE FROM appointment WHERE id = ? AND patient_id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $appointmentId, $patientId);
            
            $result = mysqli_stmt_execute($stmt);
            $affectedRows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            return $result && $affectedRows > 0;
        }
        
        return false;
    }
    
    public function getAppointments($patientId, $status = null, $limit = null) {
        $sql = "SELECT a.*, 
                       d.FN as doctor_first_name, d.LN as doctor_last_name,
                       d.specialty as doctor_specialty
                FROM appointment a
                LEFT JOIN doctor d ON a.doctor_id = d.id
                WHERE a.patient_id = ?";
        
        $params = [];
        $types = 'i';
        $params[] = $patientId;
        
        // Add status filter if provided
        if ($status !== null) {
            $sql .= " AND a.status = ?";
            $types .= 's';
            $params[] = $status;
        }
        
        // Add ordering by appointment time (newest first)
        $sql .= " ORDER BY a.appointment_time DESC";
        
        // Add limit if provided
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $types .= 'i';
            $params[] = (int)$limit;
        }
        
        $appointments = [];
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            // Bind parameters
            if (count($params) > 0) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $appointments[] = $row;
                }
            } else {
                error_log("Error fetching appointments: " . mysqli_error($this->link));
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return $appointments;
    }
    
    public function login($number, $password) {
        // Check if the number exists in either NN or idnumber
        $sql = "SELECT id, FN, LN, email, password, NN, idnumber FROM patient WHERE NN = ? OR idnumber = ?";
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $number, $number);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    // Verify password
                    if (password_verify($password, $row['password'])) {
                        // Update flag_login
                        $update_sql = "UPDATE patient SET flag_login = TRUE WHERE id = ?";
                        $update_stmt = mysqli_prepare($this->link, $update_sql);
                        mysqli_stmt_bind_param($update_stmt, "i", $row['id']);
                        mysqli_stmt_execute($update_stmt);
                        mysqli_stmt_close($update_stmt);

                        // Store email before unsetting
                        $email = $row['email'];
                        
                        // Remove sensitive data
                        unset($row['password']);
                        
                        // Determine login type
                        if ($row['NN'] === $number) {
                            $row['login_type'] = 'NN';
                        } else {
                            $row['login_type'] = 'idnumber';
                        }
                        
                        // Add email back to the result
                        $row['email'] = $email;
                        
                        unset($row['NN']);
                        unset($row['idnumber']);
                        
                        mysqli_stmt_close($stmt);
                        return ['success' => true, 'user' => $row];
                    } else {
                        mysqli_stmt_close($stmt);
                        return ['success' => false, 'error' => 'Incorrect password!'];
                    }
                } else {
                    mysqli_stmt_close($stmt);
                    return ['success' => false, 'error' => 'User not found!'];
                }
            }
            mysqli_stmt_close($stmt);
        }
        return ['success' => false, 'error' => 'Database error!'];
    }

    public function verifyId($idnumber) {
        $sql = "SELECT id, FN, LN FROM patient WHERE idnumber = ? OR NN = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $idnumber, $idnumber);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            }
        }

        mysqli_stmt_close($stmt);
        return false;
    }

    public function resetPassword($idnumber, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE patient SET password = ?, plain_password = ? WHERE idnumber = ? OR NN = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            // Bind both hashed_password and plain_password (new_password) correctly
            mysqli_stmt_bind_param($stmt, "ssss", $hashed_password, $new_password, $idnumber, $idnumber);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return true;
            }
        }

        mysqli_stmt_close($stmt);
        return false;
    }
    
    public function bookAppointment($patient_id, $doctor_id, $time_slot) {
        // Start transaction
        mysqli_begin_transaction($this->link);
        
        try {
            // 1. Check if the slot is still available
            $checkSql = "SELECT id FROM doctor_schedule
                         WHERE doctor_id = ?
                         AND day_of_week = ?
                         AND start_time <= ?
                         AND end_time >= ?
                         AND availability = 1";
            
            $checkStmt = mysqli_prepare($this->link, $checkSql);
            // Extract day of week from the time slot (or use current date if needed)
            $dayOfWeek = date('l'); // Using current date as appointment_date is removed
            mysqli_stmt_bind_param($checkStmt, "isss", $doctor_id, $dayOfWeek, $time_slot, $time_slot);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($result) === 0) {
                throw new Exception('This time slot is no longer available');
            }
            mysqli_stmt_close($checkStmt);
            
            // 2. Create the appointment (without appointment_date)
            $apptSql = "INSERT INTO appointment (patient_id, doctor_id, appointment_time, status)
                        VALUES (?, ?, ?, 'Scheduled')";
            $apptStmt = mysqli_prepare($this->link, $apptSql);
            mysqli_stmt_bind_param($apptStmt, "iis", $patient_id, $doctor_id, $time_slot);
            
            if (!mysqli_stmt_execute($apptStmt)) {
                throw new Exception('Failed to create appointment');
            }
            $appointment_id = mysqli_insert_id($this->link);
            mysqli_stmt_close($apptStmt);
            
            // 3. Update the slot availability
            $updateSql = "UPDATE doctor_schedule
                          SET availability = 0
                          WHERE doctor_id = ?
                          AND day_of_week = ?
                          AND start_time <= ?
                          AND end_time >= ?";
            
            $updateStmt = mysqli_prepare($this->link, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "isss", $doctor_id, $dayOfWeek, $time_slot, $time_slot);
            
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception('Failed to update schedule');
            }
            mysqli_stmt_close($updateStmt);
            
            // Commit transaction
            mysqli_commit($this->link);
            
            return [
                'success' => true, 
                'appointment_id' => $appointment_id,
                'message' => 'Appointment booked successfully!'
            ];
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($this->link);
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }

    public function logout($user_id) {
        $sql = "UPDATE patient SET flag_login = 0 WHERE id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return ['success' => true];
            }
            mysqli_stmt_close($stmt);
        }
        
        return ['success' => false, 'error' => 'Failed to logout'];
    }

    public function __destruct() {
        mysqli_close($this->link);
    }
} 