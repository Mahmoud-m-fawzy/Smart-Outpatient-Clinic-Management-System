<?php
session_start();
require_once '../Controller/DoctorController.php';
require_once '../model/Database.php';

// Check if appointment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid appointment ID');
}

$appointment_id = (int)$_GET['id'];

// Initialize database connection
$db = new Database();
$conn = $db->connectToDB();

// Function to fetch appointment details
function getAppointmentDetails($conn, $appointment_id) {
    $sql = "SELECT a.*, 
                   p.FN as patient_first_name, p.LN as patient_last_name, p.age, p.gender, p.phone, p.email,
                   d.FN as doctor_first_name, d.LN as doctor_last_name, d.specialty,
                   ds.start_time, ds.end_time
            FROM appointment a
            JOIN patient p ON a.patient_id = p.id
            JOIN doctor d ON a.doctor_id = d.id
            JOIN doctor_schedule ds ON a.doctor_id = ds.doctor_id AND a.day_of_week = ds.day_of_week
            WHERE a.id = ?
            LIMIT 1";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $appointment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($appointment = mysqli_fetch_assoc($result)) {
        return $appointment;
    }
    return null;
}

// Function to fetch treatment plan with sessions
function getTreatmentPlan($conn, $patient_id, $appointment_id) {
    // First, get the treatment plan
    $sql = "SELECT * FROM treatment_plans WHERE patient_id = ? AND appointment_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $patient_id, $appointment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($plan = mysqli_fetch_assoc($result)) {
        // Now fetch the treatment sessions for this plan
        $sessions_sql = "SELECT * FROM treatment_sessions 
                        WHERE treatment_plan_id = ? 
                        ORDER BY session_number ASC";
        $sessions_stmt = mysqli_prepare($conn, $sessions_sql);
        mysqli_stmt_bind_param($sessions_stmt, 'i', $plan['id']);
        mysqli_stmt_execute($sessions_stmt);
        $sessions_result = mysqli_stmt_get_result($sessions_stmt);
        
        $sessions = [];
        $completed_sessions = 0;
        $next_session = null;
        
        while ($session = mysqli_fetch_assoc($sessions_result)) {
            $sessions[] = $session;
            if ($session['status'] === 'completed') {
                $completed_sessions++;
            }
            // Find the next upcoming session
            if (!$next_session && $session['status'] === 'scheduled') {
                $next_session = $session;
            }
        }
        
        // Add sessions data to the plan
        $plan['sessions'] = $sessions;
        $plan['completed_sessions'] = $completed_sessions;
        $plan['next_session'] = $next_session ? 
            'Session ' . $next_session['session_number'] . ' on ' . $next_session['session_date'] : 
            'No upcoming sessions';
            
        return $plan;
    }
    
    // Return default values if no treatment plan exists
    return [
        'diagnosis' => 'No diagnosis recorded',
        'duration' => 'Not specified',
        'notes' => 'No treatment plan has been created yet.',
        'total_sessions' => 0,
        'completed_sessions' => 0,
        'next_session' => 'Not scheduled',
        'sessions' => []
    ];
}

// Get appointment details
$appointment = getAppointmentDetails($conn, $appointment_id);

if (!$appointment) {
    die('Appointment not found');
}

// Prepare patient data
$patient = [
    'id' => $appointment['patient_id'],
    'name' => $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name'],
    'age' => $appointment['age'],
    'gender' => ucfirst($appointment['gender']),
    'phone' => $appointment['phone'],
    'email' => $appointment['email']
];

// Prepare appointment data
$appointment_data = [
    'id' => $appointment['id'],
    'date' => $appointment['day_of_week'] . ', ' . $appointment['appointment_time'],
    'time' => date('h:i A', strtotime($appointment['appointment_time'])),
    'doctor' => 'Dr. ' . $appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name'],
    'specialty' => $appointment['specialty'],
    'status' => $appointment['status'],
    'visit_type' => $appointment['visit_type'],
    'location' => $appointment['location']
];

// Get treatment plan
$treatment_plan = getTreatmentPlan($conn, $appointment['patient_id'], $appointment_id);

// Set default values for treatment plan display
$treatment_plan['total_sessions'] = $treatment_plan['total_sessions'] ?? 0;
$treatment_plan['completed_sessions'] = $treatment_plan['completed_sessions'] ?? 0;
$treatment_plan['next_session'] = $treatment_plan['next_session'] ?? 'Not scheduled';

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appointment_data['specialty']); ?> Appointment - <?php echo htmlspecialchars($patient['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/single_appoinment.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Advanced Chat Panel Styles */
        .chat-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 380px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .chat-panel.chat-open {
            display: flex;
            transform: translateY(0);
            opacity: 1;
        }
        
        .chat-panel.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #4a6cf7 0%, #2541b2 100%);
            color: white;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
            user-select: none;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .chat-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chat-header .status-indicator {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        
        .chat-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chat-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f5f7fb;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .message {
            max-width: 80%;
            position: relative;
            animation: messageAppear 0.3s ease-out;
        }
        
        @keyframes messageAppear {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.received {
            align-self: flex-start;
            background: white;
            padding: 12px 16px;
            border-radius: 0 12px 12px 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .message.sent {
            align-self: flex-end;
            background: #4a6cf7;
            color: white;
            padding: 12px 16px;
            border-radius: 12px 0 12px 12px;
            box-shadow: 0 2px 4px rgba(74, 108, 247, 0.3);
        }
        
        .message .sender {
            font-weight: 600;
            font-size: 12px;
            margin-bottom: 4px;
            opacity: 0.9;
        }
        
        .message .time {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }
        
        .message.sent .time {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .message-status {
            display: inline-flex;
            margin-left: 6px;
            font-size: 12px;
        }
        
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 8px 12px;
            background: white;
            border-radius: 12px;
            width: fit-content;
            margin: 8px 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            align-self: flex-start;
            display: none;
        }
        
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #4a6cf7;
            border-radius: 50%;
            display: inline-block;
            opacity: 0.4;
        }
        
        .typing-indicator span:nth-child(1) { animation: typing 1s infinite; }
        .typing-indicator span:nth-child(2) { animation: typing 1s 0.2s infinite; }
        .typing-indicator span:nth-child(3) { animation: typing 1s 0.4s infinite; }
        
        @keyframes typing {
            0%, 100% { opacity: 0.4; transform: translateY(0); }
            50% { opacity: 1; transform: translateY(-3px); }
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .typing-dots {
            display: flex;
            margin-right: 8px;
        }
        
        .typing-dots span {
            width: 6px;
            height: 6px;
            background-color: #6c757d;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: typing 1.4s infinite ease-in-out both;
        }
        
        .typing-dots span:nth-child(1) {
            animation-delay: 0s;
        }
        
        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 80%, 100% { 
                transform: scale(0);
            } 40% { 
                transform: scale(1);
            }
        }
        
        .chat-input-container {
            padding: 16px;
            background: white;
            border-top: 1px solid #e9ecef;
            position: relative;
        }
        
        .chat-input-wrapper {
            display: flex;
            background: #f5f7fb;
            border-radius: 24px;
            padding: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .chat-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
            resize: none;
            max-height: 120px;
            line-height: 1.5;
            font-family: inherit;
        }
        
        .chat-send-btn {
            background: #4a6cf7;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .chat-send-btn:hover {
            background: #3a5ce4;
            transform: scale(1.05);
        }
        
        .chat-send-btn:active {
            transform: scale(0.95);
        }
        
        .chat-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            padding: 0 8px;
        }
        
        .chat-action-btn {
            background: none;
            border: none;
            color: #6c757d;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chat-action-btn:hover {
            background: #f1f3f5;
            color: #4a6cf7;
        }
        
        .chat-date-divider {
            text-align: center;
            margin: 16px 0;
            position: relative;
            z-index: 1;
        }
        
        .chat-date-divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
            z-index: -1;
        }
        
        .chat-date-divider span {
            background: #f5f7fb;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .chat-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 20px;
            transition: all 0.2s ease-in-out;
            background: #4a6cf7;
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(74, 108, 247, 0.3);
        }
        
        .chat-trigger:hover {
            background: #3a5ce4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.4);
        }
        
        .chat-trigger:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="appointment-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <h1><i class="fas fa-calendar-check me-3"></i><?php echo htmlspecialchars($appointment_data['specialty']); ?> Appointment</h1>
                    <p class="mb-0">Patient: <?php echo htmlspecialchars($patient['name']); ?> | ID: <?php echo 'PAT-' . str_pad($patient['id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-light me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-primary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Appointment Overview -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card appointment-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Appointment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Appointment ID:</span>
                                    <span class="info-value">APT-<?php echo str_pad($appointment_data['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date & Time:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($appointment_data['date']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Visit Type:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($appointment_data['visit_type']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item d-flex align-items-center">
                                    <span class="info-label">Doctor:</span>
                                    <span class="info-value me-2"><?php echo htmlspecialchars($appointment_data['doctor']); ?></span>
                                    <button class="btn btn-sm btn-outline-primary chat-trigger" id="doctorChatTrigger" title="Chat with doctor">
                                        <i class="fas fa-comment-dots"></i> Chat
                                    </button>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Specialty:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($appointment_data['specialty']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status:</span>
                                    <?php 
                                    $status_class = [
                                        'Scheduled' => 'bg-primary',
                                        'Completed' => 'bg-success',
                                        'Cancelled' => 'bg-danger'
                                    ][$appointment_data['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($appointment_data['status']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card progress-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Treatment Progress</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="progress-circle">
                            <div class="progress-number">
                                <?php 
                                $total = max(1, $treatment_plan['total_sessions']); // Avoid division by zero
                                $progress_percentage = min(100, round(($treatment_plan['completed_sessions'] / $total) * 100));
                                echo $progress_percentage;
                                ?>%
                            </div>
                            <div class="progress-text">Complete</div>
                        </div>
                        <div class="progress-stats mt-3">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $treatment_plan['completed_sessions']; ?></span>
                                <span class="stat-label">Completed</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $treatment_plan['total_sessions'] - $treatment_plan['completed_sessions']; ?></span>
                                <span class="stat-label">Remaining</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Plan -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card treatment-plan-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Treatment Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (!empty($treatment_plan['diagnosis'])): ?>
                                <div class="plan-item">
                                    <span class="plan-label">Diagnosis:</span>
                                    <span class="plan-value"><?php echo htmlspecialchars($treatment_plan['diagnosis']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($treatment_plan['duration'])): ?>
                                <div class="plan-item">
                                    <span class="plan-label">Duration:</span>
                                    <span class="plan-value"><?php echo htmlspecialchars($treatment_plan['duration']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($treatment_plan['notes'])): ?>
                                <div class="plan-item">
                                    <span class="plan-label">Notes:</span>
                                    <p class="plan-value"><?php echo nl2br(htmlspecialchars($treatment_plan['notes'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <!-- Sessions count will be shown in the treatment plan section -->
                                <div class="plan-item">
                                    <span class="plan-label">Completed Sessions:</span>
                                    <span class="plan-value"><?php echo htmlspecialchars($treatment_plan['completed_sessions']); ?></span>
                                </div>
                                <div class="plan-item">
                                    <span class="plan-label">Next Session:</span>
                                    <span class="plan-value"><?php echo htmlspecialchars($treatment_plan['next_session']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Sessions Section -->
        <?php if (!empty($treatment_plan['sessions'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card sessions-section">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; border-bottom: 1px solid #0a58ca;">
                        <h5 class="mb-0 text-white"><i class="fas fa-calendar-check me-2"></i>Treatment Sessions</h5>
                        <span class="badge bg-white text-primary"><?php echo count($treatment_plan['sessions']); ?> Sessions</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline">
                            <?php 
                            $completed_count = 0;
                            $scheduled_count = 0;
                            $total_sessions = count($treatment_plan['sessions']);
                            $session_index = 0;
                            
                            foreach ($treatment_plan['sessions'] as $session): 
                                $session_index++;
                                $status_class = [
                                    'completed' => 'completed',
                                    'scheduled' => 'scheduled',
                                    'cancelled' => 'cancelled',
                                    'no_show' => 'no-show'
                                ][$session['status']] ?? 'scheduled';
                                
                                if ($session['status'] === 'completed') $completed_count++;
                                if ($session['status'] === 'scheduled') $scheduled_count++;
                                
                                $status_text = ucfirst($session['status']);
                                $session_date = date('M j, Y', strtotime($session['session_date']));
                                $session_day = date('D', strtotime($session['session_date']));
                                $session_time = date('h:i A', strtotime($session['session_date']));
                                $is_completed = $session['status'] === 'completed';
                                $is_cancelled = $session['status'] === 'cancelled';
                                $is_no_show = $session['status'] === 'no_show';
                                $is_upcoming = $session['status'] === 'scheduled';
                                $is_last = $session_index === $total_sessions;
                            ?>
                                <div class="timeline-item <?php echo $status_class; ?>">
                                    <div class="timeline-marker">
                                        <div class="timeline-dot">
                                            <?php if ($is_completed): ?>
                                                <i class="fas fa-check"></i>
                                            <?php elseif ($is_cancelled): ?>
                                                <i class="fas fa-times"></i>
                                            <?php elseif ($is_no_show): ?>
                                                <i class="fas fa-user-times"></i>
                                            <?php else: ?>
                                                <i class="far fa-calendar"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!$is_last): ?>
                                            <div class="timeline-line"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-card">
                                            <div class="timeline-card-header">
                                                <div class="timeline-card-title">
                                                    <h6 class="mb-0">Session <?php echo $session['session_number']; ?></h6>
                                                    <span class="timeline-date"><?php echo $session_day; ?>, <?php echo $session_date; ?></span>
                                                </div>
                                                <div class="timeline-actions">
                                                    <?php if ($is_completed): ?>
                                                        <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                            <i class="far fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Edit Session">
                                                        <i class="far fa-edit"></i>
                                                    </button>
                                                    <?php if ($is_upcoming): ?>
                                                        <button class="btn btn-sm btn-success" title="Mark as Completed">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" title="Mark as No Show">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" title="Cancel Session">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="timeline-card-body">
                                                <div class="timeline-meta">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="far fa-clock me-1"></i> <?php echo $session_time; ?>
                                                    </span>
                                                    <span class="status-badge"><?php echo $status_text; ?></span>
                                                </div>
                                                <?php if (!empty($session['notes'])): ?>
                                                    <div class="timeline-notes">
                                                        <i class="far fa-clipboard"></i>
                                                        <div class="notes-text"><?php echo nl2br(htmlspecialchars($session['notes'])); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Session Summary -->
                        <div class="session-summary px-4 py-3 border-top">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="summary-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>Completed: <strong><?php echo $completed_count; ?></strong></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-item">
                                        <i class="far fa-clock text-primary"></i>
                                        <span>Upcoming: <strong><?php echo $scheduled_count; ?></strong></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-item">
                                        <i class="fas fa-chart-line text-info"></i>
                                        <span>Progress: <strong><?php echo $treatment_plan['total_sessions'] > 0 ? round(($completed_count / $treatment_plan['total_sessions']) * 100) : 0; ?>%</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-primary" onclick="updateAppointment()">
                                <i class="fas fa-edit me-2"></i>Edit Appointment
                            </button>
                            <button class="btn btn-outline-danger" onclick="if(confirm('Are you sure you want to delete this appointment?')) { /* Add delete functionality */ }">
                                <i class="fas fa-trash-alt me-2"></i>Delete Appointment
                            </button>
                            <button class="btn btn-warning text-white" id="doctorChatTrigger">
                                <i class="fas fa-comment-medical me-2"></i>Chat with Doctor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Advanced Chat Panel -->
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header" id="chatHeader">
            <h5>
                <span class="status-indicator"></span>
                <?php echo htmlspecialchars($appointment_data['doctor']); ?>
                <small class="text-white-50 ms-2">Online</small>
            </h5>
            <button class="chat-close" id="closeChat">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="chat-date-divider">
                <span>Today</span>
            </div>
            
            <!-- Sample received message -->
            <div class="message received">
                <div class="sender">Dr. <?php echo htmlspecialchars(explode(' ', $appointment_data['doctor'])[0]); ?></div>
                <div class="content">Hello! How can I help you today?</div>
                <div class="time">10:30 AM <i class="fas fa-check-double ms-1"></i></div>
            </div>
            
            <!-- Sample sent message -->
            <div class="message sent">
                <div class="content">Hi Doctor, I have a question about my treatment plan.</div>
                <div class="time">10:32 AM <i class="fas fa-check-double ms-1"></i></div>
            </div>
            
            <!-- Typing indicator (hidden by default) -->
            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span>Doctor is typing...</span>
            </div>
        </div>
        
        <div class="chat-input-container">
            <div class="chat-actions">
                <button class="chat-action-btn" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                </button>
                <button class="chat-action-btn" title="Send image">
                    <i class="fas fa-image"></i>
                </button>
            </div>
            
            <div class="chat-input-wrapper">
                <textarea 
                    id="messageInput" 
                    class="chat-input" 
                    placeholder="Type a message..."
                    rows="1"
                ></textarea>
                <button class="chat-send-btn" id="sendMessage">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Chat initialization script -->
    <script>
        // Store the appointment ID in a global variable
        window.appointmentId = '<?php echo $appointment_id; ?>';
        
        // Function to initialize the chat
        function initializeChat() {
            const chatPanel = document.getElementById('chatPanel');
            const chatTrigger = document.getElementById('doctorChatTrigger');
            
            // Make sure elements exist
            if (chatPanel && chatTrigger) {
                // Show the chat panel when clicking the trigger
                chatTrigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    chatPanel.style.display = 'flex';
                    // Trigger a reflow
                    void chatPanel.offsetWidth;
                    chatPanel.classList.add('chat-open');
                });
                
                // Close button
                const closeChat = document.getElementById('closeChat');
                if (closeChat) {
                    closeChat.addEventListener('click', () => {
                        chatPanel.classList.remove('chat-open');
                    });
                }
            }
        }
        
        // Initialize chat when the page loads
        document.addEventListener('DOMContentLoaded', initializeChat);
    </script>
    
    <!-- Include the chat script -->
    <script src="js/chat.js" data-appointment-id="<?php echo $appointment_id; ?>"></script>

    <script>
        function updateAppointment() {
            alert('Opening appointment editor...');
            // In real app, this would open an edit modal or page
        }

        function addPrescription() {
            alert('Opening prescription form...');
            // In real app, this would open a prescription form
        }

        function viewMedicalHistory() {
            alert('Opening medical history...');
            // In real app, this would show patient's medical history
        }

        function sendReminder() {
            alert('Sending appointment reminder...');
            // In real app, this would send an email/SMS reminder
        }

        function startChat() {
            alert('Initiating chat with doctor...');
            // In real app, this would open a chat interface with the doctor
            // Example: window.location.href = 'chat.php?doctor_id=<?php echo $appointment['doctor_id']; ?>&appointment_id=<?php echo $appointment_id; ?>';
        }
    </script>
</body>
</html>
