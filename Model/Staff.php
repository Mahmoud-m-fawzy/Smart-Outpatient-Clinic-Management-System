<?php
require_once("Database.php");

class Staff {
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
     * Log out staff member
     * @param int $staffId The ID of the staff member to log out
     * @return array Result of the operation
     */

    
    /**
     * Get unavailable time slots by day of week
     * @param string $dayOfWeek Day of week (e.g., 'Monday', 'Tuesday')
     * @return array Array of unavailable slots grouped by location
     */
    public function getUnavailableSlotsByDay($dayOfWeek) {
        $slotsByLocation = [];
        
        $sql = "SELECT 
                    ds.start_time,
                    ds.end_time,
                    ds.location,
                    ds.notes,
                    CONCAT(d.FN, ' ', d.LN) as doctor_name
                FROM doctor_schedule ds
                JOIN doctor d ON ds.doctor_id = d.id
                WHERE ds.day_of_week = ?
                AND ds.availability = 'Unavailable'
                ORDER BY ds.location, ds.start_time";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $dayOfWeek);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $location = $row['location'];
                    if (!isset($slotsByLocation[$location])) {
                        $slotsByLocation[$location] = [];
                    }
                    
                    $slotsByLocation[$location][] = [
                        'start' => $row['start_time'],
                        'end' => $row['end_time'],
                        'notes' => $row['notes'],
                        'doctor' => $row['doctor_name']
                    ];
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return $slotsByLocation;
    }

    /**
     * Get patient information for a specific doctor's appointment slot
     * @param string $dayOfWeek Day of week (e.g., 'Monday', 'Tuesday')
     * @param string $startTime Appointment start time (H:i:s format)
     * @param string $doctorName Doctor's full name
     * @return array|null Patient information or null if not found
     */
    /**
     * Get all appointments for a specific date with patient and doctor details
     * @param string $date Date in YYYY-MM-DD format
     * @return array Array of appointments
     */
    public function getAppointmentsByDate($date) {
        $appointments = [];
        
        // Debug: Log the input date
        error_log("Getting appointments for date: " . $date);
        
        $sql = "SELECT 
                    a.id as appointment_id,
                    a.appointment_time,
                    a.day_of_week,
                    a.status,
                    a.visit_type,
                    a.notes,
                    p.id as patient_id,
                    CONCAT(p.FN, ' ', p.LN) as patient_name,
                    p.phone as patient_phone,
                    d.id as doctor_id,
                    CONCAT(d.FN, ' ', d.LN) as doctor_name,
                    a.location,
                    a.created_at,
                    COALESCE(
                        (SELECT ds.slot_fee 
                         FROM doctor_schedule ds 
                         WHERE ds.doctor_id = d.id 
                         AND ds.day_of_week = a.day_of_week 
                         AND a.appointment_time BETWEEN ds.start_time AND ds.end_time 
                         LIMIT 1), 
                        100.00
                    ) as slot_fee
                FROM appointment a
                JOIN patient p ON a.patient_id = p.id
                JOIN doctor d ON a.doctor_id = d.id
                WHERE a.day_of_week = ?
                ORDER BY a.appointment_time ASC";
        
        // Debug: Log the SQL query
        error_log("SQL Query: " . $sql);
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            // Convert date to day of week (e.g., 'Monday', 'Tuesday')
            $dayOfWeek = date('l', strtotime($date));
            mysqli_stmt_bind_param($stmt, 's', $dayOfWeek);
            
            // Debug: Log bound parameters
            error_log("Bound parameters - date: " . $date);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $numRows = mysqli_num_rows($result);
                
                // Debug: Log number of rows found
                error_log("Number of appointments found: " . $numRows);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    // Debug: Log each appointment found
                    error_log("Found appointment: " . print_r($row, true));
                    
                    // Calculate end time by adding 30 minutes to appointment time
                    $startTime = new DateTime($row['appointment_time']);
                    $endTime = clone $startTime;
                    $endTime->add(new DateInterval('PT30M'));
                    
                    $appointments[] = [
                        'id' => $row['appointment_id'],
                        'start_time' => $row['appointment_time'],
                        'end_time' => $endTime->format('H:i:s'),
                        'day_of_week' => $row['day_of_week'],
                        'status' => $row['status'],
                        'visit_type' => $row['visit_type'],
                        'notes' => $row['notes'],
                        'patient' => [
                            'id' => $row['patient_id'],
                            'name' => $row['patient_name'],
                            'phone' => $row['patient_phone']
                        ],
                        'doctor' => [
                            'id' => $row['doctor_id'],
                            'name' => $row['doctor_name']
                        ],
                        'location' => $row['location'],
                        'fee' => $row['slot_fee']
                    ];
                }
            } else {
                // Debug: Log any SQL execution errors
                error_log("SQL Error: " . mysqli_error($this->link));
            }
            
            mysqli_stmt_close($stmt);
        } else {
            // Debug: Log any SQL preparation errors
            error_log("Failed to prepare statement: " . mysqli_error($this->link));
        }
        
        // Debug: Log the final result
        error_log("Returning appointments: " . print_r($appointments, true));
        
        return $appointments;
    }
    
    public function getPatientForAppointmentSlot($dayOfWeek, $startTime, $doctorName) {
        $sql = "SELECT 
                    p.id as patient_id,
                    p.FN,
                    p.LN,
                    p.phone,
                    a.id as appointment_id,
                    a.notes,
                    a.visit_type,
                    a.location,
                    a.status as appointment_status
                FROM appointment a
                JOIN patient p ON a.patient_id = p.id
                JOIN doctor d ON a.doctor_id = d.id
                WHERE a.day_of_week = ?
                AND a.appointment_time = ?
                AND CONCAT(d.FN, ' ', d.LN) = ?
                AND a.status = 'Scheduled'
                LIMIT 1";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, 'sss', $dayOfWeek, $startTime, $doctorName);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);
                return $result ?: null;
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return null;
    }

    public function createStaff($staffData) {
        $sql = "INSERT INTO staff (ID_NUMBER, password, FN, LN, role, email, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Hash the password
        $hashedPassword = password_hash($staffData['password'], PASSWORD_DEFAULT);
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssss", 
                $staffData['id_number'],
                $hashedPassword,
                $staffData['first_name'],
                $staffData['last_name'],
                $staffData['role'],
                $staffData['email'],
                $staffData['phone']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                return [
                    'success' => true,
                    'id' => mysqli_insert_id($this->link)
                ];
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return [
            'success' => false,
            'error' => 'Failed to create staff account.'
        ];
    }
    

    /**
     * Get current room status with appointments
     * @return array Array of rooms with their current status
     */
    public function getRoomStatus($dayName = null) {
        // Initialize 4 rooms (Clinic A to D)
        $rooms = [];
        $roomNames = ['Clinic A', 'Clinic B', 'Clinic C', 'Clinic D'];
        
        // Create room entries with default values
        foreach ($roomNames as $index => $name) {
            $rooms[] = [
                'name' => $name,
                'status' => 'Free',
                'patient' => '',
                'therapist' => '',
                'end_time' => null,
                'appointment_status' => null,
                'appointment_number' => '',
                'next_appointment' => null
            ];
        }
        
        // If no day provided, use current day
        if ($dayName === null) {
            $dayName = date('l');
        }
        
        // Get the date for the selected day of the week
        $date = new DateTime();
        $date->modify('this ' . $dayName);
        $dateStr = $date->format('Y-m-d');
        
        // Get current time in MySQL format
        $now = date('Y-m-d H:i:s');
        
        // Get appointments with their schedule information
        $sql = "SELECT 
                    a.id as appointment_id,
                    a.room_id,
                    CONCAT(p.FN, ' ', p.LN) as patient_name,
                    CONCAT(d.FN, ' ', d.LN) as doctor_name,
                    a.appointment_time as start_time,
                    -- Get the end time from the doctor's schedule
                    COALESCE(
                        (SELECT CONCAT(DATE(a.appointment_time), ' ', ds.end_time)
                         FROM doctor_schedule ds
                         WHERE ds.doctor_id = d.id
                         AND ds.day_of_week = DAYNAME(a.appointment_time)
                         AND ds.start_time <= TIME(a.appointment_time)
                         AND ds.end_time > TIME(a.appointment_time)
                         AND ds.availability = 'Unavailable'
                         LIMIT 1),
                        -- If no schedule found, use default duration
                        ADDTIME(a.appointment_time, SEC_TO_TIME(COALESCE(d.appointment_duration, 15) * 60))
                    ) as end_time,
                    a.status as appointment_status,
                    -- Calculate duration in minutes
                    COALESCE(
                        (SELECT TIMESTAMPDIFF(MINUTE, 
                            CONCAT(DATE(a.appointment_time), ' ', ds2.start_time),
                            CONCAT(DATE(a.appointment_time), ' ', ds2.end_time))
                         FROM doctor_schedule ds2
                         WHERE ds2.doctor_id = d.id
                         AND ds2.day_of_week = DAYNAME(a.appointment_time)
                         AND ds2.start_time <= TIME(a.appointment_time)
                         AND ds2.end_time > TIME(a.appointment_time)
                         AND ds2.availability = 'Unavailable'
                         LIMIT 1),
                        COALESCE(d.appointment_duration, 15)
                    ) as duration_minutes,
                    r.room_name,
                    a.appointment_number
                FROM appointment a
                LEFT JOIN patient p ON a.patient_id = p.id
                LEFT JOIN doctor d ON a.doctor_id = d.id
                LEFT JOIN rooms r ON a.room_id = r.id
                WHERE DATE(a.appointment_time) = ?
                AND a.status = 'Scheduled'
                AND r.room_name IN ('Clinic A', 'Clinic B', 'Clinic C', 'Clinic D')
                ORDER BY a.appointment_time ASC";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 's', $dateStr);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Group appointments by room
        $appointmentsByRoom = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $roomName = $row['room_name'];
            if (!isset($appointmentsByRoom[$roomName])) {
                $appointmentsByRoom[$roomName] = [];
            }
            $appointmentsByRoom[$roomName][] = $row;
        }
        
        // Process each room's appointments
        foreach ($rooms as &$room) {
            $roomName = $room['name'];
            
            if (empty($appointmentsByRoom[$roomName])) {
                continue;
            }
            
            $appointments = $appointmentsByRoom[$roomName];
            $currentTime = time();
            $foundCurrent = false;
            
            foreach ($appointments as $appt) {
                // Debug: Log raw values from database
                error_log("=== Raw Database Values ===");
                error_log("start_time: " . $appt['start_time']);
                error_log("end_time: " . ($appt['end_time'] ?? 'NOT SET'));
                
                // Get the appointment date from the appointment_time
                $appointmentDate = date('Y-m-d', strtotime($appt['start_time']));
                
                // Create a full datetime string for the start time
                $startTime = strtotime($appointmentDate . ' ' . $appt['start_time']);
                
                // Get the end time from the calculated end_time
                $endTime = strtotime($appointmentDate . ' ' . $appt['end_time']);
                
                // Debug: Log the exact times being used
                error_log("=== Time Calculations ===");
                error_log("Appointment time: " . $appt['start_time']);
                error_log("Calculated end time: " . $appt['end_time']);
                error_log("Using date: " . $appointmentDate);
                error_log("Formatted start time: " . date('Y-m-d H:i:s', $startTime));
                error_log("Formatted end time: " . date('Y-m-d H:i:s', $endTime));
                error_log("Current time: " . date('Y-m-d H:i:s', $currentTime));
                
                // Calculate total duration of the slot in seconds
                $totalDuration = $endTime - $startTime;
                
                // Debug output
                error_log("=== Final Calculation ===");
                error_log("Appointment: " . date('Y-m-d H:i:s', $startTime) . 
                        " to " . date('Y-m-d H:i:s', $endTime) . 
                        " (duration: " . $totalDuration . "s / " . round($totalDuration/60, 2) . " minutes)");
                
                // If this appointment is in progress
                if ($currentTime >= $startTime && $currentTime <= $endTime) {
                    $room['status'] = 'Occupied';
                    $room['patient'] = $appt['patient_name'];
                    $room['therapist'] = $appt['doctor_name'];
                    $room['end_time'] = $endTime;
                    $room['appointment_status'] = $appt['appointment_status'];
                    $room['appointment_number'] = $appt['appointment_number'];
                    $foundCurrent = true;
                    break;
                }
            }
            
            // If no current appointment, find the next one
            if (!$foundCurrent) {
                foreach ($appointments as $appt) {
                    $startTime = strtotime($appt['appointment_time']);
                    if ($startTime > $currentTime) {
                        $room['next_appointment'] = [
                            'appointment_number' => $appt['appointment_number'],
                            'patient' => $appt['patient_name'],
                            'therapist' => $appt['doctor_name'],
                            'start_time' => $startTime,
                            'end_time' => $startTime + (!empty($appt['duration_minutes']) ? (int)$appt['duration_minutes'] * 60 : 1800)
                        ];
                        break;
                    }
                }
            }
        }
        
        return $rooms;
    }
    
    public function login($idNumber, $password) {
        $sql = "SELECT id, ID_NUMBER, password, FN as first_name, LN as last_name, 
                       role, email, phone, flag_login 
                FROM staff 
                WHERE ID_NUMBER = ?";
        
        $stmt = mysqli_prepare($this->link, $sql);
    
        if (!$stmt) {
            return ['success' => false, 'error' => 'Database error.'];
        }
    
        mysqli_stmt_bind_param($stmt, "s", $idNumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $staff = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    
        if (!$staff) {
            return ['success' => false, 'error' => 'Staff not found.'];
        }
    
        // Check if password matches hashed password or plain text (for testing)
        if (!password_verify($password, $staff['password']) && $staff['password'] !== $password) {
            return ['success' => false, 'error' => 'Incorrect password.'];
        }
        
        // If password was in plain text, hash it for security
        if ($staff['password'] === $password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_prepare($this->link, "UPDATE staff SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $hashedPassword, $staff['id']);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
        
        // Check if already logged in elsewhere
        /*if ($staff['flag_login'] == 1) {
            return [
                'success' => false,
                'error' => 'This account is already logged in elsewhere.'
            ];
        }*/
        
        // Update login status to logged in
        $sql = "UPDATE staff SET flag_login = 1 WHERE id = ?";
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $staff['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Remove sensitive data before returning
        unset($staff['password']);
        
        return [
            'success' => true,
            'staff' => $staff
        ];
    }
    
    public function logout($staffId) {
        $sql = "UPDATE staff SET flag_login = 0 WHERE id = ?";
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $staffId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return true;
        }    
        return false;
    }
    
    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}