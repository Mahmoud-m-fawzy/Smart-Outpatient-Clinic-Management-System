<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/StaffController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$staffController = new StaffController();

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: staff_login.php');
    exit();
}

// Get staff data
$staffData = $_SESSION['user'];

// Get today's appointments
$todayReport = $staffController->generateDailyReport(date('Y-m-d'));

// Get all doctors for dropdowns
$doctors = (new Doctor())->getAllDoctors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo htmlspecialchars($staffData['first_name'] . ' ' . $staffData['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/staff_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="Logo" height="40" class="d-inline-block align-text-top me-2">
                Staff Dashboard
            </a>
            <div class="d-flex align-items-center">
                <div class="doctor-info">
                    <span class="doctor-name">
                        <?php echo htmlspecialchars($staffData['first_name'] . ' ' . $staffData['last_name']); ?>
                    </span>
                    <span class="doctor-id">
                        <i class="bi bi-person-badge me-1"></i>ID: <?php echo htmlspecialchars($staffData['id_number']); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-outline-light ms-3">
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
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#schedule-tab">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Manage Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#appointments-tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                Book Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#patients-tab">
                                <i class="bi bi-people me-2"></i>
                                Patient Search
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#reports-tab">
                                <i class="bi bi-file-earmark-medical me-2"></i>
                                Daily Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard-tab">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Dashboard Overview</h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                                    <i class="bi bi-plus-circle"></i> New Appointment
                                </button>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card text-white bg-primary h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-uppercase mb-2">Today's Appointments</h6>
                                                <h2 class="mb-0"><?php echo $todayReport['total_appointments'] ?? '0'; ?></h2>
                                            </div>
                                            <i class="bi bi-calendar-check fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-white bg-success h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-uppercase mb-2">Completed</h6>
                                                <h2 class="mb-0"><?php echo $todayReport['completed'] ?? '0'; ?></h2>
                                            </div>
                                            <i class="bi bi-check-circle fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-white bg-warning h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-uppercase mb-2">Pending</h6>
                                                <h2 class="mb-0"><?php echo $todayReport['pending'] ?? '0'; ?></h2>
                                            </div>
                                            <i class="bi bi-clock-history fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-white bg-danger h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-uppercase mb-2">Cancelled</h6>
                                                <h2 class="mb-0"><?php echo $todayReport['cancelled'] ?? '0'; ?></h2>
                                            </div>
                                            <i class="bi bi-x-circle fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="stat-info">
                            <h3>Appointments Today</h3>
                            <p class="stat-number">45</p>
                            <p class="stat-change">8 remaining</p>
                        </div>
                    </div>

                    <!-- Schedule Management Tab -->
                    <div class="tab-pane fade" id="schedule-tab">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Manage Doctor Schedules</h1>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                <i class="bi bi-plus-circle"></i> Add Schedule
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="schedulesTable">
                                        <thead>
                                            <tr>
                                                <th>Doctor</th>
                                                <th>Day</th>
                                                <th>Time Slot</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Book Appointments Tab -->
                    <div class="tab-pane fade" id="appointments-tab">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Book New Appointment</h1>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form id="bookAppointmentForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="patientSearch" class="form-label">Search Patient</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="patientSearch" placeholder="Search by ID, Name, or Phone">
                                                <button class="btn btn-outline-secondary" type="button" id="searchPatientBtn">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                            <div id="patientSearchResults" class="mt-2"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="doctorSelect" class="form-label">Select Doctor</label>
                                            <select class="form-select" id="doctorSelect" required>
                                                <option value="" selected disabled>Select Doctor</option>
                                                <?php foreach ($doctors as $doctor): ?>
                                                    <option value="<?php echo $doctor['id']; ?>">
                                                        <?php echo htmlspecialchars('Dr. ' . $doctor['FN'] . ' ' . $doctor['LN'] . ' (' . $doctor['specialty'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="appointmentDate" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="appointmentDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="appointmentTime" class="form-label">Time</label>
                                            <select class="form-select" id="appointmentTime" required>
                                                <option value="" selected disabled>Select Time</option>
                                                <!-- Will be populated by JavaScript -->
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="visitType" class="form-label">Visit Type</label>
                                            <select class="form-select" id="visitType" required>
                                                <option value="Consultation">Consultation</option>
                                                <option value="Follow-up">Follow-up</option>
                                                <option value="Emergency">Emergency</option>
                                                <option value="Check-up">Check-up</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="appointmentNotes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="appointmentNotes" rows="2"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-calendar-plus"></i> Book Appointment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Search Tab -->
                    <div class="tab-pane fade" id="patients-tab">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Patient Search</h1>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="patientSearchInput" placeholder="Search by ID, Name, Phone, or Email">
                                            <button class="btn btn-primary" type="button" id="searchPatientBtn">
                                                <i class="bi bi-search me-1"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                            <i class="bi bi-person-plus me-1"></i> Add New Patient
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover" id="patientsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Last Visit</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Tab -->
                    <div class="tab-pane fade" id="reports-tab">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">Daily Appointment Report</h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="printReportBtn">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="exportReportBtn">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="date" class="form-control" id="reportDate" value="<?php echo date('Y-m-d'); ?>">
                                    <button class="btn btn-primary" type="button" id="generateReportBtn">
                                        <i class="bi bi-arrow-repeat"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div id="reportContent">
                                    <!-- Report will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Doctor Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="scheduleForm">
                    <div class="modal-body">
                        <input type="hidden" id="scheduleId">
                        <div class="mb-3">
                            <label for="doctorSelect" class="form-label">Doctor</label>
                            <select class="form-select" id="scheduleDoctor" required>
                                <option value="" selected disabled>Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo htmlspecialchars('Dr. ' . $doctor['FN'] . ' ' . $doctor['LN']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dayOfWeek" class="form-label">Day of Week</label>
                                <select class="form-select" id="dayOfWeek" required>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" required>
                            </div>
                            <div class="col-md-6">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="scheduleNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="scheduleNotes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="patientDetailsForm">
                        <input type="hidden" id="patientId">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob">
                            </div>
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="bloodType" class="form-label">Blood Type</label>
                                <select class="form-select" id="bloodType">
                                    <option value="">Unknown</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="medicalHistory" class="form-label">Medical History</label>
                            <textarea class="form-control" id="medicalHistory" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePatientBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newPatientForm">
                        <div class="mb-3">
                            <label for="newFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="newFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="newLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="newLastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="newPhone" required>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="newEmail">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNewPatientBtn">Save Patient</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/staff_dashboard.js"></script>
</body>
</html>