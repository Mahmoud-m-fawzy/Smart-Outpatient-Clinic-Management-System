<?php
require_once __DIR__ . '/../Model/Manager.php';

class ManagerController {
    /**
     * Start session with secure settings
     */
    public static function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session cookie parameters before starting the session
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => 86400, // 24 hours
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            session_start();
            
            // Regenerate session ID to prevent session fixation
            if (empty($_SESSION['last_activity'])) {
                session_regenerate_id(true);
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Handle manager login
     */
    public static function handleLogin() {
        self::startSecureSession();

        // Check if user is already logged in and trying to access login page
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'manager' && 
            isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'manager_login.php') !== false) {
            header('Location: /MVC/View/manager_dashboard.php');
            exit();
        }

        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Clear any previous errors
            unset($_SESSION['login_errors']);
            
            // Validate input
            $errors = [];
            
            if (empty($username)) {
                $errors[] = 'Username is required';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            
            // If no validation errors, try to login
            if (empty($errors)) {
                $manager = new Manager();
                $result = $manager->login($username, $password);
                
                if ($result['success'] && isset($result['manager'])) {
                    // Set session data
                    $_SESSION['user'] = [
                        'id' => $result['manager']['id'],
                        'username' => $result['manager']['username'],
                        'first_name' => $result['manager']['first_name'],
                        'last_name' => $result['manager']['last_name'],
                        'email' => $result['manager']['email'],
                        'phone' => $result['manager']['phone']
                    ];
                    $_SESSION['user_type'] = 'manager';
                    $_SESSION['last_activity'] = time();
                    
                    // Redirect to dashboard on success
                    header('Location: /MVC/View/manager_dashboard.php');
                    exit();
                } else {
                    $error = $result['error'] ?? 'Login failed. Please try again.';
                    $errors[] = $error;
                }
            }
            
            // If we got here, there were errors
            if (!empty($errors)) {
                $_SESSION['login_errors'] = $errors;
            }
            header('Location: /MVC/View/manager_login.php');
            exit();
        }
    }

    /**
     * Handle manager logout
     */
    public static function handleLogout() {
        self::startSecureSession();
        
        // Clear all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: /MVC/View/manager_login.php');
        exit();
    }

    /**
     * Get dashboard statistics
     * @return array Dashboard statistics
     */
    public static function getDashboardStatistics() {
        $manager = new Manager();
        return [
            'success' => true,
            'stats' => [
                'total_patients' => $manager->getTotalPatients(),
                'total_staff' => $manager->getTotalStaff(),
                'total_doctors' => $manager->getTotalDoctors(),
                'total_appointments' => $manager->getTotalAppointments(true)
            ]
        ];
    }
    
    /**
     * Handle patient search request
     * @return array Search results or error message
     */
    /**
     * Handle patient update request
     * @return array Result of the update operation
     */
    public static function updatePatient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        
        if (!isset($_POST['id'])) {
            return ['success' => false, 'error' => 'Patient ID is required'];
        }
        
        $patientId = (int)$_POST['id'];
        $updateData = [];
        
        // Filter and sanitize input data
        $allowedFields = ['FN', 'LN', 'email', 'phone', 'age', 'address', 'job', 'gender', 'marital'];
        foreach ($_POST as $key => $value) {
            if (in_array($key, $allowedFields) && $value !== '') {
                $updateData[$key] = trim($value);
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        $manager = new Manager();
        $result = $manager->updatePatient($patientId, $updateData);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Patient information updated successfully',
                'patient' => $manager->searchPatientById($patientId)
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to update patient information'
            ];
        }
    }
    
    /**
     * Handle patient search request
     * @return array Search results or error message
     */
    public static function searchPatient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request method'];
        }
        
        $searchType = $_POST['search_type'] ?? '';
        $searchValue = trim($_POST['search_value'] ?? '');
        
        if (empty($searchValue)) {
            return ['success' => false, 'error' => 'Please enter a search value'];
        }
        
        $manager = new Manager();
        
        if ($searchType === 'phone') {
            $result = $manager->searchPatientByPhone($searchValue);
        } else {
            $result = $manager->searchPatientById($searchValue);
        }
        
        if ($result) {
            return [
                'success' => true,
                'patient' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => 'No patient found with the provided information',
                'stats' => [
                    'total_patients' => 0,
                    'total_staff' => 0,
                    'total_doctors' => 0,
                    'total_appointments' => 0
                ]
            ];
        }
    }
}

// Initialize session
ManagerController::startSecureSession();

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    ManagerController::handleLogin();
}

// Handle logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    ManagerController::handleLogout();
}
