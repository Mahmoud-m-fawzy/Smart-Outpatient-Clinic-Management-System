<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Include the model class
require_once("D:\wamp64\www\MVC\Model\Patient.php");
require_once("D:\wamp64\www\MVC\Model\Database.php");

// Get the form data
$data = [
    'FN' => $_POST['FN'] ?? '',
    'LN' => $_POST['LN'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'password' => $_POST['password'] ?? '',
    'plain_password' => $_POST['password'] ?? '', // Store the original password
    'confirmPassword' => $_POST['confirmPassword'] ?? '',
    'age' => $_POST['age'] ?? '',
    'idType' => $_POST['idType'] ?? '',
    'idInput' => $_POST['idInput'] ?? '',
    'address' => $_POST['address'] ?? '',
    'job' => $_POST['job'] ?? '',
    'gender' => $_POST['gender'] ?? '',
    'marital' => $_POST['marital'] ?? ''
];


// Set the appropriate ID field based on selection
if ($data['idType'] === 'id') {
    $data['idnumber'] = $data['idInput'];
    $data['NN'] = null;
} else {
    $data['NN'] = $data['idInput'];
    $data['idnumber'] = null;
}

// Check if address and job are empty
if (empty($data['address'])) {
    $data['address'] = null;
} else {
    $data['address'] = trim($data['address']);
}

if (empty($data['job'])) {
    $data['job'] = null;
} else {
    $data['job'] = trim($data['job']);
}

// Create patient object and call the register method
$patient = new Patient();
$success = $patient->register(
    $data['FN'], $data['LN'], $data['email'], $data['phone'],
    $data['password'], $data['password'], $data['age'], $data['idnumber'], $data['NN'],
    $data['address'], $data['job'], $data['gender'], $data['marital']
);


// Handle the result
if ($success) {
    header('Location: /MVC/View/login.php');
    exit();
} else {
    $_SESSION['errors'] = ['Registration failed. Please try again.'];
    $_SESSION['data'] = $data;
    header('Location: /MVC/View/registration.php');
    exit();
}
?>
