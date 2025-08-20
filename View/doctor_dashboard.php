<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/DoctorController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session data
error_log('Session data: ' . print_r($_SESSION, true));

// Redirect to login if not logged in as doctor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    error_log('Redirecting to login - user not logged in or not a doctor');
    header('Location: /doctor-login');
    exit;
}

// Initialize controller and get dashboard data
try {
    $doctorController = new DoctorController();
    $data = $doctorController->dashboard();
    error_log('Dashboard data: ' . print_r($data, true));
    
    // Handle errors or redirects
    if (!is_array($data) || (is_array($data) && isset($data['error']))) {
        $error = is_array($data) ? $data['error'] : 'Invalid data returned from dashboard';
        error_log('Dashboard error: ' . $error);
        throw new Exception($error);
    }
} catch (Exception $e) {
    error_log('Exception in doctor_dashboard.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    die('Error loading dashboard: ' . htmlspecialchars($e->getMessage()) . 
        '<br>Please check the error log for more details.');
}

// Extract data with null coalescing to prevent undefined index notices
$doctor = $data['doctor'] ?? [];
$appointments = $data['appointments'] ?? [];
$currentDate = $data['currentDate'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - <?php echo htmlspecialchars($doctor['FN'] . ' ' . $doctor['LN']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/doctor_dashboard.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-outline-light rounded-circle p-2 me-3" id="sidebarToggle" aria-label="Toggle sidebar" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-chevron-left" id="sidebarToggleIcon" style="font-size: 1.25rem; transition: transform 0.3s ease;"></i>
            </button>
            <a class="navbar-brand me-auto" href="#">
                <img src="images/logo.png" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
                Doctor Dashboard
            </a>
            <div class="d-flex align-items-center">
                <div class="doctor-info">
                    <span class="doctor-name">
                        <span class="doctor-prefix">Dr.</span> 
                        <?php 
                        $doctorName = '';
                        if (!empty($doctor['FN'])) {
                            $doctorName = htmlspecialchars($doctor['FN']);
                            if (!empty($doctor['LN'])) {
                                $doctorName .= ' ' . htmlspecialchars($doctor['LN']);
                            }
                        } else {
                            $doctorName = 'Doctor';
                        }
                        echo $doctorName;
                        ?>
                    </span>
                    <?php if (!empty($doctor['ID_NUMBER'])): ?>
                    <span class="doctor-id">
                        <i class="bi bi-person-badge me-1"></i>ID: <?php echo htmlspecialchars($doctor['ID_NUMBER']); ?>
                    </span>
                    <?php endif; ?>
                </div>
                <a href="process-logout.php" class="btn btn-outline-light ms-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0 overflow-hidden">
        <div class="d-flex position-relative">
            <!-- Sidebar -->
            <nav id="sidebar" class="bg-light sidebar h-100 position-fixed">
                <!-- Close button for mobile -->
                <div class="d-md-none text-end p-3">
                    <button class="btn-close" id="closeSidebar"></button>
                </div>
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#dashboard-tab">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#appointments-tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                My Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#sessions-tab">
                                <i class="bi bi-clock-history me-2"></i>
                                Daily Sessions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#reports-tab">
                                <i class="bi bi-file-earmark-medical me-2"></i>
                                Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="flex-grow-1 main-content" style="margin-left: 280px; width: calc(100% - 280px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                <div class="container-fluid p-4 pt-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard-tab">
                        <!-- Doctor Profile Card -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="<?php echo !empty($doctor['photo']) ? htmlspecialchars($doctor['photo']) : 'images/doctor-avatar.png'; ?>" 
                                             alt="Doctor" class="rounded-circle me-4" width="100" height="100">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="mb-1">Dr. <?php echo !empty($doctor['FN']) ? htmlspecialchars($doctor['FN'] . ' ' . ($doctor['LN'] ?? '')) : 'Doctor'; ?></h4>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-briefcase me-2"></i>
                                            <?php echo !empty($doctor['specialization']) ? htmlspecialchars($doctor['specialization']) : 'Medical Professional'; ?>
                                        </p>
                                        <div class="d-flex align-items-center text-muted mb-2">
                                            <i class="bi bi-envelope me-2"></i>
                                            <?php echo !empty($doctor['email']) ? htmlspecialchars($doctor['email']) : 'N/A'; ?>
                                        </div>
                                        <div class="d-flex align-items-center text-muted">
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success bg-opacity-10 text-success mb-2">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active Now
                                        </span>
                                        <style>
                                            .btn-profile {
                                                transition: all 0.3s ease;
                                            }
                                            .btn-profile:hover {
                                                transform: translateY(-2px);
                                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                            }
                                            .btn-edit:hover {
                                                background-color: #0d6efd;
                                                color: white !important;
                                            }
                                            .btn-settings:hover {
                                                background-color: #6c757d;
                                                color: white !important;
                                            }
                                        </style>
                                        <div class="d-grid gap-2 d-md-block mt-3">
                                            <button class="btn btn-sm btn-outline-primary me-2 btn-profile btn-edit">
                                                <i class="bi bi-pencil-square"></i> Edit Profile
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary btn-profile btn-settings">
                                                <i class="bi bi-gear"></i> Settings
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title text-white-50 mb-0">TODAY'S APPOINTMENTS</h6>
                                            <span class="badge bg-white bg-opacity-25">
                                                <i class="bi bi-calendar3"></i>
                                            </span>
                                        </div>
                                        <h2 class="display-5 fw-bold mb-3 text-white"><?php echo count($appointments); ?></h2>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-white-75"><?php echo date('F j, Y'); ?></span>
                                            <a href="#" class="text-white text-decoration-none small fw-bold">
                                                View All <i class="bi bi-arrow-right-short"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-success text-white h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title text-white-50 mb-0">TOTAL SESSIONS</h6>
                                            <span class="badge bg-white bg-opacity-25">
                                                <i class="bi bi-check2-circle"></i>
                                            </span>
                                        </div>
                                        <h2 class="display-5 fw-bold mb-3 text-white"><?php echo isset($data['total_sessions']) ? $data['total_sessions'] : '0'; ?></h2>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-white-75">All time</span>
                                            <a href="#" class="text-white text-decoration-none small fw-bold">
                                                View History <i class="bi bi-arrow-right-short"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <a href="/MVC/View/chat.php" class="text-decoration-none">
                                    <div class="card bg-warning text-dark h-100 border-0 shadow-sm hover-effect">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title text-dark-50 mb-0">CHAT WITH PATIENTS</h6>
                                                <span class="badge bg-white bg-opacity-25">
                                                    <i class="bi bi-chat-dots"></i>
                                                </span>
                                            </div>
                                            <h2 class="display-5 fw-bold mb-3 text-dark">
                                                <?php echo isset($data['unread_messages']) ? $data['unread_messages'] : '0'; ?>
                                                <?php if (isset($data['unread_messages']) && $data['unread_messages'] > 0): ?>
                                                    <span class="badge bg-danger ms-2">
                                                        <?php echo $data['unread_messages']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </h2>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small text-dark-75">Unread messages</span>
                                                <span class="text-dark text-decoration-none small fw-bold">
                                                    Chat Now <i class="bi bi-arrow-right-short"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        <!-- Upcoming Appointments -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">My Appointments</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($appointments) && is_array($appointments)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Patient</th>
                                                    <th>Phone</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($appointments as $appointment): ?>
                                                <tr>
                                                    <td><?php echo !empty($appointment['appointment_time']) ? date('h:i A', strtotime($appointment['appointment_time'])) : 'N/A'; ?></td>
                                                    <td>
                                                        <?php 
                                                        $patientName = '';
                                                        if (!empty($appointment['patient_FN'])) {
                                                            $patientName = htmlspecialchars($appointment['patient_FN']);
                                                            if (!empty($appointment['patient_LN'])) {
                                                                $patientName .= ' ' . htmlspecialchars($appointment['patient_LN']);
                                                            }
                                                        } else {
                                                            $patientName = 'Unknown Patient';
                                                        }
                                                        echo $patientName;
                                                        ?>
                                                    </td>
                                                    <td><?php echo !empty($appointment['patient_phone']) ? htmlspecialchars($appointment['patient_phone']) : 'N/A'; ?></td>
                                                    <td><span class="badge bg-success">Scheduled</span></td>
                                                    <td>
                                                        <a href="single_appoinment_doctor.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Tab -->
                    <div class="tab-pane fade" id="appointments-tab">
                        <!-- Content will be loaded via AJAX -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading appointments...</p>
                        </div>
                    </div>

                    <!-- Patients Tab -->
                    <div class="tab-pane fade" id="patients-tab">
                        <!-- Content will be loaded via AJAX -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading patients...</p>
                        </div>
                    </div>

                    <!-- Reports Tab -->
                    <div class="tab-pane fade" id="reports-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Patient Progress Reports</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="progressChart" style="height: 300px;">
                                            <!-- Chart will be rendered here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Appointment Statistics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="appointmentStats" style="height: 300px;">
                                            <!-- Stats will be rendered here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Patient Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientModalLabel">Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="medical-history-tab" data-bs-toggle="tab" data-bs-target="#medical-history" type="button" role="tab">Medical History</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="treatment-plan-tab" data-bs-toggle="tab" data-bs-target="#treatment-plan" type="button" role="tab">Treatment Plan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button>
                        </li>
                    </ul>
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="patientTabContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <!-- Overview content will be loaded here -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="medical-history" role="tabpanel">
                            <!-- Medical History content will be loaded here -->
                        </div>
                        <div class="tab-pane fade" id="treatment-plan" role="tabpanel">
                            <!-- Treatment Plan content will be loaded here -->
                        </div>
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <!-- Documents content will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Diagnosis Modal -->
    <div class="modal fade" id="addDiagnosisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Diagnosis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="diagnosisForm">
                    <div class="modal-body">
                        <input type="hidden" name="appointment_id" id="diagnosisAppointmentId">
                        <input type="hidden" name="patient_id" id="diagnosisPatientId">
                        
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <input type="text" class="form-control" id="diagnosis" name="diagnosis" required>
                        </div>
                        <div class="mb-3">
                            <label for="diagnosisNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="diagnosisNotes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Diagnosis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="documentUploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="appointment_id" id="documentAppointmentId">
                        <input type="hidden" name="patient_id" id="documentPatientId">
                        
                        <div class="mb-3">
                            <label for="documentFile" class="form-label">Select File</label>
                            <input class="form-control" type="file" id="documentFile" name="attachment" required>
                        </div>
                        <div class="mb-3">
                            <label for="documentType" class="form-label">Document Type</label>
                            <select class="form-select" id="documentType" name="file_type" required>
                                <option value="">Select document type</option>
                                <option value="xray">X-Ray</option>
                                <option value="lab">Lab Results</option>
                                <option value="report">Medical Report</option>
                                <option value="prescription">Prescription</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="documentDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="documentDescription" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Treatment Plan Modal -->
    <div class="modal fade" id="addTreatmentPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Treatment Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="treatmentPlanForm">
                    <div class="modal-body">
                        <input type="hidden" name="patient_id" id="treatmentPatientId">
                        
                        <div class="mb-3">
                            <label for="sessionsCount" class="form-label">Number of Sessions</label>
                            <input type="number" class="form-control" id="sessionsCount" name="sessions_count" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="exercises" class="form-label">Exercises</label>
                            <textarea class="form-control" id="exercises" name="exercises" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="treatmentNotes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="treatmentNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            
            // Function to update the toggle icon
            function updateToggleIcon(isCollapsed) {
                if (toggleIcon) {
                    toggleIcon.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const closeSidebar = document.getElementById('closeSidebar');
            
            // Toggle sidebar on button click
            function toggleSidebar() {
                const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                const mainContent = document.querySelector('.main-content');
                
                if (isCollapsed) {
                    mainContent.style.marginLeft = '0';
                    mainContent.style.width = '100%';
                } else {
                    mainContent.style.marginLeft = '280px';
                    mainContent.style.width = 'calc(100% - 280px)';
                }
                
                updateToggleIcon(isCollapsed);
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
            
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            // Close sidebar when clicking the close button (mobile)
            if (closeSidebar) {
                closeSidebar.addEventListener('click', toggleSidebar);
            }
            
            // Initialize sidebar state from localStorage
            const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
            if (savedState) {
                document.body.classList.add('sidebar-collapsed');
                updateToggleIcon(true);
            }
            
            // Close sidebar on close button click (mobile)
            if (closeSidebar) {
                closeSidebar.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-collapsed');
                });
            }
            
            // Close sidebar when clicking outside (mobile)
            document.addEventListener('click', function(event) {
                const isClickInside = sidebar.contains(event.target) || sidebarToggle.contains(event.target);
                if (!isClickInside && window.innerWidth < 768) { // 768px is Bootstrap's md breakpoint
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-collapsed');
                }
            });
        });
    </script>
    <style>
        /* Sidebar styles */
        #sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            background-color: #f8f9fa;
            overflow-y: auto;
            padding-top: 60px; /* Space for navbar */
        }
        
        .main-content {
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: 280px;
            width: calc(100% - 280px);
            padding-top: 80px; /* Space for navbar */
        }
        
        /* Collapsed state */
        body.sidebar-collapsed #sidebar {
            transform: translateX(-100%);
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        /* Arrow rotation */
        body:not(.sidebar-collapsed) #sidebarToggleIcon {
            transform: rotate(180deg);
        }
        
        /* Link hover effects */
        .card a.text-white,
        .card a.text-dark {
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .card a.text-white:hover,
        .card a.text-dark:hover {
            transform: translateX(5px);
        }
        
        .card a.text-white:hover .bi-arrow-right-short,
        .card a.text-dark:hover .bi-arrow-right-short {
            transform: translateX(3px);
        }
        
        .bi-arrow-right-short {
            transition: transform 0.3s ease;
            display: inline-block;
        }
        
        /* Card hover effects */
        .hover-effect:hover {
            cursor: pointer;
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        /* Dashboard stats cards specific hover */
        .card.bg-primary:hover {
            background-color: #0b5ed7 !important;
        }
        
        .card.bg-success:hover {
            background-color: #157347 !important;
        }
        
        .card.bg-warning:hover {
            background-color: #ffca2c !important;
        }
        
        /* Ensure content doesn't get hidden behind navbar */
        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            padding: 0.5rem 1rem;
        }
        
        /* Adjust padding for main content to prevent overlap with fixed navbar */
        body {
            padding-top: 60px;
        }
        
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -300px;
                bottom: 0;
                z-index: 1050;
                transition: left 0.3s ease-in-out;
                overflow-y: auto;
                box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            }
            #sidebar.show {
                left: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }
            body.sidebar-collapsed .sidebar-backdrop {
                display: block;
            }
            .main-content {
                transition: margin-left 0.3s ease-in-out;
            }
        }
        /* Ensure content takes full width when sidebar is hidden */
        @media (min-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            #sidebar {
                transition: all 0.3s ease-in-out;
            }
            body.sidebar-collapsed #sidebar {
                margin-left: -25%;
            }
            body.sidebar-collapsed .main-content {
                margin-left: -25%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/doctor_dashboard.js"></script>
</body>
</html>
