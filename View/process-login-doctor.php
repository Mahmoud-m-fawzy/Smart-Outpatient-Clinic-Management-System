<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log POST data
error_log('=== Doctor Login Attempt ===');
error_log('POST data: ' . print_r($_POST, true));

try {
    // Include necessary files
    require_once("../Controller/DoctorController.php");
    require_once("../Model/Database.php");
    
    // Verify DoctorController exists and is callable
    if (!class_exists('DoctorController')) {
        throw new Exception('DoctorController class not found');
    }

    // Initialize variables
    $errors = [];
    $data = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $idNumber = trim($_POST["id_number"] ?? "");
        $password = trim($_POST["password"] ?? "");

        error_log("Attempting login for ID: $idNumber");

        // Validate ID Number
        if (empty($idNumber)) {
            $errors["id_number"] = "Please enter your ID Number.";
            error_log("ID Number is empty");
        }

        // Validate password
        if (empty($password)) {
            $errors["password"] = "Please enter your password.";
            error_log("Password is empty");
        }

        // If no validation errors, proceed with login
        if (empty($errors)) {
            try {
                $doctorController = new DoctorController();
                error_log("Calling login method with ID: $idNumber");
                $result = $doctorController->login($idNumber, $password);
                error_log("Login result: " . print_r($result, true));

                if ($result['success']) {
                    // Login successful
                    error_log("Login successful for user ID: " . $result['user']['id']);
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['FN'] = $result['user']['FN'] ?? '';
                    $_SESSION['LN'] = $result['user']['LN'] ?? '';
                    $_SESSION['ID_NUMBER'] = $result['user']['ID_NUMBER'] ?? $idNumber;
                    $_SESSION['login_type'] = 'doctor';
                    
                    error_log("Session data after login: " . print_r($_SESSION, true));
                    
                    // Redirect to doctor dashboard
                    header("Location: doctor_dashboard.php");
                    exit();
                } else {
                    // Login failed
                    $errorMsg = $result['error'] ?? 'Invalid ID Number or password';
                    $errors['general'] = $errorMsg;
                    $data['id_number'] = $idNumber; // Keep the ID number in the form
                    error_log("Login failed: $errorMsg");
                }
            } catch (Exception $e) {
                $errorMsg = 'An error occurred during login.';
                $errors['general'] = $errorMsg;
                error_log("Exception during login: " . $e->getMessage());
            }
        }
    } else {
        error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
        $errors['general'] = 'Invalid request method.';
    }
    
    // Store errors and data in session
    $_SESSION['errors'] = $errors;
    $_SESSION['data'] = $data;
    
    error_log("Errors: " . print_r($errors, true));
    error_log("Session data before redirect: " . print_r($_SESSION, true));
    
} catch (Exception $e) {
    error_log("Fatal error in process-login-doctor.php: " . $e->getMessage());
    $_SESSION['errors'] = ['general' => 'A system error occurred. Please try again.'];
}

// Redirect back to login page with errors
header("Location: doctor_login.php");
exit();
