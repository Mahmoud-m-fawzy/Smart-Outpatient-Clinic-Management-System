<?php

class AuthController {
    private $db;

    public function __construct() {
        // Initialize database connection
        $this->db = new Database();
    }

    public function verifyId() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit;
        }

        $idNumber = $_POST['idnumber'] ?? '';
        $errors = [];
        $data = ['idnumber' => $idNumber];

        if (empty($idNumber)) {
            $errors['idnumber'] = 'ID number is required';
            $this->renderForgotPassword($errors, $data);
            return;
        }

        try {
            // Search in both idnumber and NN columns
            $query = "SELECT * FROM users WHERE idnumber = ? OR NN = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$idNumber, $idNumber]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Pass the user data to the view
                $this->renderForgotPassword([], $data, $user);
            } else {
                $errors['idnumber'] = 'Invalid ID number';
                $this->renderForgotPassword($errors, $data);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $errors['general'] = 'An error occurred';
            $this->renderForgotPassword($errors, $data);
        }
    }

    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit;
        }

        $idNumber = $_POST['idnumber'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        $data = ['idnumber' => $idNumber];

        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
            $this->renderForgotPassword($errors, $data);
            return;
        }

        try {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in both idnumber and NN columns
            $query = "UPDATE users SET password = ? WHERE idnumber = ? OR NN = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$hashedPassword, $idNumber, $idNumber]);

            if ($result) {
                // Redirect to login page with success message
                $_SESSION['success_message'] = 'Password updated successfully';
                header('Location: /login');
                exit;
            } else {
                $errors['general'] = 'Failed to update password';
                $this->renderForgotPassword($errors, $data);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $errors['general'] = 'An error occurred';
            $this->renderForgotPassword($errors, $data);
        }
    }

    private function renderForgotPassword($errors = [], $data = [], $user = null) {
        // Include the view file with the data
        require_once 'View/forgot-password.php';
    }
} 