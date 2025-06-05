<?php
require_once("Database.php");

class Staff {
    private $db;
    private $link;

    public function __construct() {
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
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
        if ($staff['flag_login'] == 1) {
            return [
                'success' => false,
                'error' => 'This account is already logged in elsewhere.'
            ];
        }
        
        // Update login status to logged in
        $this->updateLoginStatus($staff['id'], 1);
        
        // Remove sensitive data before returning
        unset($staff['password']);
        
        return [
            'success' => true,
            'staff' => $staff
        ];
    }
    
    public function logout($staffId) {
        return $this->updateLoginStatus($staffId, 0);
    }
    

    private function updateLoginStatus($staffId, $status) {
        $sql = "UPDATE staff SET flag_login = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($this->link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $status, $staffId);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        
        return false;
    }
    
    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}
