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

// Initialize variables
$searchResult = [];
$error = null;
$success = null;

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an update request
    if (isset($_POST['id'])) {
        // This is an update request
        $response = ManagerController::updatePatient();
        if ($response['success']) {
            $success = $response['message'];
            // Update the displayed data with the new values
            $searchResult = $response['patient'];
        } else {
            $error = $response['error'];
        }
    } else {
        // This is a search request
        $response = ManagerController::searchPatient();
        if ($response['success']) {
            $searchResult = $response['patient'];
            $success = 'Patient found successfully!';
        } else {
            $error = $response['error'];
        }
    }

    if (empty($searchResult)) {
        $error = "No patient found with the provided information";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Patient - Medical Center</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/manger_search_patient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <main class="search-patient-container">
        <div class="search-section">
            <div class="header-actions">
                <h1>Search Patient</h1>
                <a href="manager_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="search-form-container">
                <form method="POST" action="" class="search-form">
                    <div class="form-group">
                        <label for="search_type">Search By:</label>
                        <select name="search_type" id="search_type" required>
                            <option value="id">Patient ID</option>
                            <option value="phone">Phone Number</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="search_value">Enter Search Value:</label>
                        <input type="text" name="search_value" id="search_value"
                            placeholder="Enter patient ID or phone number" required>
                    </div>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($searchResult): ?>
                <div class="search-results">
                    <h2>Patient Information</h2>
                    <div class="patient-card">
                        <div class="patient-info">
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($searchResult['id']); ?></p>
                            <p><strong>Name:</strong>
                                <?php echo htmlspecialchars($searchResult['FN'] . ' ' . $searchResult['LN']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($searchResult['phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($searchResult['email']); ?></p>
                            <p><strong>Age:</strong> <?php echo htmlspecialchars($searchResult['age']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($searchResult['gender']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($searchResult['address']); ?></p>
                            <p><strong>Job:</strong> <?php echo htmlspecialchars($searchResult['job']); ?></p>
                            <p><strong>Marital Status:</strong> <?php echo htmlspecialchars($searchResult['marital']); ?>
                            </p>
                        </div>
                        <div class="patient-actions">
                        <button type="button" class="update-btn" onclick="toggleEditForm()">
                            <i class="fas fa-edit"></i> Update Information
                        </button>
                        <a href="patient_records.php?id=<?php echo $searchResult['id']; ?>" class="records-btn">
                            <i class="fas fa-file-medical"></i> View Records
                        </a>
                    </div>
                    
                    <!-- Update Form (initially hidden) -->
                    <div id="updateForm" style="display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3>Update Patient Information</h3>
                        <form method="POST" action="" class="update-form">
                            <input type="hidden" name="id" value="<?php echo $searchResult['id']; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name:</label>
                                    <input type="text" name="FN" value="<?php echo htmlspecialchars($searchResult['FN']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name:</label>
                                    <input type="text" name="LN" value="<?php echo htmlspecialchars($searchResult['LN']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($searchResult['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone:</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($searchResult['phone']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Age:</label>
                                    <input type="number" name="age" value="<?php echo htmlspecialchars($searchResult['age']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender:</label>
                                    <select name="gender" required>
                                        <option value="Male" <?php echo $searchResult['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $searchResult['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Address:</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($searchResult['address']); ?>" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Job:</label>
                                    <input type="text" name="job" value="<?php echo htmlspecialchars($searchResult['job']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Marital Status:</label>
                                    <select name="marital" required>
                                        <option value="Single" <?php echo $searchResult['marital'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo $searchResult['marital'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Divorced" <?php echo $searchResult['marital'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                        <option value="Widowed" <?php echo $searchResult['marital'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="update-btn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <button type="button" class="cancel-btn" onclick="toggleEditForm()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <script>
                    function toggleEditForm() {
                        const form = document.getElementById('updateForm');
                        form.style.display = form.style.display === 'none' ? 'block' : 'none';
                    }
                    </script>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>