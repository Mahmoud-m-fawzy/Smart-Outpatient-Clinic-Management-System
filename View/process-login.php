<?php
session_start();
require_once("../Model/Patient.php");
require_once("../Model/Database.php");

// Initialize variables
$errors = [];
$data = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the number (could be either NN or idnumber)
    $number = trim($_POST["national_id"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Validate number
    if (empty($number)) {
        $errors["national_id"] = "Please enter your ID or National Number.";
    }

    // Validate password
    if (empty($password)) {
        $errors["password"] = "Please enter your password.";
    }

    // If no validation errors, proceed with login
    if (empty($errors)) {
        $patient = new Patient();
        $result = $patient->login($number, $password);

        if ($result['success']) {
            // Login successful
            // Set session variables
            $_SESSION['patient_id'] = $result['user']['id'];
            $_SESSION['patient_name'] = trim($result['user']['FN'] . ' ' . $result['user']['LN']);
            $_SESSION['patient_email'] = $result['user']['email'] ?? '';
            $_SESSION['patient_nn'] = $result['user']['NN'];
            $_SESSION['patient_id_number'] = $result['user']['ID'];
            
            // For backward compatibility
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['FN'] = $result['user']['FN'];
            $_SESSION['LN'] = $result['user']['LN'];
            $_SESSION['NN'] = $result['user']['NN'];
            $_SESSION['ID'] = $result['user']['ID'];
            
            $_SESSION['login_method'] = is_numeric($number) && strlen($number) > 10 ? 'ID' : 'NN';
            $_SESSION['login_number'] = $number; // Save the actual number used for login
            $_SESSION['login_type'] = $result['user']['login_type'] ?? 'patient';
            
            // Redirect to patient dashboard
            header("Location: patient_dashboard.php");
            exit();
        } else {
            // Login failed
            $errors['general'] = $result['error'];
            $data['national_id'] = $number; // Keep the number in the form
        }
    }
}

// If there are errors, store them in session and redirect back to login page
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['data'] = $data;
    header("Location: login.php");
    exit();
}
?> 