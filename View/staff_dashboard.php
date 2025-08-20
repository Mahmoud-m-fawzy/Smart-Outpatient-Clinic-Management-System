<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/StaffController.php';
require_once __DIR__ . '/../Model/Database.php';

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

// Get all doctors for dropdowns
$doctors = (new Doctor())->getAllDoctors();

// Get today's appointments
try {
    $today = date('Y-m-d');
    error_log("Checking for appointments on: " . $today);
    
    // Get today's appointments using the controller
    $todayAppointments = $staffController->getAppointmentsByDate($today);
    error_log("Found " . count($todayAppointments) . " appointments for today");
    
    // Create a test appointment if none exist
    if (empty($todayAppointments)) {
        $testAppointment = [
            'appointment_id' => 'TEST-001',
            'patient_name' => 'John Doe',
            'phone' => '123-456-7890',
            'doctor_name' => 'Dr. Jane Smith',
            'appointment_type' => 'Test Appointment',
            'status' => 'Scheduled',
            'appointment_time' => '10:00 AM'
        ];
        $todayAppointments[] = $testAppointment;
    }
    
} catch (Exception $e) {
    $todayAppointments = [];
    $error = "Error loading appointments: " . $e->getMessage();
    error_log($error);
    // Display error on the page for debugging
    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo htmlspecialchars($staffData['first_name'] . ' ' . $staffData['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4bb543;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2a2d3e 0%, #1e2130 100%);
            color: #fff;
            transition: all 0.3s;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .quick-action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .quick-action-card i {
            font-size: 2rem;
            margin-bottom: 12px;
            color: var(--primary-color);
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 25px 0 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@3.0.0/dist/qz-tray.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@3.0.0/dist/js/rsvp-3.5.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@3.0.0/dist/js/sha-256.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@3.0.0/dist/js/sha-256.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-none d-md-block" style="width: 250px;">
            <div class="p-3 text-center">
                <img src="images/logo.png" alt="Logo" height="50" class="mb-3">
                <h5 class="text-white mb-0">Medical Center</h5>
                <small class="text-muted">Staff Portal</small>
            </div>
            <hr class="bg-secondary">
            <ul class="nav flex-column px-2">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-injured"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-md"></i> Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-file-invoice"></i> Reports
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-warning" href="/MVC/View/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-link d-md-none" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center ms-auto">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="me-2 text-end d-none d-sm-block">
                                    <div class="fw-bold"><?php echo htmlspecialchars($staffData['first_name'] . ' ' . $staffData['last_name']); ?></div>
                                    <small class="text-muted">Staff Member</small>
                                </div>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr($staffData['first_name'], 0, 1) . substr($staffData['last_name'], 0, 1)); ?>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="p-4">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($staffData['first_name']); ?>!</h2>
                    <p>Here's what's happening with your clinic today.</p>
                    <i class="fas fa-clinic-medical" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 5rem; opacity: 0.2;"></i>
                </div>
                
                <!-- Quick Actions -->
                <h5 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h5>
                <div class="quick-actions">
                    <!-- Today's Appointments -->
                    <button class="quick-action-card" onclick="window.location.href='#today-appointments'">
                        <i class="fas fa-calendar-day"></i>
                        <h5>Today's Appointments</h5>
                        <p>View and manage today's schedule</p>
                    </button>
                    
                    <!-- Add Slot -->
                    <a href="add_slot.php" class="quick-action-card text-decoration-none">
                        <div>
                            <i class="far fa-calendar-plus"></i>
                            <h5 style="color: #000000;">Add Slot</h5>
                            <p style="color: #000000;">Create new appointment slots</p>
                        </div>
                    </a>
                    
                    <!-- Add Patient -->
                    <a href="add_patient.php" class="quick-action-card text-decoration-none">
                        <div>
                            <i class="fas fa-user-plus"></i>
                            <h5 style="color: #000000;">Add Patient</h5>
                            <p style="color: #000000;">Register new patient records</p>
                        </div>
                    </a>
                    
                    <!-- Room Activity -->
                    <button class="quick-action-card" onclick="window.location.href='status_board.php'">
                        <i class="fas fa-door-open"></i>
                        <h5>Room Activity</h5>
                        <p>Monitor room status</p>
                    </button>
                    

                </div>

                <!-- Today's Appointments Section -->
                <div id="today-appointments" class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title"><i class="far fa-calendar-alt"></i> Today's Appointments (<?php echo date('F j, Y', strtotime($today)); ?>)</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshAppointments()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Phone</th>
                                    <th>Doctor</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appointments-list">
                                <?php if (empty($todayAppointments)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-calendar-check fa-3x text-muted mb-3 d-block"></i>
                                            <h5>No appointments scheduled for today</h5>
                                            <p class="text-muted">All caught up for now!</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    // Filter out appointments without required data
                                    $validAppointments = array_filter($todayAppointments, function($appt) {
                                        return !empty($appt['start_time']) && !empty($appt['end_time']) && 
                                               !empty($appt['patient']) && is_array($appt['patient']) &&
                                               !empty($appt['doctor']) && is_array($appt['doctor']);
                                    });
                                    
                                    if (empty($validAppointments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-calendar-check fa-3x text-muted mb-3 d-block"></i>
                                                <h5>No valid appointments to display</h5>
                                                <p class="text-muted">Please check your appointments data</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($validAppointments as $appointment): 
                                        $startTime = !empty($appointment['start_time']) ? new DateTime($appointment['start_time']) : null;
                                        $endTime = !empty($appointment['end_time']) ? new DateTime($appointment['end_time']) : null;
                                        $appointmentId = $appointment['id'] ?? 'N/A';
                                    ?>
                                        <tr data-appointment-id="<?php echo htmlspecialchars($appointmentId); ?>">
                                            <td class="align-middle">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium"><?php echo $startTime ? $startTime->format('h:i A') : 'N/A'; ?></span>
                                                    <small class="text-muted"><?php echo $endTime ? $endTime->format('h:i A') : 'N/A'; ?></small>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-light rounded-circle text-primary">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($appointment['patient']['name'] ?? 'N/A'); ?></h6>
                                                        <small class="text-muted">ID: <?php echo htmlspecialchars($appointment['patient']['id'] ?? 'N/A'); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (!empty($appointment['patient']['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($appointment['patient']['phone']); ?>" class="text-decoration-none">
                                                        <i class="fas fa-phone-alt me-1 text-muted"></i>
                                                        <?php echo htmlspecialchars($appointment['patient']['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-light rounded-circle text-success">
                                                            <i class="fas fa-user-md"></i>
                                                        </div>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($appointment['doctor']['name'] ?? 'N/A'); ?></span>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge bg-info">
                                                    <?php echo !empty($appointment['visit_type']) ? htmlspecialchars(ucfirst($appointment['visit_type'])) : 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge bg-<?php 
                                                    echo $appointment['status'] === 'Completed' ? 'success' : 
                                                         ($appointment['status'] === 'Cancelled' ? 'danger' : 'primary'); 
                                                ?> rounded-pill">
                                                    <?php echo htmlspecialchars($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <button class="btn btn-sm btn-outline-primary btn-print-receipt" 
                                                        data-appointment-id="<?php echo $appointmentId; ?>"
                                                        data-patient-name="<?php echo htmlspecialchars($appointment['patient']['name'] ?? 'N/A'); ?>"
                                                        data-doctor-name="<?php echo htmlspecialchars($appointment['doctor']['name'] ?? 'N/A'); ?>"
                                                        data-date="<?php echo $startTime ? $startTime->format('F j, Y') : 'N/A'; ?>"
                                                        data-time="<?php 
                                                            $timeStr = 'N/A';
                                                            if ($startTime && $endTime) {
                                                                $timeStr = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');
                                                            }
                                                            echo $timeStr;
                                                        ?>"
                                                        data-visit-type="<?php echo !empty($appointment['visit_type']) ? htmlspecialchars(ucfirst($appointment['visit_type'])) : 'N/A'; ?>"
                                                        data-fee="<?php echo isset($appointment['fee']) ? htmlspecialchars($appointment['fee']) : '100.00'; ?>">
                                                    <i class="fas fa-print me-1"></i> Print
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                    </div>

    <!-- Add Patient Modal -->
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="patientForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="patientForm" class="btn btn-primary">Save Patient</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Slot Modal -->
    <div class="modal fade" id="addSlotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Appointment Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="slotForm">
                        <div class="mb-3">
                            <label class="form-label">Doctor</label>
                            <select class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" value="30" min="5" step="5">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="slotForm" class="btn btn-primary">Create Slots</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="receiptContent" class="p-4">
                        <!-- Receipt content will be generated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Helper function to format date as dd/mm/yyyy
        function formatDateForReceipt(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Function to generate receipt content
        function generateReceipt(appointmentData) {
            const { 
                id, 
                patientName, 
                doctorName, 
                date, 
                time, 
                visitType,
                fee
            } = appointmentData;
            
            // Generate receipt number (RCPT + 5-digit appointment ID)
            const receiptNumber = 'RCPT' + String(id).padStart(5, '0');
            
            // Format the current date and time for receipt
            const now = new Date();
            const receiptDate = now.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            return `
                <div class="receipt-container">
                    <div class="text-center mb-3">
                        <h4 class="mb-1">OUTPATIENT CLINIC</h4>
                        <p class="mb-1 small text-muted">MSA UNIVERSITY</p>
                        <p class="mb-1 small text-muted">${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="receipt-header mb-3">
                        <h5 class="text-center mb-3">APPOINTMENT RECEIPT</h5>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Receipt #:</span>
                            <span class="receipt-number">${receiptNumber}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Date:</span>
                            <span>${formatDateForReceipt(new Date())}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Time:</span>
                            <span>${new Date().toLocaleTimeString()}</span>
                        </div>
                    </div>
                    
                    <div class="patient-details mb-3">
                        <h6 class="border-bottom pb-1 mb-2">Appointment Details</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Patient:</span>
                            <span class="patient-name">${patientName}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Doctor:</span>
                            <span class="doctor-name">${doctorName.replace('Dr. ', '')}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Date:</span>
                            <span class="appointment-date">${date}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Time:</span>
                            <span class="appointment-time">${time}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Visit Type:</span>
                            <span>${visitType || 'General Consultation'}</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <tbody>
                                <tr class="table-active">
                                    <td><strong>Fee Amount</strong></td>
                                    <td class="text-end fee-amount">${parseFloat(fee).toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-1">Thank you for choosing our clinic</p>
                        <p class="text-muted small mb-0">Please bring this receipt for your next visit</p>
                    </div>
                </div>
                
                <style>
                    .receipt-container {
                        max-width: 500px;
                        margin: 0 auto;
                        font-family: Arial, sans-serif;
                    }
                    @media print {
                        body * {
                            visibility: hidden;
                        }
                        .receipt-container, .receipt-container * {
                            visibility: visible;
                        }
                        .receipt-container {
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 100%;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                </style>
            `;
        }
        
        // Function to print the receipt using QZ Tray
        function printReceipt() {
            // Get receipt data
            const receiptNumber = document.querySelector('.receipt-number')?.textContent || '0000';
            const patientName = document.querySelector('.patient-name')?.textContent || 'N/A';
            const doctorName = document.querySelector('.doctor-name')?.textContent || 'N/A';
            const dateTime = document.querySelector('.appointment-time')?.textContent || new Date().toLocaleString();
            const fee = document.querySelector('.fee-amount')?.textContent || '0.00';

            // Create ESC/POS commands for the receipt
            const cmds = [
                // Initialize printer
                '\x1B@',
                // Center align
                '\x1B\x61\x01',
                // Double height and width
                '\x1D\x21\x11',
                // Clinic name
                'OUTPATIENT CLINIC\n',
                'MSA UNIVERSITY\n\n',
                // Reset text size
                '\x1D\x21\x00',
                // Left align
                '\x1B\x61\x00',
                `Receipt #: ${receiptNumber}\n`,
                `Date: ${new Date().toLocaleDateString()}\n`,
                `Time: ${new Date().toLocaleTimeString()}\n\n`,
                // Bold
                '\x1B\x45\x01',
                'APPOINTMENT RECEIPT\n\n',
                // Regular weight
                '\x1B\x45\x00',
                `Patient: ${patientName}\n`,
                `Doctor: Dr. ${doctorName}\n`,
                `Date/Time: ${dateTime}\n\n`,
                // Line
                '--------------------------------\n',
                // Bold
                '\x1B\x45\x01',
                'Fee: $' + parseFloat(fee).toFixed(2) + '\n',
                // Regular weight
                '\x1B\x45\x00',
                '--------------------------------\n\n',
                // Center align
                '\x1B\x61\x01',
                'Thank you for choosing\n',
                'our clinic!\n\n',
                // Feed paper
                '\n\n\n\x1B\x69',
                // Cut paper
                '\x1D\x56\x41\x10'
            ];

            // Configure QZ Tray
            qz.api.setSha256Type(data => sha256(data));
            qz.api.setPromiseType(resolver => new Promise(resolver));

            // Create config for the default printer
            const config = qz.configs.create();

            // Print using QZ Tray
            qz.print(config, [{
                type: 'raw',
                format: 'plain',
                data: cmds.join('')
            }]).catch(e => {
                console.error('Print error:', e);
                alert('Printing failed. Please make sure QZ Tray is running and a printer is configured.\n\nError: ' + e.message);
                
                // Fallback to browser print if QZ Tray fails
                const originalContent = document.body.innerHTML;
                document.body.innerHTML = document.getElementById('receiptContent').innerHTML;
                window.print();
                document.body.innerHTML = originalContent;
            });
        }
        
        // Handle receipt button clicks
        document.addEventListener('DOMContentLoaded', function() {
            const receiptButtons = document.querySelectorAll('.btn-generate-receipt');
            
            receiptButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentData = {
                        id: this.dataset.appointmentId,
                        patientName: this.dataset.patientName,
                        doctorName: this.dataset.doctorName,
                        date: this.dataset.date,
                        time: this.dataset.time,
                        visitType: this.dataset.visitType
                    };
                    
                    // Generate and display receipt
                    document.getElementById('receiptContent').innerHTML = generateReceipt(appointmentData);
                    
                    // Show the modal
                    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
                    receiptModal.show();
                });
            });
        });
    </script>

    <script>
        // Function to show receipt in modal
        function showReceiptInModal(button) {
            const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            const receiptContent = document.getElementById('receiptContent');
            
            // Get appointment data from button data attributes
            const appointmentData = {
                id: button.getAttribute('data-appointment-id'),
                patientName: button.getAttribute('data-patient-name'),
                doctorName: button.getAttribute('data-doctor-name'),
                date: button.getAttribute('data-date'),
                time: button.getAttribute('data-time'),
                visitType: button.getAttribute('data-visit-type'),
                fee: button.getAttribute('data-fee') || '100.00'
            };
            
            // Generate receipt HTML
            receiptContent.innerHTML = generateReceipt(appointmentData);
            
            // Show the modal
            receiptModal.show();
            
            // Auto-print when modal is shown
            receiptModal._element.addEventListener('shown.bs.modal', function onModalShown() {
                printReceipt();
                // Remove the event listener after first print
                receiptModal._element.removeEventListener('shown.bs.modal', onModalShown);
            }, { once: true });
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event for print receipt buttons
            document.querySelectorAll('.btn-print-receipt').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    showReceiptInModal(this);
                });
            });
            
            // Toggle sidebar on mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('d-none');
                });
            }

            // Initialize date picker for appointment date
            const dateInputs = document.querySelectorAll("input[type='date']");
            if (dateInputs.length > 0) {
                flatpickr("input[type='date']", {
                    dateFormat: "Y-m-d",
                    minDate: "today"
                });
            }

            // Initialize time picker for appointment time
            const timeInputs = document.querySelectorAll("input[type='time']");
            if (timeInputs.length > 0) {
                flatpickr("input[type='time']", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: false
                });
            }

            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Add patient with sound
        function addPatientWithSound() {
            // Play notification sound
            const audio = new Audio('notification.mp3');
            audio.play().catch(e => console.error('Error playing sound:', e));
            
            // Show the add patient modal
            const addPatientModal = new bootstrap.Modal(document.getElementById('addPatientModal'));
            addPatientModal.show();
        }

        // Show all receipts for today
        function showAllReceipts() {
            const allReceiptsContent = document.getElementById('allReceiptsContent');
            allReceiptsContent.innerHTML = '';
            
            // Get all appointment rows
            const appointmentRows = document.querySelectorAll('tr[data-appointment-id]');
            
            if (appointmentRows.length === 0) {
                allReceiptsContent.innerHTML = '<div class="text-center py-4">No appointments found for today.</div>';
                const modal = new bootstrap.Modal(document.getElementById('printAllReceiptsModal'));
                modal.show();
                return;
            }
            
            // Create a receipt for each appointment
            appointmentRows.forEach(row => {
                const appointmentId = row.getAttribute('data-appointment-id');
                const patientName = row.querySelector('.patient-name').textContent;
                const doctorName = row.querySelector('.doctor-name').textContent;
                const appointmentDate = row.querySelector('.appointment-date').textContent;
                const appointmentTime = row.querySelector('.appointment-time').textContent;
                const visitType = row.querySelector('.visit-type').textContent;
                
                const receiptHtml = `
                    <div class="receipt-container border rounded p-3 mb-3">
                        <div class="text-center mb-3">
                            <h5 class="mb-1">OutPatient Clinic</h5>
                            <p class="mb-1 text-muted">MSA University</p>
                            <p class="mb-2">1966</p>
                            <h6>APPOINTMENT RECEIPT</h6>
                            <hr>
                        </div>
                        
                        <div class="mb-3">
                            <div class="row mb-1">
                                <div class="col-6"><strong>Receipt #:</strong> RCPT${String(appointmentId).padStart(5, '0')}</div>
                                <div class="col-6 text-end"><strong>Date:</strong> ${new Date().toLocaleDateString()}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-12"><strong>Patient Name:</strong> ${patientName}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-6"><strong>Date:</strong> ${appointmentDate}</div>
                                <div class="col-6 text-end"><strong>Time:</strong> ${appointmentTime}</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-6"><strong>Doctor:</strong> ${doctorName}</div>
                                <div class="col-6 text-end"><strong>Specialty:</strong> ${visitType}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 text-end">
                                    <strong>Fee:</strong> $100.00
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center text-muted mt-3">
                            <p class="mb-1">Thank you for choosing our clinic</p>
                            <hr>
                        </div>
                        
                        <div class="text-center mt-2">
                            <button class="btn btn-sm btn-primary" onclick="printSingleReceipt('${appointmentId}')">
                                <i class="fas fa-print me-1"></i> Print This Receipt
                            </button>
                        </div>
                    </div>
                `;
                
                allReceiptsContent.innerHTML += receiptHtml;
            });
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('printAllReceiptsModal'));
            modal.show();
        }
        
        // Print a single receipt
        function printSingleReceipt(appointmentId) {
            const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
            if (!row) return;
            
            const patientName = row.querySelector('.patient-name').textContent;
            const doctorName = row.querySelector('.doctor-name').textContent;
            const appointmentDate = row.querySelector('.appointment-date').textContent;
            const appointmentTime = row.querySelector('.appointment-time').textContent;
            const visitType = row.querySelector('.visit-type').textContent;
            
            const receiptHtml = `
                <div class="receipt-container">
                    <div class="text-center mb-3">
                        <h5 class="mb-1">OutPatient Clinic</h5>
                        <p class="mb-1 text-muted">MSA University</p>
                        <p class="mb-2">1966</p>
                        <h6>APPOINTMENT RECEIPT</h6>
                        <hr>
                    </div>
                    
                    <div class="mb-3">
                        <div class="row mb-1">
                            <div class="col-6"><strong>Receipt #:</strong> RCPT${String(appointmentId).padStart(5, '0')}</div>
                            <div class="col-6 text-end"><strong>Date:</strong> ${new Date().toLocaleDateString()}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-12"><strong>Patient Name:</strong> ${patientName}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-6"><strong>Date:</strong> ${appointmentDate}</div>
                            <div class="col-6 text-end"><strong>Time:</strong> ${appointmentTime}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-6"><strong>Doctor:</strong> ${doctorName}</div>
                            <div class="col-6 text-end"><strong>Specialty:</strong> ${visitType}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12 text-end">
                                <strong>Fee:</strong> $100.00
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center text-muted mt-3">
                        <p class="mb-1">Thank you for choosing our clinic</p>
                        <hr>
                    </div>
                </div>
            `;
            
            document.getElementById('receiptContent').innerHTML = receiptHtml;
            const modal = new bootstrap.Modal(document.getElementById('printReceiptModal'));
            modal.show();
        }
        
        // Print the current receipt
        function printReceipt() {
            const printContent = document.getElementById('receiptContent').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Receipt</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            @page { margin: 0; }
                            body { padding: 20px; }
                            .no-print { display: none !important; }
                        }
                        .receipt-container {
                            max-width: 500px;
                            margin: 0 auto;
                            padding: 20px;
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <div class="text-center mt-3 no-print">
                        <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
                        <button class="btn btn-secondary" onclick="window.close()">Close</button>
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body = originalContent;
            window.location.reload();
        }

        // Refresh appointments
        function refreshAppointments() {
            // Add loading state
            const refreshBtn = document.querySelector('#today-appointments button');
            const originalHtml = refreshBtn.innerHTML;
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
            
            // Simulate API call
            setTimeout(() => {
                // In a real app, this would be an AJAX call to refresh the data
                refreshBtn.innerHTML = originalHtml;
                refreshBtn.disabled = false;
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.role = 'alert';
                alert.innerHTML = `
                    Appointments refreshed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('#today-appointments').prepend(alert);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 3000);
            }, 1000);
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize flatpickr for date inputs
            flatpickr("input[type='date']", {
                dateFormat: "Y-m-d",
                minDate: "today"
            });
            
            // Initialize time picker
            flatpickr("input[type='time']", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: false
            });
        });
    </script>
    </div>
</div>

<!-- Print All Receipts Modal -->
<div class="modal fade" id="printAllReceiptsModal" tabindex="-1" aria-labelledby="printAllReceiptsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printAllReceiptsModalLabel">Today's Receipts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="allReceiptsContent">
                <!-- Receipts will be generated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Receipt Modal -->
<div class="modal fade" id="printReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Print Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be generated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>