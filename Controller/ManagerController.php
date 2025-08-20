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
     * Get report data for the manager dashboard
     * @param string $reportType Type of report to generate
     * @param string $startDate Start date for the report (Y-m-d)
     * @param string $endDate End date for the report (Y-m-d)
     * @return array Report data including statistics and recent appointments
     */
    public static function getReportData($reportType = 'overview', $startDate = null, $endDate = null) {
        self::startSecureSession();
        
        // Check if user is logged in as manager
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
            return ['error' => 'Unauthorized access'];
        }
        
        // Set default date range if not provided
        if ($startDate === null) {
            $startDate = date('Y-m-01'); // First day of current month
        }
        if ($endDate === null) {
            $endDate = date('Y-m-t'); // Last day of current month
        }
        
        // Validate dates
        if (!self::validateDate($startDate) || !self::validateDate($endDate)) {
            return ['error' => 'Invalid date format. Use YYYY-MM-DD.'];
        }
        
        $manager = new Manager();
        $link = $manager->getLink();
        
        // Initialize report data array with default values
        $reportData = [
            'title' => ucfirst($reportType) . ' Report',
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'generated_at' => date('Y-m-d H:i:s'),
            'stats' => [
                'total_appointments' => 0,
                'completed_appointments' => 0,
                'pending_appointments' => 0,
                'cancelled_appointments' => 0,
                'total_revenue' => 0,
                'total_patients' => 0,
                'total_doctors' => 0,
                'total_staff' => 0
            ],
            'recent_appointments' => [],
            'appointments_by_date' => [],
            'revenue_by_service' => []
        ];
        
        try {
            // Get basic statistics
            $reportData['stats'] = [
                'total_appointments' => $manager->getTotalAppointments(),
                'completed_appointments' => $manager->getCompletedAppointments(),
                'pending_appointments' => $manager->getPendingAppointments(),
                'cancelled_appointments' => $manager->getCancelledAppointments(),
                'total_revenue' => $manager->getTotalRevenue(),
                'total_patients' => $manager->getTotalPatients(),
                'total_doctors' => $manager->getTotalDoctors(),
                'total_staff' => $manager->getTotalStaff()
            ];
            
            // Check if appointment table exists
            $appointmentTableExists = mysqli_query($link, "SHOW TABLES LIKE 'appointment'");
            if (mysqli_num_rows($appointmentTableExists) > 0) {
                // Get recent appointments (last 10)
                $patientTableExists = mysqli_query($link, "SHOW TABLES LIKE 'patient'");
                $doctorTableExists = mysqli_query($link, "SHOW TABLES LIKE 'doctor'");
                
                // Build the query based on available tables and columns
                $selectFields = ["a.*"];
                $joins = [];
                
                // Handle patient name fields
                if (mysqli_num_rows($patientTableExists) > 0) {
                    $patientCols = [];
                    $cols = mysqli_query($link, "SHOW COLUMNS FROM patient");
                    while ($col = mysqli_fetch_assoc($cols)) {
                        $patientCols[] = $col['Field'];
                    }
                    
                    if (in_array('FN', $patientCols) && in_array('LN', $patientCols)) {
                        $selectFields[] = "p.FN as patient_first_name";
                        $selectFields[] = "p.LN as patient_last_name";
                        $joins[] = "LEFT JOIN patient p ON a.patient_id = p.patient_id";
                    } else {
                        $selectFields[] = "'' as patient_first_name";
                        $selectFields[] = "'' as patient_last_name";
                    }
                } else {
                    $selectFields[] = "'' as patient_first_name";
                    $selectFields[] = "'' as patient_last_name";
                }
                
                // Handle doctor name fields
                if (mysqli_num_rows($doctorTableExists) > 0) {
                    $doctorCols = [];
                    $cols = mysqli_query($link, "SHOW COLUMNS FROM doctor");
                    while ($col = mysqli_fetch_assoc($cols)) {
                        $doctorCols[] = $col['Field'];
                    }
                    
                    if (in_array('FN', $doctorCols) && in_array('LN', $doctorCols)) {
                        $selectFields[] = "d.FN as doctor_first_name";
                        $selectFields[] = "d.LN as doctor_last_name";
                        $joins[] = "LEFT JOIN doctor d ON a.doctor_id = d.doctor_id";
                    } else {
                        $selectFields[] = "'' as doctor_first_name";
                        $selectFields[] = "'' as doctor_last_name";
                    }
                } else {
                    $selectFields[] = "'' as doctor_first_name";
                    $selectFields[] = "'' as doctor_last_name";
                }
                
                // Build and execute the query
                $sql = "SELECT " . implode(", ", $selectFields) . "
                        FROM appointment a
                        " . implode(" ", $joins) . "
                        ORDER BY a.appointment_date DESC, a.appointment_time DESC
                        LIMIT 10";
                
                $result = mysqli_query($link, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $reportData['recent_appointments'][] = $row;
                    }
                }
                
                // Get appointments by date for the last 7 days
                $dateColumn = 'appointment_date';
                $cols = mysqli_query($link, "SHOW COLUMNS FROM appointment");
                $hasDateColumn = false;
                $hasStatusColumn = false;
                
                while ($col = mysqli_fetch_assoc($cols)) {
                    if (stripos($col['Type'], 'date') !== false) {
                        $dateColumn = $col['Field'];
                        $hasDateColumn = true;
                    }
                    if ($col['Field'] === 'status') {
                        $hasStatusColumn = true;
                    }
                }
                
                if ($hasDateColumn) {
                    $statusCheck = $hasStatusColumn ? 
                        "SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed" : 
                        "0 as completed";
                    
                    $sql = "SELECT DATE($dateColumn) as date, 
                                   COUNT(*) as count, 
                                   $statusCheck
                            FROM appointment 
                            WHERE $dateColumn >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                            GROUP BY DATE($dateColumn)
                            ORDER BY date ASC";
                    
                    $result = mysqli_query($link, $sql);
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $reportData['appointments_by_date'][] = $row;
                        }
                    }
                }
                
                // Get revenue by service if service table exists
                $serviceTableExists = mysqli_query($link, "SHOW TABLES LIKE 'service'");
                if (mysqli_num_rows($serviceTableExists) > 0) {
                    $hasServiceId = false;
                    $cols = mysqli_query($link, "SHOW COLUMNS FROM appointment LIKE 'service_id'");
                    if (mysqli_num_rows($cols) > 0) {
                        $hasServiceId = true;
                        
                        $serviceNameColumn = 'service_name';
                        $cols = mysqli_query($link, "SHOW COLUMNS FROM service");
                        $hasFeeColumn = false;
                        
                        while ($col = mysqli_fetch_assoc($cols)) {
                            if ($col['Field'] === 'name') {
                                $serviceNameColumn = 'name';
                            }
                            if ($col['Field'] === 'fee') {
                                $hasFeeColumn = true;
                            }
                        }
                        
                        $feeSelect = $hasFeeColumn ? "SUM(s.fee)" : "0";
                        $sql = "SELECT s.$serviceNameColumn as service_name, 
                                       COUNT(a.appointment_id) as appointment_count, 
                                       $feeSelect as total_revenue
                                FROM appointment a
                                JOIN service s ON a.service_id = s.service_id";
                        
                        if ($hasStatusColumn) {
                            $sql .= " WHERE a.status = 'completed'";
                        }
                        
                        $sql .= " GROUP BY s.$serviceNameColumn";
                        
                        $result = mysqli_query($link, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $reportData['revenue_by_service'][] = $row;
                            }
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            // Log error but don't expose it to the user
            error_log("Error in getReportData: " . $e->getMessage());
        }
        
        return $reportData;
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
                'message' => 'Patient not found'
            ];
        }
    }
/**
 * Generate overview report data
 */
private static function getOverviewReport($startDate, $endDate) {
    $manager = new Manager();
    $link = $manager->getLink();
    
    // Get total appointments
    $totalAppointments = $manager->getTotalAppointments($startDate, $endDate);
    
    // Get new patients
        $newPatients = $manager->getNewPatients($startDate, $endDate);
        
        // Get revenue data
        $revenueData = $manager->getRevenueData($startDate, $endDate);
        
        // Get staff performance
        $staffPerformance = $manager->getStaffPerformance($startDate, $endDate);
        
        return [
            'sections' => [
                [
                    'title' => 'Appointments Overview',
                    'table' => [
                        'headers' => ['Metric', 'Count'],
                        'rows' => [
                            ['Total Appointments', $totalAppointments],
                            ['New Patients', $newPatients],
                            ['Completed Appointments', $manager->getCompletedAppointments($startDate, $endDate)],
                            ['Cancelled Appointments', $manager->getCancelledAppointments($startDate, $endDate)]
                        ]
                    
                    ]
                ],
                [
                    'title' => 'Revenue Summary',
                    'summary' => [
                        ['label' => 'Total Revenue', 'value' => '$' . number_format($revenueData['total_revenue'] ?? 0, 2)],
                        ['label' => 'Average Revenue per Appointment', 'value' => '$' . number_format($revenueData['avg_revenue'] ?? 0, 2)],
                        ['label' => 'Most Common Service', 'value' => $revenueData['top_service'] ?? 'N/A']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate appointments report data
     */
    private static function getAppointmentsReport($startDate, $endDate) {
        $manager = new Manager();
        $appointments = $manager->getAppointmentsByDateRange($startDate, $endDate);
        
        $rows = [];
        foreach ($appointments as $appt) {
            $rows[] = [
                $appt['appointment_date'],
                $appt['patient_name'],
                $appt['staff_name'],
                $appt['service_name'],
                ucfirst($appt['status']),
                '$' . number_format($appt['amount'] ?? 0, 2)
            ];
        }
        
        return [
            'sections' => [
                [
                    'title' => 'Appointment Details',
                    'table' => [
                        'headers' => ['Date', 'Patient', 'Staff', 'Service', 'Status', 'Amount'],
                        'rows' => $rows
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate revenue report data
     */
    private static function getRevenueReport($startDate, $endDate) {
        $manager = new Manager();
        $revenueByService = $manager->getRevenueByService($startDate, $endDate);
        $revenueByMonth = $manager->getRevenueByMonth($startDate, $endDate);
        
        $serviceRows = [];
        foreach ($revenueByService as $service) {
            $serviceRows[] = [
                $service['service_name'],
                $service['appointment_count'],
                '$' . number_format($service['total_revenue'], 2),
                '$' . number_format($service['avg_revenue'], 2)
            ];
        }
        
        return [
            'sections' => [
                [
                    'title' => 'Revenue by Service',
                    'table' => [
                        'headers' => ['Service', 'Appointments', 'Total Revenue', 'Average Revenue'],
                        'rows' => $serviceRows
                    ]
                ],
                [
                    'title' => 'Monthly Revenue',
                    'table' => [
                        'headers' => ['Month', 'Appointments', 'Total Revenue'],
                        'rows' => $revenueByMonth
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate patient report data
     */
    private static function getPatientReport($startDate, $endDate) {
        $manager = new Manager();
        $newPatients = $manager->getNewPatientsDetails($startDate, $endDate);
        $patientDemographics = $manager->getPatientDemographics();
        
        $patientRows = [];
        foreach ($newPatients as $patient) {
            $patientRows[] = [
                $patient['patient_id'],
                $patient['full_name'],
                $patient['gender'],
                $patient['age'],
                $patient['phone'],
                $patient['first_appointment']
            ];
        }
        
        return [
            'sections' => [
                [
                    'title' => 'New Patients',
                    'table' => [
                        'headers' => ['ID', 'Name', 'Gender', 'Age', 'Phone', 'First Appointment'],
                        'rows' => $patientRows
                    ]
                ],
                [
                    'title' => 'Patient Demographics',
                    'summary' => [
                        ['label' => 'Total Patients', 'value' => $patientDemographics['total_patients']],
                        ['label' => 'Average Age', 'value' => $patientDemographics['avg_age']],
                        ['label' => 'Gender Distribution', 'value' => $patientDemographics['gender_distribution']]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Validate date format (YYYY-MM-DD)
     */
    private static function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
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
