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
        $result = mysqli_query($this->link, "SELECT DISTINCT specialty FROM doctor WHERE specialty IS NOT NULL ORDER BY specialty");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $specialties[] = $row['specialty'];
            }
            mysqli_free_result($result);
        }
        return $specialties;
    }
    

    public function getScheduleBySpecialty($specialty) {
        // Get all doctors' schedules for this specialty with availability status
        $scheduleStmt = mysqli_prepare($this->link, "
            SELECT 
                ds.*, 
                d.specialty, 
                d.FN, 
                d.LN,
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
