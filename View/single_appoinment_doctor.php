
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as a doctor
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor' || !isset($_SESSION['user']['id'])) {
    header('Location: /login.php');
    exit();
}

// Include necessary files
require_once __DIR__ . '/../Controller/DoctorController.php';

// Initialize variables
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error_message = '';
$appointment_data = [];
$treatment_plan = [];
// Patient data is now accessed via $appointment_data['patient']

// If we have an appointment ID, fetch the data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $appointment_id > 0) {
    $doctorController = new DoctorController();
    $result = $doctorController->viewAppointment($appointment_id);
    
    if ($result['success']) {
        $appointment_data = $result['appointment'];
        $treatment_plan = $result['treatment_plan'];
        // Patient data is now accessed via $appointment_data['patient']
        $current_date = $result['current_date'];
    } else {
        $error_message = $result['error'] ?? 'Failed to load appointment details.';
    }
} else if ($appointment_id <= 0) {
    $error_message = 'Invalid appointment ID.';
}

// Redirect if there was an error
if (!empty($error_message)) {
    $_SESSION['error'] = $error_message;
    header('Location: /doctor/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment - Dr.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
            background-attachment: fixed;
            position: relative;
            font-family: 'Tajawal', 'Segoe UI', Arial, sans-serif;
            color: #333;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cdefs%3E%3Cpattern id='bones' patternUnits='userSpaceOnUse' width='100' height='100'%3E%3Cpath d='M20,50 L30,45 L40,50 L50,45 L60,50 L70,45 L80,50' stroke='rgba(37, 99, 235, 0.1)' stroke-width='2' fill='none'/%3E%3Ccircle cx='25' cy='47' r='3' fill='rgba(37, 99, 235, 0.08)'/%3E%3Ccircle cx='75' cy='47' r='3' fill='rgba(37, 99, 235, 0.08)'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23bones)'/%3E%3C/svg%3E"),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 60 60'%3E%3Cdefs%3E%3Cpattern id='medical' patternUnits='userSpaceOnUse' width='60' height='60'%3E%3Cpath d='M30,10 L35,15 L30,20 L25,15 Z' fill='rgba(30, 58, 138, 0.06)'/%3E%3Cpath d='M10,30 L15,35 L20,30 L15,25 Z' fill='rgba(30, 58, 138, 0.06)'/%3E%3Cpath d='M50,30 L55,35 L60,30 L55,25 Z' fill='rgba(30, 58, 138, 0.06)'/%3E%3Cpath d='M30,50 L35,55 L30,60 L25,55 Z' fill='rgba(30, 58, 138, 0.06)'/%3E%3Ccircle cx='30' cy='30' r='8' fill='rgba(59, 130, 246, 0.05)'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='60' height='60' fill='url(%23medical)'/%3E%3C/svg%3E"),
                radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(30, 58, 138, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
            background-size: 200px 200px, 120px 120px, 300px 300px, 400px 400px, 500px 500px;
            background-position: 0 0, 60px 60px, 0 0, 0 0, 0 0;
            filter: blur(2px);
            pointer-events: none;
            z-index: -1;
            opacity: 0.6;
        }

        /* Header Styles */
        .appointment-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 24px rgba(30, 58, 138, 0.12);
            background-size: 200% 200%;
            animation: gradientMove 8s ease-in-out infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .appointment-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.2rem;
        }

        .appointment-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .header-actions .btn {
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .header-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Card Styles */
        .appointment-card,
        .progress-card,
        .treatment-plan-card,
        .sessions-card {
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(37, 99, 235, 0.08);
            margin-bottom: 1.5rem;
            border: 1px solid #e0e7ef;
            background: #fff;
            transition: transform 0.18s cubic-bezier(.4,2,.6,1), box-shadow 0.18s cubic-bezier(.4,2,.6,1);
        }

        .appointment-card:hover,
        .progress-card:hover,
        .treatment-plan-card:hover,
        .sessions-card:hover {
            transform: translateY(-6px) scale(1.015);
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.16);
            border-color: #2563eb33;
        }

        .card-header {
            background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%) !important;
            color: #fff !important;
            border-bottom: none;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Info Items */
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2563eb;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 500;
            color: #333;
        }

        /* Progress Circle */
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#2563eb 0%, #2563eb calc(var(--progress, 33) * 3.6deg), #e0e7ef calc(var(--progress, 33) * 3.6deg), #e0e7ef 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
        }

        .progress-circle::before {
            content: '';
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }

        .progress-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2563eb;
            z-index: 1;
            position: relative;
        }

        .progress-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .progress-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            gap: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        /* Treatment Plan */
        .plan-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .plan-item:last-child {
            border-bottom: none;
        }

        .plan-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 140px;
        }

        .plan-value {
            color: #4a5568;
            flex: 1;
        }

        /* Form Elements */
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border: none;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        }

        .btn-outline-primary {
            border: 1px solid #2563eb;
            color: #2563eb;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* View/Edit Modes */
        .view-mode {
            display: block;
        }
        
        .edit-mode {
            display: none;
        }
        
        .edit-mode.active {
            display: block;
        }
        
        .view-mode.active {
            display: none;
        }

        /* Avatar */
        .avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .gender-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .gender-male {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .gender-female {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }

        /* Responsive Adjustments */
        /* Timeline Styling */
        .timeline {
            position: relative;
            padding: 0 0 0 2.5rem;
            margin: 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 1.25rem;
            width: 2px;
            background: #e9ecef;
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            padding: 1.5rem 0;
            margin: 0;
            border-radius: 0;
            transition: all 0.2s ease;
            background: transparent;
        }

        .timeline-item:first-child {
            padding-top: 0;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item:hover {
            background: transparent;
            transform: none;
        }

        .timeline-marker {
            position: absolute;
            left: -2.5rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            background: #fff;
            border: 2px solid #dee2e6;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .timeline-item.completed .timeline-marker {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .timeline-item.scheduled .timeline-marker {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .timeline-item.cancelled .timeline-marker {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .timeline-marker i {
            font-size: 1rem;
        }

        .timeline-content {
            background: white;
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            margin-bottom: 1.25rem;
            transition: all 0.2s ease;
        }

        .timeline-item:last-child .timeline-content {
            margin-bottom: 0;
        }

        .timeline-item:hover .timeline-content {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }

        .timeline-content h6 {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .timeline-content p {
            color: #6c757d;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .timeline-content .text-muted {
            font-size: 0.85rem;
        }

        .session-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e9ecef;
        }

        .session-actions .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
        }

        .session-actions .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }

        .session-actions .dropdown-toggle::after {
            display: none;
        }

        @media (max-width: 768px) {
            .appointment-header h1 {
                font-size: 1.8rem;
            }
            
            .progress-circle {
                width: 100px;
                height: 100px;
            }
            
            .progress-circle::before {
                width: 70px;
                height: 70px;
            }
            
            .progress-number {
                font-size: 1.5rem;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            .plan-item {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .plan-label {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="appointment-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2><i class="fas fa-calendar-check me-2"></i>Appointment Details</h2>
                    <p class="mb-0">View and manage patient appointment</p>
                </div>
                <div>
                    <a href="doctor_dashboard.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Appointment Overview -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card appointment-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Appointment Details</h5>
                        <span class="badge bg-<?php echo $appointment_data['status'] === 'completed' ? 'success' : 'primary'; ?>">
                            <?php echo ucfirst($appointment_data['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="far fa-calendar-alt me-2"></i>Appointment Date</h6>
                                    <p class="mb-0">
                                        <?php 
                                        $appointmentDate = new DateTime($appointment_data['date']);
                                        echo $appointmentDate->format('l, F j, Y');
                                        ?>
                                    </p>
                                    <small class="text-muted">
                                        <?php 
                                        $appointmentTime = new DateTime($appointment_data['time']);
                                        echo $appointmentTime->format('g:i A');
                                        ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-user-md me-2"></i>Attending Physician</h6>
                                    <p class="mb-0">Dr. <?php echo htmlspecialchars($appointment_data['doctor']['name']); ?></p>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(ucfirst($appointment_data['doctor']['specialty'])); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                                    <p class="mb-0">
                                        <i class="fas fa-clinic-medical me-2"></i>
                                        <?php echo htmlspecialchars(ucfirst($appointment_data['location'])); ?> Department
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-notes-medical me-2"></i>Appointment Type</h6>
                                    <p class="mb-0">
                                        <span class="badge bg-info text-dark">
                                            <?php echo ucfirst($appointment_data['visit_type']); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-info-circle me-2"></i>Status</h6>
                                    <p class="mb-0">
                                        <?php 
                                        $statusClass = [
                                            'scheduled' => 'bg-primary',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'confirmed' => 'bg-info',
                                            'no_show' => 'bg-warning text-dark'
                                        ][$appointment_data['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $appointment_data['status'])); ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1"><i class="fas fa-clock me-2"></i>Duration</h6>
                                    <p class="mb-0">30 minutes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="fas fa-user-injured text-primary me-2"></i>
                            Patient Information
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <?php 
                                $avatarContent = '';
                                $patient = $appointment_data['patient'] ?? [];
                                if (!empty($patient['photo'])) {
                                    $avatarContent = '<img src="' . htmlspecialchars($patient['photo']) . '" class="rounded-circle" width="100" height="100" alt="Patient Photo">';
                                } else {
                                    $initials = !empty($patient['name']) ? strtoupper(substr($patient['name'], 0, 1)) : 'P';
                                    $avatarContent = '<div class="avatar" style="width: 100px; height: 100px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; color: #6c757d;">' . $initials . '</div>';
                                }
                                echo $avatarContent;
                                ?>
                                <?php 
                                $patient = $appointment_data['patient'] ?? [];
                                if (!empty($patient['gender'])): 
                                    $genderIcon = '';
                                    $genderBg = '';
                                    $genderLower = strtolower($patient['gender']);
                                    
                                    if (strpos($genderLower, 'male') !== false) {
                                        $genderIcon = 'mars';
                                        $genderBg = '#0d6efd';
                                    } elseif (strpos($genderLower, 'female') !== false) {
                                        $genderIcon = 'venus';
                                        $genderBg = '#d63384';
                                    } else {
                                        $genderIcon = 'genderless';
                                        $genderBg = '#6c757d';
                                    }
                                ?>
                                <span class="position-absolute bottom-0 end-0 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background-color: <?php echo $genderBg; ?>; color: white; font-size: 14px; border: 2px solid white;">
                                    <i class="fas fa-<?php echo $genderIcon; ?>"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <h4 class="mt-3 mb-1"><?php echo !empty($patient['name']) ? htmlspecialchars($patient['name']) : 'Patient Name'; ?></h4>
                            <?php if (!empty($patient['gender'])): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-<?php echo $genderIcon; ?> me-1"></i>
                                    <?php echo ucfirst(htmlspecialchars($patient['gender'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-uppercase text-muted small mb-0">Contact Details</h6>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#chatModal">
                                    <i class="fas fa-comment-dots me-1"></i> Chat with Patient
                                </button>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <?php 
                                $patient = $appointment_data['patient'] ?? [];
                                if (!empty($patient['phone'])): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-phone-alt text-primary me-2"></i>
                                        <a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($patient['phone']); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($patient['email'])): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($patient['email']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($patient['email']); ?>
                                        </a>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($patient['address'])): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-map-marker-alt text-primary me-2 mt-1"></i>
                                        <span><?php echo htmlspecialchars($patient['address']); ?></span>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($patient['age'])): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-birthday-cake text-primary me-2"></i>
                                        <span>Age: <?php echo htmlspecialchars($patient['age']); ?> years</span>
                                    </div>
                                </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($patient['blood_type'])): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tint text-primary me-2"></i>
                                        <span>Blood Type: <?php echo htmlspecialchars($patient['blood_type']); ?></span>
                                    </div>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Plan -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card treatment-plan">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; color: white;">
                        <h5 class="mb-0 text-white"><i class="fas fa-file-medical me-2"></i>Treatment Plan</h5>
                        <div class="d-flex">
                            <button type="button" class="btn btn-sm btn-light me-2" id="editPlanBtn">
                                <i class="fas fa-edit me-1"></i> Edit Plan
                            </button>
                            <div id="formButtons" class="d-none">
                                <button type="button" class="btn btn-sm btn-outline-light me-2" id="cancelEdit">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                                <button type="submit" form="treatmentPlanForm" class="btn btn-sm btn-light">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="treatmentPlanForm">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                            <input type="hidden" name="patient_id" value="<?php echo $appointment_data['patient']['id']; ?>">
                            <input type="hidden" name="treatment_plan_id" value="<?php echo $treatment_plan['id'] ?? ''; ?>">
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-muted small mb-1">Diagnosis</label>
                                    <div class="view-mode">
                                        <div class="bg-light p-3 rounded">
                                            <?php if (!empty($treatment_plan['diagnosis'])): ?>
                                                <?php echo nl2br(htmlspecialchars($treatment_plan['diagnosis'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">No diagnosis recorded</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea class="form-control" name="diagnosis" rows="3" placeholder="Enter diagnosis..."><?php 
                                            echo htmlspecialchars($treatment_plan['diagnosis'] ?? ''); 
                                        ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-muted small mb-1">Duration</label>
                                    <div class="view-mode">
                                        <div class="bg-light p-3 rounded">
                                            <?php echo !empty($treatment_plan['duration']) ? htmlspecialchars($treatment_plan['duration']) : 'Not specified'; ?>
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <select class="form-select" name="duration">
                                            <option value="">Select duration...</option>
                                            <option value="1 week" <?php echo ($treatment_plan['duration'] ?? '') === '1 week' ? 'selected' : ''; ?>>1 week</option>
                                            <option value="2 weeks" <?php echo ($treatment_plan['duration'] ?? '') === '2 weeks' ? 'selected' : ''; ?>>2 weeks</option>
                                            <option value="1 month" <?php echo ($treatment_plan['duration'] ?? '') === '1 month' ? 'selected' : ''; ?>>1 month</option>
                                            <option value="2 months" <?php echo ($treatment_plan['duration'] ?? '') === '2 months' ? 'selected' : ''; ?>>2 months</option>
                                            <option value="3 months" <?php echo ($treatment_plan['duration'] ?? '') === '3 months' ? 'selected' : ''; ?>>3 months</option>
                                            <option value="6 months" <?php echo ($treatment_plan['duration'] ?? '') === '6 months' ? 'selected' : ''; ?>>6 months</option>
                                            <option value="1 year" <?php echo ($treatment_plan['duration'] ?? '') === '1 year' ? 'selected' : ''; ?>>1 year</option>
                                            <option value="Ongoing" <?php echo ($treatment_plan['duration'] ?? '') === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold text-muted small mb-1">Total Sessions</label>
                                    <div class="view-mode">
                                        <div class="bg-light p-3 rounded">
                                            <?php echo !empty($treatment_plan['total_sessions']) ? $treatment_plan['total_sessions'] : 'Not specified'; ?>
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <input type="number" class="form-control" name="total_sessions" 
                                               value="<?php echo $treatment_plan['total_sessions'] ?? 1; ?>" min="1">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small mb-1">Treatment Notes</label>
                                    <div class="view-mode">
                                        <div class="bg-light p-3 rounded">
                                            <?php if (!empty($treatment_plan['notes'])): ?>
                                                <?php echo nl2br(htmlspecialchars($treatment_plan['notes'])); ?>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">No treatment notes available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea class="form-control" name="notes" rows="5" placeholder="Enter detailed treatment notes..."><?php 
                                            echo htmlspecialchars($treatment_plan['notes'] ?? ''); 
                                        ?></textarea>
                                        <div class="form-text">You can use markdown formatting in your notes.</div>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar (View Mode Only) -->
                                <?php if (!empty($treatment_plan['sessions'])): ?>
                                <div class="col-12 view-mode">
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-muted small">TREATMENT PROGRESS</span>
                                            <span class="text-muted small">
                                                <?php 
                                                $total_sessions = count($treatment_plan['sessions']);
                                                $completed_sessions = 0;
                                                foreach ($treatment_plan['sessions'] as $session) {
                                                    if ($session['status'] === 'completed') $completed_sessions++;
                                                }
                                                $progress = $total_sessions > 0 ? round(($completed_sessions / $total_sessions) * 100) : 0;
                                                echo "$completed_sessions of $total_sessions sessions completed";
                                                ?>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" 
                                                 aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<?php
// ... existing content ...
?>

        <!-- Treatment Sessions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; color: white;">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Treatment Sessions</h5>
                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                            <i class="fas fa-plus me-1"></i> Add Session
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($treatment_plan['sessions'])): ?>
                            <div class="timeline">
                                <?php foreach ($treatment_plan['sessions'] as $session): 
                                    $is_completed = $session['status'] === 'completed';
                                    $is_scheduled = $session['status'] === 'scheduled';
                                    $status_class = $is_completed ? 'completed' : ($is_scheduled ? 'scheduled' : 'cancelled');
                                    $status_icon = $is_completed ? 'check' : ($is_scheduled ? 'calendar-check' : 'times');
                                ?>
                                    <div class="timeline-item <?php echo $status_class; ?>" id="session-<?php echo $session['id']; ?>">
                                        <div class="timeline-marker">
                                            <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-calendar-check text-primary me-2"></i>
                                                    Session #<?php echo $session['session_number']; ?>
                                                </h6>
                                                
                                            </div>
                                            
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="far fa-calendar text-muted me-2"></i>
                                                <span class="text-muted">
                                                    <?php echo date('l, F j, Y', strtotime($session['session_date'])); ?>
                                                    at <?php echo date('h:i A', strtotime($session['session_time'] ?? '12:00:00')); ?>
                                                </span>
                                            </div>
                                            
                                            <?php if (!empty($session['notes'])): ?>
                                                <div class="bg-light p-3 rounded-2 mt-3">
                                                    <p class="mb-0 text-muted">
                                                        <i class="fas fa-sticky-note text-primary me-2"></i>
                                                        <?php echo nl2br(htmlspecialchars($session['notes'])); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="session-actions">
                                                <span class="badge <?php 
                                                    echo $is_completed ? 'bg-success' : ($is_scheduled ? 'bg-info' : 'bg-secondary');
                                                ?> text-white">
                                                    <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                    <?php echo ucfirst($session['status']); ?>
                                                </span>
                                                <div class="ms-auto d-flex">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No treatment sessions have been scheduled yet.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                    <i class="fas fa-plus me-1"></i> Schedule First Session
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-5">
            <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="doctor_dashboard.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button class="btn btn-outline-primary me-2" id="printAppointmentBtn">
                                <i class="fas fa-print me-2"></i>Print
                            </button>
    
    <!-- Edit Treatment Plan Modal -->
    <div class="modal fade" id="editTreatmentPlanModal" tabindex="-1" aria-labelledby="editTreatmentPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTreatmentPlanModalLabel">
                        <?php echo !empty($treatment_plan['diagnosis']) ? 'Edit' : 'Create'; ?> Treatment Plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="treatmentPlanForm" action="../Controller/DoctorController.php?action=updateTreatmentPlan" method="POST">
                    <input type="hidden" name="treatment_plan_id" value="<?php echo $treatment_plan['id'] ?? ''; ?>">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required><?php 
                                echo htmlspecialchars($treatment_plan['diagnosis'] ?? ''); 
                            ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Treatment Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" 
                                           value="<?php echo htmlspecialchars($treatment_plan['duration'] ?? ''); ?>" required>
                                    <div class="form-text">e.g., 4 weeks, 3 months, etc.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_sessions" class="form-label">Total Sessions</label>
                                    <input type="number" class="form-control" id="total_sessions" name="total_sessions" 
                                           min="1" value="<?php echo $treatment_plan['total_sessions'] ?? 1; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php 
                                echo htmlspecialchars($treatment_plan['notes'] ?? ''); 
                            ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSessionModalLabel">Add Treatment Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addSessionForm" action="../Controller/DoctorController.php?action=addTreatmentSession" method="POST">
                    <input type="hidden" name="treatment_plan_id" value="<?php echo $treatment_plan['id'] ?? ''; ?>">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="session_date" class="form-label">Session Date</label>
                            <input type="date" class="form-control" id="session_date" name="session_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="session_time" class="form-label">Session Time</label>
                            <input type="time" class="form-control" id="session_time" name="session_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="session_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="session_notes" name="session_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Session Modal -->
    <div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSessionModalLabel">Edit Treatment Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSessionForm" action="../Controller/DoctorController.php?action=updateSession" method="POST">
                    <input type="hidden" name="session_id" id="edit_session_id">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_session_date" class="form-label">Session Date</label>
                            <input type="date" class="form-control" id="edit_session_date" name="session_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_session_time" class="form-label">Session Time</label>
                            <input type="time" class="form-control" id="edit_session_time" name="session_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_session_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_session_notes" name="session_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="chatModalLabel">
                        <i class="fas fa-comments me-2"></i>Chat with <?php echo htmlspecialchars($appointment_data['patient_first_name'] . ' ' . $appointment_data['patient_last_name']); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="chat-container" style="height: 400px; overflow-y: auto;" id="chatMessages">
                        <!-- Chat messages will be loaded here -->
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-comment-alt fa-3x mb-3"></i>
                            <p>Start your conversation with <?php echo htmlspecialchars($appointment_data['patient_first_name']); ?></p>
                        </div>
                    </div>
                    <div class="p-3 border-top">
                        <form id="chatForm">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                            <input type="hidden" name="sender_type" value="doctor">
                            <div class="input-group">
                                <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style media="print">
        body * {
            visibility: hidden;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    </style>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            // Base URL for AJAX requests
            const baseUrl = window.location.origin + '/MVC/Controller/DoctorController.php';
            
            // Debug function
            function debugLog(message, data = null) {
                const timestamp = new Date().toISOString();
                const logMessage = `[${timestamp}] ${message}`;
                console.log(logMessage, data || '');
                
                // Optional: Log to a debug div if it exists
                const debugDiv = $('#debug-log');
                if (debugDiv.length) {
                    debugDiv.prepend(`<div>${logMessage} ${data ? JSON.stringify(data) : ''}</div>`);
                }
            }
            
            // Create debug div if it doesn't exist
            if ($('#debug-log').length === 0) {
                $('body').append(`
                    <div id="debug-log" style="position: fixed; bottom: 0; right: 0; width: 300px; 
                        height: 200px; overflow-y: auto; background: rgba(0,0,0,0.8); color: #fff; 
                        padding: 10px; font-size: 12px; z-index: 9999; display: none;">
                        <div style="position: sticky; top: 0; background: #000; padding: 5px;">
                            Debug Console <button id="toggle-debug" style="float: right;">Hide</button>
                        </div>
                    </div>
                `);
                
                $('#toggle-debug').on('click', function() {
                    const $debug = $('#debug-log');
                    const isVisible = $debug.is(':visible');
                    $debug.toggle(!isVisible);
                    $(this).text(isVisible ? 'Show' : 'Hide');
                });
            }
            
            // Handle delete session
            $(document).on('click', '.delete-session', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(this);
                const sessionId = $button.data('session-id');
                const sessionElement = $button.closest('.timeline-item');
                
                if (confirm('Are you sure you want to delete this session? This action cannot be undone.')) {
                    // Show loading state
                    const originalHtml = $button.html();
                    $button.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
                    $button.prop('disabled', true);
                    
                    debugLog('Deleting session:', { sessionId });
                    
                    // Prepare request data
                    const requestData = {
                        action: 'deleteSession',
                        session_id: sessionId,
                        appointment_id: '<?php echo $appointment_id; ?>',
                        _token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>' // Add CSRF token if needed
                    };
                    
                    debugLog('Sending delete request:', requestData);
                    
                    // AJAX call to delete session
                    $.ajax({
                        url: baseUrl,
                        type: 'POST',
                        data: requestData,
                        dataType: 'json',
                        xhrFields: {
                            withCredentials: true // Include cookies in the request
                        },
                        crossDomain: true,
                        success: function(response) {
                            debugLog('Delete response:', response);
                            if (response.success) {
                                // Remove the session element with animation
                                sessionElement.fadeOut(300, function() {
                                    $(this).remove();
                                    showAlert('Session deleted successfully', 'success');
                                });
                            } else {
                                showAlert(response.error || 'Failed to delete session', 'danger');
                                $button.html(originalHtml).prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            showAlert('An error occurred while deleting the session', 'danger');
                            $button.html(originalHtml).prop('disabled', false);
                        }
                    });
                }
            });
            
            // Handle mark as completed
            $(document).on('click', '.mark-completed', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(this);
                const sessionId = $button.data('session-id');
                const sessionElement = $button.closest('.timeline-item');
                
                if (confirm('Are you sure you want to mark this session as completed?')) {
                    // Show loading state
                    const originalHtml = $button.html();
                    $button.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
                    $button.prop('disabled', true);
                    
                    debugLog('Marking session as completed:', { sessionId });
                    
                    // Prepare request data
                    const requestData = {
                        action: 'updateSessionStatus',
                        session_id: sessionId,
                        status: 'completed',
                        appointment_id: '<?php echo $appointment_id; ?>',
                        _token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>' // Add CSRF token if needed
                    };
                    
                    debugLog('Sending complete request:', requestData);
                    
                    // AJAX call to update session status
                    $.ajax({
                        url: baseUrl,
                        type: 'POST',
                        data: requestData,
                        dataType: 'json',
                        xhrFields: {
                            withCredentials: true // Include cookies in the request
                        },
                        crossDomain: true,
                        success: function(response) {
                            debugLog('Complete response:', response);
                            if (response.success) {
                                // Update UI to show completed status
                                sessionElement.removeClass('scheduled').addClass('completed');
                                sessionElement.find('.timeline-marker i')
                                    .removeClass('fa-clock text-info')
                                    .addClass('fa-check-circle text-success');
                                sessionElement.find('.badge')
                                    .removeClass('bg-info')
                                    .addClass('bg-success')
                                    .html('<i class="fas fa-check-circle me-1"></i> Completed');
                                
                                // Remove the complete button
                                $button.closest('.mark-completed').remove();
                                
                                showAlert('Session marked as completed', 'success');
                            } else {
                                showAlert(response.error || 'Failed to update session status', 'danger');
                                $button.html(originalHtml).prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            showAlert('An error occurred while updating the session', 'danger');
                            $button.html(originalHtml).prop('disabled', false);
                        }
                    });
                }
            });
            
            // Handle edit session form submission
            $('#editSessionForm').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const originalBtnText = $submitBtn.html();
                
                // Show loading state
                $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
                $submitBtn.prop('disabled', true);
                
                // Add CSRF token if needed
                let formData = $form.serialize();
                if ('<?php echo $_SESSION['csrf_token'] ?? ''; ?>') {
                    formData += '&_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
                }
                
                debugLog('Saving session:', formData);
                
                $.ajax({
                    url: baseUrl,
                    type: 'POST',
                    data: formData + '&action=updateSession',
                    xhrFields: {
                        withCredentials: true // Include cookies in the request
                    },
                    crossDomain: true,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        debugLog('Save response:', response);
                        if (response.success) {
                            // Update the UI with the new data
                            const sessionElement = $('#session-' + response.session_id);
                            const dateObj = new Date(response.session_date);
                            const formattedDate = dateObj.toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            });
                            
                            // Update the session date display
                            sessionElement.find('.session-date').text(formattedDate);
                            
                            // Update the button data attributes
                            const $editBtn = sessionElement.find('.edit-session');
                            $editBtn.data('session-date', response.session_date);
                            $editBtn.data('session-time', response.session_time);
                            $editBtn.data('session-notes', response.notes);
                            
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editSessionModal'));
                            if (modal) modal.hide();
                            
                            showAlert('Session updated successfully', 'success');
                        } else {
                            showAlert(response.error || 'Failed to update session', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        showAlert('An error occurred while updating the session', 'danger');
                    },
                    complete: function() {
                        $submitBtn.html(originalBtnText).prop('disabled', false);
                    }
                });
            });
            
            // Function to show alert messages
            function showAlert(message, type) {
                // Remove any existing alerts
                $('.alert-dismissible').remove();
                
                // Create alert HTML
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                         role="alert" style="z-index: 1100; min-width: 300px; text-align: center;">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                // Add alert to body
                $('body').append(alertHtml);
                
                // Auto-remove alert after 5 seconds
                setTimeout(() => {
                    $('.alert').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
            
            // Function to show confirmation dialog
            function showConfirmation(message) {
                // Remove any existing confirmation dialogs
                $('.confirmation-dialog').remove();
                
                // Create and show the confirmation dialog
                const dialog = $(`
                    <div class="confirmation-dialog position-fixed top-50 start-50 translate-middle p-4 bg-white rounded shadow-lg" 
                         style="z-index: 1090; width: 90%; max-width: 400px;">
                        <div class="mb-3">${message}</div>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-secondary cancel-btn">Cancel</button>
                            <button class="btn btn-sm btn-danger confirm-btn">Confirm</button>
                        </div>
                    </div>
                `);
                
                // Add overlay
                const overlay = $('<div class="modal-backdrop show" style="z-index: 1080;"></div>');
                
                // Add to body
                $('body').append(overlay).append(dialog);
                
                // Handle button clicks
                return new Promise((resolve) => {
                    dialog.on('click', '.confirm-btn', function() {
                        dialog.remove();
                        overlay.remove();
                        resolve(true);
                    });
                    
                    dialog.on('click', '.cancel-btn', function() {
                        dialog.remove();
                        overlay.remove();
                        resolve(false);
                    });
                });
            }
            
            // Handle delete session
            $(document).on('click', '.delete-session', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(this);
                const sessionId = $button.data('session-id');
                const sessionElement = $button.closest('.timeline-item');
                
                // Store scroll position
                const scrollPosition = $(window).scrollTop();
                
                // Show confirmation dialog
                const confirmDelete = confirm('Are you sure you want to delete this session? This action cannot be undone.');
                
                // Restore scroll position
                $(window).scrollTop(scrollPosition);
                
                if (confirmDelete) {
                    // Show loading state
                    const originalHtml = $button.html();
                    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
                    
                    $.ajax({
                        url: '../Controller/DoctorController.php?action=deleteSession',
                        type: 'POST',
                        data: { 
                            session_id: sessionId,
                            appointment_id: <?php echo $appointment_id; ?>
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                sessionElement.fadeOut(300, function() {
                                    $(this).remove();
                                    showAlert('Session deleted successfully', 'success');
                                });
                            } else {
                                showAlert('Error: ' + (response.error || 'Failed to delete session'), 'danger');
                                $button.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            showAlert('An error occurred while deleting the session', 'danger');
                            $button.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
            
            // Handle mark as completed
            $(document).on('click', '.mark-completed', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(this);
                const sessionId = $button.data('session-id');
                const sessionElement = $button.closest('.timeline-item');
                
                // Store scroll position
                const scrollPosition = $(window).scrollTop();
                
                // Show confirmation dialog
                const confirmComplete = confirm('Are you sure you want to mark this session as completed?');
                
                // Restore scroll position
                $(window).scrollTop(scrollPosition);
                
                if (confirmComplete) {
                    // Show loading state
                    const originalHtml = $button.html();
                    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
                    
                    $.ajax({
                        url: '../Controller/DoctorController.php?action=updateSessionStatus',
                        type: 'POST',
                        data: {
                            session_id: sessionId,
                            status: 'completed',
                            appointment_id: <?php echo $appointment_id; ?>
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Update the UI to show completed status
                                sessionElement.removeClass('scheduled').addClass('completed');
                                sessionElement.find('.timeline-marker i')
                                    .removeClass('fa-clock text-info')
                                    .addClass('fa-check-circle text-success');
                                sessionElement.find('.session-actions .badge')
                                    .removeClass('bg-info')
                                    .addClass('bg-success')
                                    .html('<i class="fas fa-check-circle me-1"></i> Completed');
                                
                                // Remove the complete button since it's no longer needed
                                $button.remove();
                                
                                // Show success message
                                showAlert('Session marked as completed', 'success');
                            } else {
                                showAlert(response.error || 'Failed to update session status', 'danger');
                                $button.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function() {
                            showAlert('An error occurred while updating the session', 'danger');
                            $button.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
            
            // Handle edit session
            $(document).on('click', '.edit-session', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $button = $(this);
                const sessionId = $button.data('session-id');
                const sessionDate = $button.data('session-date');
                const sessionTime = $button.data('session-time');
                const sessionNotes = $button.data('session-notes');
                
                // Set values in the edit modal
                $('#editSessionId').val(sessionId);
                $('#editSessionDate').val(sessionDate);
                $('#editSessionTime').val(sessionTime);
                $('#editSessionNotes').val(sessionNotes);
                
                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('editSessionModal'));
                editModal.show();
                
                const sessionId = $(this).data('session-id');
                const sessionDate = $(this).data('session-date');
                const sessionTime = $(this).data('session-time');
                const sessionNotes = $(this).data('session-notes');
                
                // Set values in the edit modal
                $('#editSessionId').val(sessionId);
                $('#editSessionDate').val(sessionDate);
                $('#editSessionTime').val(sessionTime || '12:00');
                $('#editSessionNotes').val(sessionNotes);
                
                // Show the edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editSessionModal'));
                editModal.show();
                
                // Restore scroll position after a short delay to ensure modal is shown
                setTimeout(() => {
                    $(window).scrollTop(scrollPosition);
                }, 100);
            });
            
            // Handle update session form submission
            $('#editSessionForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                // Show loading state
                const submitBtn = $('#updateSessionBtn');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...');
                
                $.ajax({
                    url: '../Controller/DoctorController.php?action=updateSession',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (response.success) {
                            // Close the modal
                            $('#editSessionModal').modal('hide');
                            // Show success message
                            showAlert('Session updated successfully', 'success');
                            // Reload the page to reflect changes
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showAlert('Error: ' + (response.error || 'Failed to update session'), 'danger');
                        }
                    },
                    error: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        showAlert('An error occurred while updating the session', 'danger');
                    }
                });
            });
            
{{ ... }}
            // Handle edit button click - Open modal instead of inline edit
            $('#editPlanBtn').on('click', function() {
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editTreatmentPlanModal'));
                modal.show();
            });
            
            // Handle treatment plan form submission
            $('#editTreatmentPlanForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
                
                // Get form data
                const formData = $(this).serialize();
                
                // Submit via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editTreatmentPlanModal'));
                            modal.hide();
                            
                            // Show success message
                            const toast = `
                                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                        <div class="toast-header bg-success text-white">
                                            <strong class="me-auto">Success</strong>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                                        </div>
                                        <div class="toast-body">
                                            <i class="fas fa-check-circle me-2"></i> ${response.message || 'Treatment plan saved successfully!'}
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('body').append(toast);
                            
                            // Remove toast after 3 seconds
                            setTimeout(() => {
                                $('.toast').remove();
                            }, 3000);
                            
                            // Reload the page to reflect changes
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('Error: ' + (response.error || 'Failed to save treatment plan.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving the treatment plan. Please try again.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Handle treatment plan form submission
            $('#editTreatmentPlanModal form').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
                
                // Get form data
                const formData = $(this).serialize();
                
                // Submit via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editTreatmentPlanModal'));
                            modal.hide();
                            
                            // Show success message
                            const toast = `
                                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                        <div class="toast-header bg-success text-white">
                                            <strong class="me-auto">Success</strong>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                                        </div>
                                        <div class="toast-body">
                                            <i class="fas fa-check-circle me-2"></i> ${response.message || 'Treatment plan saved successfully!'}
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('body').append(toast);
                            
                            // Remove toast after 3 seconds
                            setTimeout(() => {
                                $('.toast').remove();
                            }, 3000);
                            
                            // Reload the page to reflect changes
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('Error: ' + (response.error || 'Failed to save treatment plan.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving the treatment plan. Please try again.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Handle add session form submission
            $('#addSessionForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addSessionModal'));
                            modal.hide();
                            
                            // Show success message
                            const toast = `
                                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                        <div class="toast-header bg-success text-white">
                                            <strong class="me-auto">Success</strong>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                                        </div>
                                        <div class="toast-body">
                                            <i class="fas fa-check-circle me-2"></i> Session added successfully!
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('body').append(toast);
                            
                            // Remove toast after 3 seconds
                            setTimeout(() => {
                                $('.toast').remove();
                            }, 3000);
                            
                            // Reload the page to reflect changes
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('Error: ' + (response.error || 'Failed to add session.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while adding the session. Please try again.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Handle edit session form
            $('#editSessionForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: '../Controller/DoctorController.php?action=updateSession',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Session updated successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.error || 'Failed to update session.'));
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
            
            // Handle mark as completed
            // Handle edit session form submission
            $('#editSessionForm').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const originalBtnText = $submitBtn.html();
                
                // Show loading state
                $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                
                $.ajax({
                    url: '../Controller/DoctorController.php?action=updateSession',
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update the session in the UI
                            const sessionId = response.session_id || $('#editSessionId').val();
                            const sessionElement = $(`#session-${sessionId}`);
                            
                            if (sessionElement.length) {
                                // Update the date and notes in the UI
                                const formattedDate = new Date(response.session_date).toLocaleDateString();
                                sessionElement.find('.session-date').text(formattedDate);
                                sessionElement.find('.session-notes').text(response.notes || '');
                                
                                // Update the button data attributes
                                const $editBtn = sessionElement.find('.edit-session');
                                $editBtn.data('session-date', response.session_date);
                                $editBtn.data('session-time', response.session_time || '12:00');
                                $editBtn.data('session-notes', response.notes || '');
                                
                                // Show success message
                                showAlert('Session updated successfully', 'success');
                            } else {
                                showAlert('Session updated, but could not update UI', 'warning');
                            }
                            
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editSessionModal'));
                            if (modal) modal.hide();
                            
                        } else {
                            showAlert('Error: ' + (response.error || 'Failed to update session'), 'danger');
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                        }
                    },
                    error: function() {
                        showAlert('An error occurred while updating the session', 'danger');
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
            
            // Handle delete session
            $('.delete-session').on('click', function(e) {
                e.preventDefault();
                const sessionId = $(this).data('session-id');
                
                if (confirm('Are you sure you want to delete this session? This action cannot be undone.')) {
                    $.ajax({
                        url: '../Controller/DoctorController.php?action=deleteSession',
                        type: 'POST',
                        data: {
                            session_id: sessionId,
                            appointment_id: <?php echo $appointment_id; ?>
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Session deleted successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + (response.error || 'Failed to delete session.'));
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            });
            
            // Handle complete appointment button
            $('#completeAppointmentBtn').on('click', function() {
                if (confirm('Are you sure you want to mark this appointment as completed?')) {
                    $.ajax({
                        url: '../Controller/DoctorController.php?action=completeAppointment',
                        type: 'POST',
                        data: {
                            appointment_id: <?php echo $appointment_id; ?>
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Appointment marked as completed!');
                                location.reload();
                            } else {
                                alert('Error: ' + (response.error || 'Failed to complete appointment.'));
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            });
            
            // Handle start chat button
            // Print functionality
            $('#printAppointmentBtn').on('click', function() {
                // Create a print section
                let printContent = `
                    <div class="print-section p-4">
                        <div class="text-center mb-4">
                            <h3 class="mb-1">${$('.appointment-header h2').text()}</h3>
                            <p class="text-muted">${new Date().toLocaleDateString()}</p>
                        </div>
                        ${$('.appointment-card').html()}
                        ${$('.treatment-plan').html()}
                        ${$('.card:contains("Treatment Sessions")').html()}
                    </div>
                `;
                
                // Open print window
                let printWindow = window.open('', '', 'width=900,height=800');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Appointment Details</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .card { border: 1px solid #dee2e6; margin-bottom: 20px; }
                            .card-header { background-color: #f8f9fa; font-weight: bold; }
                            .text-muted { color: #6c757d !important; }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                        <script>
                            window.onload = function() {
                                window.print();
                                setTimeout(function() { window.close(); }, 100);
                            };
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            });
            
            // Chat functionality
            let chatModal = document.getElementById('chatModal');
            if (chatModal) {
                chatModal.addEventListener('show.bs.modal', function() {
                    loadChatMessages();
                    // Start polling for new messages every 5 seconds
                    window.chatPolling = setInterval(loadChatMessages, 5000);
                });
                
                chatModal.addEventListener('hidden.bs.modal', function() {
                    // Stop polling when modal is closed
                    clearInterval(window.chatPolling);
                });
            }
            
            // Load chat messages
            function loadChatMessages() {
                $.ajax({
                    url: '../Controller/ChatController.php?action=getMessages',
                    type: 'GET',
                    data: {
                        appointment_id: <?php echo $appointment_id; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let messagesHtml = '';
                            response.messages.forEach(function(message) {
                                const isDoctor = message.sender_type === 'doctor';
                                messagesHtml += `
                                    <div class="d-flex mb-3 ${isDoctor ? 'justify-content-end' : 'justify-content-start'}">
                                        <div class="card ${isDoctor ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="fw-bold">
                                                        ${isDoctor ? 'You' : '<?php echo addslashes($appointment_data["patient_first_name"]); ?>'}
                                                    </small>
                                                    <small class="ms-2">
                                                        ${moment(message.created_at).format('h:mm A')}
                                                    </small>
                                                </div>
                                                <p class="mb-0">${message.message}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            $('#chatMessages').html(messagesHtml);
                            // Scroll to bottom
                            const chatContainer = document.getElementById('chatMessages');
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }
                });
            }
            
            // Handle chat form submission
            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const messageInput = $(this).find('input[name="message"]');
                
                $.ajax({
                    url: '../Controller/ChatController.php?action=sendMessage',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Clear input
                            messageInput.val('');
                            // Reload messages
                            loadChatMessages();
                        } else {
                            alert('Failed to send message: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('An error occurred while sending the message.');
                    }
                });
            });
            
            // Handle update session button click
            $('#updateSessionBtn').on('click', function() {
                $('#editSessionForm').submit();
            });
            
            // Initialize datepicker for edit session modal
            $('#editSessionModal').on('shown.bs.modal', function() {
                $('#editSessionDate').focus();
            });
        });
    </script>
    
    <!-- Edit Session Modal -->
    <!-- Edit Treatment Plan Modal -->
    <div class="modal fade" id="editTreatmentPlanModal" tabindex="-1" aria-labelledby="editTreatmentPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editTreatmentPlanModalLabel">Edit Treatment Plan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTreatmentPlanForm" action="../Controller/DoctorController.php?action=updateTreatmentPlan" method="POST">
                    <input type="hidden" name="treatment_plan_id" value="<?php echo $treatment_plan['id'] ?? ''; ?>">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required><?php echo htmlspecialchars($treatment_plan['diagnosis'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" value="<?php echo htmlspecialchars($treatment_plan['duration'] ?? '4 weeks'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_sessions" class="form-label">Total Sessions</label>
                                    <input type="number" class="form-control" id="total_sessions" name="total_sessions" min="1" value="<?php echo $treatment_plan['total_sessions'] ?? 1; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Treatment Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($treatment_plan['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Session Modal -->
    <div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSessionModalLabel">Edit Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSessionForm">
                        <input type="hidden" name="session_id" id="editSessionId">
                        <div class="mb-3">
                            <label for="editSessionDate" class="form-label">Session Date</label>
                            <input type="date" class="form-control" id="editSessionDate" name="session_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSessionNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="editSessionNotes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateSessionBtn">Update Session</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

