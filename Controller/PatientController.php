<?php
// Define variables and initialize with empty values
$FN = $LN = $email = $phone = $password = $confirm_password = $age = $idnumber = $NN = $address = $job = $gender = $marital = "";
$FN_err = $LN_err = $email_err = $phone_err = $password_err = $confirm_password_err = $age_err = $idnumber_err = $NN_err = $address_err = $job_err = $gender_err = $marital_err = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate First Name
    $input_FN = trim($_POST["FN"]);
    if (empty($input_FN)) {
        $FN_err = "Please enter first name.";
    } elseif (!filter_var($input_FN, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
        $FN_err = "Please enter a valid first name.";
    } else {
        $FN = $input_FN;
    }

    // Validate Last Name
    $input_LN = trim($_POST["LN"]);
    if (empty($input_LN)) {
        $LN_err = "Please enter last name.";
    } elseif (!filter_var($input_LN, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
        $LN_err = "Please enter a valid last name.";
    } else {
        $LN = $input_LN;
    }

    // Validate Email
    $input_email = trim($_POST["email"]);
    if (empty($input_email)) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email.";
    } else {
        $email = $input_email;
    }

    // Validate Phone
    $input_phone = trim($_POST["phone"]);
    if (empty($input_phone)) {
        $phone_err = "Please enter a phone number.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $input_phone)) {
        $phone_err = "Please enter a valid phone number.";
    } else {
        $phone = $input_phone;
    }

    // Validate Password
    $input_password = trim($_POST["password"]);
    if (empty($input_password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($input_password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = $input_password;
    }

    // Validate Confirm Password
    $input_confirm_password = trim($_POST["confirmPassword"]);
    if (empty($input_confirm_password)) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = $input_confirm_password;
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Validate Age
    $input_age = trim($_POST["age"]);
    if (empty($input_age)) {
        $age_err = "Please enter age.";
    } elseif (!ctype_digit($input_age)) {
        $age_err = "Please enter a positive integer value.";
    } else {
        $age = $input_age;
    }

    $input_idnumber = isset($_POST["idnumber"]) ? trim($_POST["idnumber"]) : "";
    $input_NN = isset($_POST["NN"]) ? trim($_POST["NN"]) : "";
    
    
    if (empty($input_idnumber) && empty($input_NN)) {
        $idnumber_err = "Please enter either ID number or National number.";
        $NN_err = "Please enter either ID number or National number.";
    } else {
        if (!empty($input_idnumber)) {
            $idnumber = $input_idnumber;
            $NN = null;
        } else {
            $NN = $input_NN;
            $idnumber = null;
        }
    }

    // Validate Address
    $input_address = isset($_POST["address"]) ? trim($_POST["address"]) : "";
    if (empty($input_address)) {
        $address_err = "Please enter an address.";
    } else {
        $address = $input_address;
    }

    // Validate Job
    $input_job = isset($_POST["job"]) ? trim($_POST["job"]) : "";
    if (empty($input_job)) {
        $job_err = "Please enter job.";
    } else {
        $job = $input_job;
    }

    // Validate Gender
    if (!isset($_POST["gender"])) {
        $gender_err = "Please select gender.";
    } else {
        $gender = $_POST["gender"];
    }

    // Validate Marital Status
    if (!isset($_POST["marital"])) {
        $marital_err = "Please select marital status.";
    } else {
        $marital = $_POST["marital"];
    }

    // Check input errors before inserting in database
    if (empty($FN_err) && empty($LN_err) && empty($email_err) && empty($phone_err) && 
        empty($password_err) && empty($confirm_password_err) && empty($age_err) && 
        empty($idnumber_err) && empty($NN_err) && empty($address_err) && 
        empty($job_err) && empty($gender_err) && empty($marital_err)) {
        
        include_once '../Model/Patient.php';
        $patient = new Patient();
        
        if ($patient->register($FN, $LN, $email, $phone, $password, $age, $idnumber, 
                             $NN, $address, $job, $gender, $marital)) {
            header("location: login.php");
            exit();
        } else {
            echo "Something went wrong. Please try again later.";
        }
    }
}

// For login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $input_idnumber = trim($_POST["idnumber"]);
    $input_password = trim($_POST["password"]);

    if (empty($input_idnumber)) {
        $idnumber_err = "Please enter ID number.";
    } else {
        $idnumber = $input_idnumber;
    }

    if (empty($input_password)) {
        $password_err = "Please enter your password.";
    } else {
        $password = $input_password;
    }

    if (empty($idnumber_err) && empty($password_err)) {
        include_once '../Model/Patient.php';
        $patient = new Patient();
        
        $result = $patient->login($idnumber, $password);
        if ($result) {
            session_start();
            $_SESSION["patient_id"] = $result["id"];
            $_SESSION["patient_name"] = $result["FN"] . " " . $result["LN"];
            header("location: dashboard.php");
            exit();
        } else {
            $password_err = "Invalid ID number or password.";
        }
    }
}

class PatientController {
    public function register($data) {
        // Validate the data
        $errors = [];
        if (empty($data['FN'])) {
            $errors['FN'] = "First name is required.";
        }
        if (empty($data['LN'])) {
            $errors['LN'] = "Last name is required.";
        }
        // Add more validation as needed

        // If there are no errors, proceed with registration
        if (empty($errors)) {
            // Logic to insert the data into the database
            // For example:
            // $sql = "INSERT INTO patients (FN, LN, email, ...) VALUES (?, ?, ?, ...)";
            // $stmt = $conn->prepare($sql);
            // $stmt->bind_param("sss...", $data['FN'], $data['LN'], $data['email'], ...);
            // $stmt->execute();

            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => $errors];
        }
    }
}
?> 