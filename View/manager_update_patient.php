<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary controller
require_once __DIR__ . '/../Controller/ManagerController.php';

// Check if user is logged in as manager
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
    header("Location: /MVC/View/manager_login.php");
    exit();
}

// Check if patient ID is provided
if (!isset($_GET['id'])) {
    header("Location: manger_search_patient.php");
    exit();
}

$patient_id = (int)$_GET['id'];
$error = null;
$success = null;

// Get patient data using the controller
$manager = new Manager();
$patientData = $manager->searchPatientById($patient_id);

if (!$patientData) {
    $_SESSION['error'] = "Patient not found";
    header("Location: manger_search_patient.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the patient ID in the POST data
    $_POST['id'] = $patient_id;
    
    // Process the update through the controller
    $response = ManagerController::updatePatient();
    
    if ($response['success']) {
        $success = $response['message'];
        // Update patient data with the returned data
        $patientData = $response['patient'];
    } else {
        $error = $response['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Patient Information - Medical Center</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/manger_update_patient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <main class="update-patient-container">
        <div class="update-section">
            <div class="header-actions">
                <h1>Update Patient Information</h1>
                <a href="manager_home.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="update-form-container">
                <form method="POST" action="" class="update-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($patientData['FN']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name"
                                value="<?php echo htmlspecialchars($patientData['LN']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($patientData['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($patientData['phone']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age"
                                value="<?php echo htmlspecialchars($patientData['age']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="Male" <?php echo $patientData['gender'] === 'Male' ? 'selected' : ''; ?>>
                                    Male</option>
                                <option value="Female" <?php echo $patientData['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address"
                            value="<?php echo htmlspecialchars($patientData['address']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="job">Job:</label>
                            <input type="text" id="job" name="job"
                                value="<?php echo htmlspecialchars($patientData['job']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="marital">Marital Status:</label>
                            <select id="marital" name="marital" required>
                                <option value="Single" <?php echo $patientData['marital'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo $patientData['marital'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo $patientData['marital'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo $patientData['marital'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="update-btn">
                            <i class="fas fa-save"></i> Update Information
                        </button>
                        <a href="search_patient.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i> Back to Search
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

</html>