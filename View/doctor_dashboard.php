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
            <a class="navbar-brand" href="#">
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

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
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
                                Daily Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#patients-tab">
                                <i class="bi bi-people me-2"></i>
                                Patients
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> New Appointment
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard-tab">
                        <div class="row">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo !empty($doctor['photo']) ? htmlspecialchars($doctor['photo']) : 'images/doctor-avatar.png'; ?>" 
                                     alt="Doctor" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <h6 class="mb-0">Dr. <?php echo !empty($doctor['FN']) ? htmlspecialchars($doctor['FN'] . ' ' . ($doctor['LN'] ?? '')) : 'Doctor'; ?></h6>
                                    <?php if (!empty($doctor['id'])): ?>
                                    <small class="text-muted">ID: <?php echo htmlspecialchars($doctor['id']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Today's Appointments</h5>
                                        <h2 class="display-5"><?php echo count($appointments); ?></h2>
                                        <p class="card-text">Scheduled for <?php echo date('F j, Y'); ?></p>
                                    </div>
                                    <div class="card-footer d-flex">
                                        View all appointments
                                        <i class="bi bi-chevron-right ms-auto"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Completed Sessions</h5>
                                        <h2 class="display-5">0</h2>
                                        <p class="card-text">This week</p>
                                    </div>
                                    <div class="card-footer d-flex">
                                        View reports
                                        <i class="bi bi-chevron-right ms-auto"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card bg-warning text-dark h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Pending Actions</h5>
                                        <h2 class="display-5">3</h2>
                                        <p class="card-text">Requires your attention</p>
                                    </div>
                                    <div class="card-footer d-flex">
                                        Take action
                                        <i class="bi bi-chevron-right ms-auto"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Appointments -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Today's Appointments (<?php echo htmlspecialchars($currentDate); ?>)</h5>
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
                                                        <?php if (!empty($appointment['id'])): ?>
                                                        <button class="btn btn-sm btn-primary view-patient" 
                                                                data-id="<?php echo (int)$appointment['id']; ?>">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/doctor_dashboard.js"></script>
</body>
</html>
