<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../Model/Patient.php");
require_once("../Model/Doctor.php");
require_once("../Model/Database.php");

class BookingController {
    private $patient;
    private $db;
    private $link;

    public function __construct() {
        $this->patient = new Patient();
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            return;
        }

        $action = $_GET['action'] ?? '';
        
        if ($action === 'book') {
            $this->handleBooking();
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    }

    private function handleBooking() {
        header('Content-Type: application/json');
        
        try {
            // Log received data for debugging
            error_log('Received POST data: ' . print_r($_POST, true));
            error_log('Session data: ' . print_r($_SESSION, true));
            
            // Validate input
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $doctor_id = $_POST['doctor_id'] ?? null;
            $appointment_date = $_POST['appointment_date'] ?? null;
            $time_slot = $_POST['appointment_time'] ?? null;  // Changed from time_slot to appointment_time
            $day_of_week = $_POST['day_of_week'] ?? null;
            $location = $_POST['location'] ?? 'Not specified';
            $notes = $_POST['notes'] ?? 'No notes';
            $patient_id = $_SESSION['user_id'] ?? null;

            error_log("Validating fields - doctor_id: $doctor_id, appointment_date: $appointment_date, time_slot: $time_slot, day_of_week: $day_of_week, patient_id: $patient_id");

            if (!$doctor_id || !$appointment_date || !$time_slot || !$day_of_week || !$patient_id) {
                throw new Exception('Missing required fields');
            }

            // Start transaction
            mysqli_begin_transaction($this->link);

            // 1. Check if the slot is still available
            $checkSql = "SELECT id, availability, start_time, end_time FROM doctor_schedule 
                       WHERE doctor_id = ? 
                       AND day_of_week = ? 
                       AND start_time <= ? 
                       AND end_time >= ?";
            
            $checkStmt = mysqli_prepare($this->link, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "isss", $doctor_id, $day_of_week, $time_slot, $time_slot);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($result) === 0) {
                // Log the details of why no slot was found
                error_log("No matching schedule found for doctor_id: $doctor_id, day: $day_of_week, time: $time_slot");
                
                // Check if there are any schedules for this doctor and day (for debugging)
                $debugSql = "SELECT * FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?";
                $debugStmt = mysqli_prepare($this->link, $debugSql);
                mysqli_stmt_bind_param($debugStmt, "is", $doctor_id, $day_of_week);
                mysqli_stmt_execute($debugStmt);
                $debugResult = mysqli_stmt_get_result($debugStmt);
                
                if (mysqli_num_rows($debugResult) > 0) {
                    $slots = [];
                    while ($row = mysqli_fetch_assoc($debugResult)) {
                        $slots[] = "ID: {$row['id']}, Start: {$row['start_time']}, End: {$row['end_time']}, Available: {$row['availability']}";
                    }
                    error_log("Available schedules for this doctor/day: " . implode(" | ", $slots));
                } else {
                    error_log("No schedules found for doctor_id: $doctor_id on $day_of_week");
                }
                mysqli_stmt_close($debugStmt);
                
                throw new Exception('This time slot is no longer available or not scheduled');
            }
            
            // Check if the slot is available
            $slot = mysqli_fetch_assoc($result);
            if ($slot['availability'] !== 'Available') {
                throw new Exception('This time slot is already booked');
            }
            mysqli_stmt_close($checkStmt);
            
            // 2. Create the appointment
            $apptSql = "INSERT INTO appointment 
                      (patient_id, doctor_id, appointment_time, status, day_of_week, location, notes) 
                      VALUES (?, ?, ?, 'Scheduled', ?, ?, ?)";
            $apptStmt = mysqli_prepare($this->link, $apptSql);
            mysqli_stmt_bind_param($apptStmt, "iissss", 
                $patient_id, 
                $doctor_id, 
                $time_slot, 
                $day_of_week,
                $location,
                $notes
            );
            
            if (!mysqli_stmt_execute($apptStmt)) {
                throw new Exception('Failed to create appointment: ' . mysqli_error($this->link));
            }
            $appointment_id = mysqli_insert_id($this->link);
            mysqli_stmt_close($apptStmt);
            
            // 3. Update the slot availability
            $updateSql = "UPDATE doctor_schedule 
                         SET availability = 'Unavailable' 
                         WHERE doctor_id = ? 
                         AND day_of_week = ? 
                         AND start_time <= ? 
                         AND end_time >= ?";
            
            $updateStmt = mysqli_prepare($this->link, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "isss", $doctor_id, $day_of_week, $time_slot, $time_slot);
            
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception('Failed to update schedule');
            }
            
            // Commit transaction
            mysqli_commit($this->link);
            
            echo json_encode([
                'success' => true, 
                'appointment_id' => $appointment_id,
                'message' => 'Appointment booked successfully!'
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($this->link)) {
                mysqli_rollback($this->link);
            }
            
            error_log('Booking error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }

    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}

// Handle the request
if (isset($_GET['action'])) {
    $controller = new BookingController();
    $controller->handleRequest();
}
