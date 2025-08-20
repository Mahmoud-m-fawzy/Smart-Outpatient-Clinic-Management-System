<?php
require_once("Database.php");

class Manager {
    private $db;
    private $link;

    public function __construct() {
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
    }

    /**
     * Get total number of active staff members (non-doctors)
     * @return int
     */
    public function getTotalStaff() {
        $sql = "SELECT COUNT(*) as count FROM staff";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }

    /**
     * Get total number of active doctors
     * @return int
     */
    public function getTotalDoctors() {
        $sql = "SELECT COUNT(*) as count FROM doctor";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }

    /**
     * Get total number of active patients
     * @return int
     */
    public function getTotalPatients() {
        $sql = "SELECT COUNT(*) as count FROM patient";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }
    
    /**
     * Get database connection link
     * @return mysqli Database connection
     */
    public function getLink() {
        return $this->link;
    }
    
    /**
     * Get total number of appointments
     * @return int
     */
    public function getTotalAppointments() {
        $sql = "SELECT COUNT(*) as count FROM appointment";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }
    
    /**
     * Get number of completed appointments
     * @return int
     */
    public function getCompletedAppointments() {
        $sql = "SELECT COUNT(*) as count FROM appointment WHERE status = 'completed'";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }
    
    /**
     * Get number of pending appointments
     * @return int
     */
    public function getPendingAppointments() {
        $sql = "SELECT COUNT(*) as count FROM appointment WHERE status = 'pending'";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }
    
    /**
     * Get number of cancelled appointments
     * @return int
     */
    public function getCancelledAppointments() {
        $sql = "SELECT COUNT(*) as count FROM appointment WHERE status = 'cancelled'";
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (int)$row['count'] : 0;
        }
        return 0;
    }
    
    /**
     * Get total revenue from completed appointments
     * @return float
     */
    public function getTotalRevenue() {
        // First check if the service table exists
        $tableCheck = mysqli_query($this->link, "SHOW TABLES LIKE 'service'");
        if (mysqli_num_rows($tableCheck) === 0) {
            // If service table doesn't exist, check for a fee column in the appointment table
            $columnCheck = mysqli_query($this->link, "SHOW COLUMNS FROM `appointment` LIKE 'fee'");
            if (mysqli_num_rows($columnCheck) > 0) {
                // If fee column exists in appointment table, use that
                $sql = "SELECT COALESCE(SUM(fee), 0) as total 
                        FROM appointment 
                        WHERE status = 'completed'";
            } else {
                // If no fee information is available, return 0
                return 0.0;
            }
        } else {
            // Service table exists, use the original query
            $sql = "SELECT COALESCE(SUM(s.fee), 0) as total 
                    FROM appointment a
                    JOIN service s ON a.service_id = s.service_id
                    WHERE a.status = 'completed'";
        }
        
        $result = mysqli_query($this->link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? (float)$row['total'] : 0.0;
        }
        return 0.0;
    }

    /**
     * Handle manager login
     */
    public function login($username, $password) {
        $sql = "SELECT id, username, password, first_name, last_name, email, phone, flag_login 
                FROM manager 
                WHERE username = ?";
        
        $stmt = mysqli_prepare($this->link, $sql);
    
        if (!$stmt) {
            return ['success' => false, 'error' => 'Database error.'];
        }
    
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $manager = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    
        if (!$manager) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }
    
        // Verify password
        if (!password_verify($password, $manager['password']) && $manager['password'] !== $password) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }
        
        // If password was in plain text, hash it for security
        if ($manager['password'] === $password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_prepare($this->link, "UPDATE manager SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $hashedPassword, $manager['id']);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
        
        // Update login status to logged in
        $this->updateLoginStatus($manager['id'], 1);
        
        // Remove sensitive data before returning
        unset($manager['password']);
        
        return [
            'success' => true,
            'manager' => $manager
        ];
    }

    private function updateLoginStatus($id, $status) {
        $sql = "UPDATE manager SET flag_login = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . mysqli_error($this->link));
            return false;
        }
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $result = mysqli_stmt_execute($stmt);
        if ($result === false) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }

    public function logout($id) {
        return $this->updateLoginStatus($id, 0);
    }

    /**
     * Check if a manager is logged in (session-based)
     * @return bool True if manager is logged in, false otherwise
     */
    public static function isManagerLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_type']) && 
               $_SESSION['user_type'] === 'manager' &&
               !empty($_SESSION['user']['id']);
    }
    
    /**
     * Get dashboard statistics
     * @return array Dashboard statistics
     */
    public function getDashboardStats() {
        return [
            'success' => true,
            'stats' => [
                'total_patients' => $this->getTotalPatients(),
                'total_staff' => $this->getTotalStaff(),
                'total_doctors' => $this->getTotalDoctors(),
                'total_appointments' => $this->getTotalAppointments(),
                'today_appointments' => $this->getTotalAppointments(true)
            ]
        ];
    }
    
    public function getConnection() {
        return $this->link;
    }
    
    /**
     * Search for a patient by ID or National Number
     * @param string $id The ID or National Number to search for
     * @return array|null The patient data if found, null otherwise
     */
    public function searchPatientById($id)
    {
        // First try to get by ID
        $sql = "SELECT * FROM patient WHERE id = ?";

        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        // If not found by ID, try by ID number or National Number
        $sql = "SELECT * FROM patient WHERE idnumber = ? OR NN = ?";

        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $id, $id);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        return null;
    }

    /**
     * Search for a patient by phone number
     * @param string $phone The phone number to search for
     * @return array|null The patient data if found, null otherwise
     */
    public function searchPatientByPhone($phone)
    {
        // Clean the phone number (remove any non-numeric characters)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // First try exact match
        $sql = "SELECT * FROM patient WHERE phone = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $phone);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        // If not found, try with different phone number formats
        $sql = "SELECT * FROM patient WHERE REPLACE(REPLACE(REPLACE(phone, ' ', ''), '(', ''), ')', '') = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $phone);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    mysqli_stmt_close($stmt);
                    return $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        return null;
    }
    
    /**
     * Update patient information
     * @param int $id Patient ID
     * @param array $data Associative array of fields to update
     * @return bool True on success, false on failure
     */
    public function updatePatient($id, $data)
    {
        $allowedFields = [
            'FN',
            'LN',
            'email',
            'phone',
            'age',
            'address',
            'job',
            'gender',
            'marital'
        ];

        $updates = [];
        $types = "";
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = ?";
                $values[] = $value;
                $types .= "s"; // Assuming all fields are strings
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE patient SET " . implode(', ', $updates) . " WHERE id = ?";
        $values[] = $id;
        $types .= "i"; // id is integer

        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>
