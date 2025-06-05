<?php
require_once ("Database.php");

class Patient {
    private $db;
    private $link;

    public function __construct() {
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
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