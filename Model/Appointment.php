<?php
require_once("Database.php");

class Appointment {
    private $db;
    private $link;

    public function __construct() {
        try {
            $this->db = new Database();
            $this->link = $this->db->connectToDB();
        } catch (Exception $e) {
            error_log("Failed to initialize Appointment model: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all appointments with optional filters
     * @param array $filters Optional filters (date, status, doctor_id, patient_id)
     * @return array Array of appointments
     */
    public function getAllAppointments($filters = []) {
        $sql = "SELECT a.*, 
                       d.FN as doctor_first_name, d.LN as doctor_last_name,
                       p.FN as patient_first_name, p.LN as patient_last_name
                FROM appointment a
                LEFT JOIN doctor d ON a.doctor_id = d.id
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Add filters
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(a.appointment_time) = ?";
            $params[] = $filters['date'];
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (!empty($filters['doctor_id'])) {
            $sql .= " AND a.doctor_id = ?";
            $params[] = $filters['doctor_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['patient_id'])) {
            $sql .= " AND a.patient_id = ?";
            $params[] = $filters['patient_id'];
            $types .= 'i';
        }
        
        $sql .= " ORDER BY a.appointment_time DESC";
        
        $stmt = mysqli_prepare($this->link, $sql);
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $appointments;
    }
    
    /**
     * Get appointment by ID
     * @param int $id Appointment ID
     * @return array|null Appointment data or null if not found
     */
    public function getAppointmentById($id) {
        $sql = "SELECT a.*, 
                       d.FN as doctor_first_name, d.LN as doctor_last_name,
                       p.FN as patient_first_name, p.LN as patient_last_name
                FROM appointment a
                LEFT JOIN doctor d ON a.doctor_id = d.id
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE a.id = ?";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $appointment = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $appointment ?: null;
    }
    
    /**
     * Update appointment status
     * @param int $id Appointment ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE appointment SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Create a new appointment
     * @param array $data Appointment data
     * @return int|false New appointment ID or false on failure
     */
    public function createAppointment($data) {
        $sql = "INSERT INTO appointment (patient_id, doctor_id, appointment_time, status, notes, created_at) 
                VALUES (?, ?, ?, 'Scheduled', ?, NOW())";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            'iiss', 
            $data['patient_id'], 
            $data['doctor_id'], 
            $data['appointment_time'],
            $data['notes'] ?? ''
        );
        
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            $appointmentId = mysqli_insert_id($this->link);
            mysqli_stmt_close($stmt);
            return $appointmentId;
        }
        
        mysqli_stmt_close($stmt);
        return false;
    }
    
    /**
     * Delete an appointment
     * @param int $id Appointment ID
     * @return bool True on success, false on failure
     */
    public function deleteAppointment($id) {
        $sql = "DELETE FROM appointment WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Get appointments by date range
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Array of appointments
     */
    public function getAppointmentsByDateRange($startDate, $endDate) {
        $sql = "SELECT a.*, 
                       d.FN as doctor_first_name, d.LN as doctor_last_name,
                       p.FN as patient_first_name, p.LN as patient_last_name
                FROM appointment a
                LEFT JOIN doctor d ON a.doctor_id = d.id
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE DATE(a.appointment_time) BETWEEN ? AND ?
                ORDER BY a.appointment_time ASC";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $appointments;
    }
    
    /**
     * Get appointments by doctor and date
     * @param int $doctorId Doctor ID
     * @param string $date Date (Y-m-d)
     * @return array Array of appointments
     */
    public function getAppointmentsByDoctorAndDate($doctorId, $date) {
        $sql = "SELECT a.*, 
                       p.FN as patient_first_name, p.LN as patient_last_name
                FROM appointment a
                LEFT JOIN patient p ON a.patient_id = p.id
                WHERE a.doctor_id = ? AND DATE(a.appointment_time) = ?
                ORDER BY a.appointment_time ASC";
                
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $doctorId, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $appointments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $appointments;
    }
    
    /**
     * Generate a daily report of appointments
     * @param string $date Date in Y-m-d format
     * @return array Report data including appointment details and statistics
     */
    public function getDailyReport($date) {
        try {
            // Get all appointments for the given date
            $sql = "SELECT 
                        a.*, 
                        d.FN as doctor_first_name, 
                        d.LN as doctor_last_name,
                        p.FN as patient_first_name, 
                        p.LN as patient_last_name,
                        p.phone as patient_phone,
                        p.email as patient_email
                    FROM appointment a
                    LEFT JOIN doctor d ON a.doctor_id = d.id
                    LEFT JOIN patient p ON a.patient_id = p.id
                    WHERE DATE(a.appointment_time) = ?
                    ORDER BY a.appointment_time ASC";
            
            $stmt = mysqli_prepare($this->link, $sql);
            mysqli_stmt_bind_param($stmt, 's', $date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $appointments = [];
            $stats = [
                'total' => 0,
                'completed' => 0,
                'scheduled' => 0,
                'cancelled' => 0,
                'no_show' => 0,
                'by_doctor' => []
            ];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $appointments[] = $row;
                $stats['total']++;
                
                // Update status counts
                $status = strtolower($row['status']);
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                
                // Group by doctor
                $doctorId = $row['doctor_id'];
                if (!isset($stats['by_doctor'][$doctorId])) {
                    $stats['by_doctor'][$doctorId] = [
                        'name' => trim($row['doctor_first_name'] . ' ' . $row['doctor_last_name']),
                        'count' => 0
                    ];
                }
                $stats['by_doctor'][$doctorId]['count']++;
            }
            
            mysqli_stmt_close($stmt);
            
            return [
                'success' => true,
                'date' => $date,
                'appointments' => $appointments,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Error in getDailyReport: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to generate daily report: ' . $e->getMessage()
            ];
        }
    }
    
    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}
