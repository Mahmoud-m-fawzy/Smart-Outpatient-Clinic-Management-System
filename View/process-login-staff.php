<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once("../Controller/StaffController.php");
require_once("../Model/Database.php");

try {
    // Verify StaffController exists and is callable
    if (!class_exists('StaffController')) {
        throw new Exception('StaffController class not found');
    }

    $controller = new StaffController();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $idNumber = trim($_POST["id_number"] ?? "");
        $password = $_POST["password"] ?? "";

        // Basic validation
        if (empty($idNumber) || empty($password)) {
            $_SESSION['login_error'] = 'ID Number and password are required.';
            header('Location: staff_login.php');
            exit();
        }

        // Call the login method
        $loginResult = $controller->login($idNumber, $password);

        if ($loginResult['success']) {
            // Login successful - redirect to dashboard
            header('Location: ' . ($loginResult['redirect'] ?? 'dashboard.php'));
            exit();
        } else {
            // Login failed - redirect back with error
            $_SESSION['login_error'] = $loginResult['error'] ?? 'Invalid ID Number or password.';
            $_SESSION['data'] = ['id_number' => $idNumber];
            header('Location: staff_login.php');
            exit();
        }
    } else {
        // Not a POST request - redirect to login
        header('Location: staff_login.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Staff login process error: " . $e->getMessage());
    $_SESSION['login_error'] = 'A system error occurred. Please try again later.';
    header('Location: staff_login.php');
    exit();
}
?>
