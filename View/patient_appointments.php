<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['patient_id'])) {
    header('Location: /MVC/View/login.php');
    exit();
}

// Include necessary models
require_once("../Model/Patient.php");

// Initialize patient model
$patientModel = new Patient();

// Get patient data
$patient = [
    'id' => $_SESSION['patient_id'],
    'name' => $_SESSION['patient_name'] ?? 'Patient',
    'email' => $_SESSION['patient_email'] ?? '',
    'profile_image' => 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['patient_name'] ?? 'Patient') . '&background=2563eb&color=fff',
    'notifications' => []
];


// Get all appointments for the patient
$appointments = $patientModel->getAppointments($patient['id']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Andalusia Hospital</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/MVC/public/images/favicon.png">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/MVC/View/css/patient_dashboard.css">
    
    <style>
        /* Reset body padding for the new header */
        body {
            padding-top: 150px;
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Custom styles for the dashboard */
        .welcome-section {
            margin-bottom: 30px;
        }
        
        .welcome-section h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        /* Chatbot styles */
        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 360px;
            height: 0;
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            transform-origin: bottom right;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1001;
            border: none;
        }
        
        .chat-window.active {
            height: 460px;
            max-height: 70vh;
            opacity: 1;
            transform: translateY(0) scale(1);
            animation: chatAppear 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes chatAppear {
            0% {
                transform: scale(0.3) translateY(40px);
                opacity: 0;
                border-radius: 50%;
            }
            70% {
                transform: scale(1.02) translateY(0);
                border-radius: 24px;
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
                border-radius: 18px;
            }
        }
        
        .chat-header {
            padding: 16px 24px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }
        
        .chat-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.3px;
        }
        
        .chat-title i {
            font-size: 18px;
        }
        
        .chat-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .chat-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .chat-messages {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            animation: messageAppear 0.2s ease-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .bot-message {
            background: white;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        
        .user-message {
            background: #3b82f6;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        
        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 6px;
            text-align: right;
            font-weight: 500;
        }
        
        .user-message .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .chat-input {
            padding: 16px;
            background: white;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 12px;
            align-items: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.03);
        }
        
        /* Status Badges */
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }
        
        .status-scheduled {
            background-color: #fff3cd;  /* Yellow */
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .status-completed {
            background-color: #d4edda;  /* Green */
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-upcoming {
            background-color: #cce5ff;  /* Light blue */
            color: #004085;
            border: 1px solid #b8daff;
        }
        
        .status-cancelled {
            background-color: #f8d7da;  /* Red */
            color: #721c24;
            border: 1px solid #f5c6cb;
            text-decoration: line-through;
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            outline: none;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .chat-input input:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .chat-input button {
            width: 48px;
            height: 48px;
            border: none;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
        }
        
        .chat-input button:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }
        
        .chat-input button:active {
            transform: translateY(0);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatIcon = document.getElementById('openChatbot');
            const chatWindow = document.getElementById('chatWindow');
            const closeChat = document.getElementById('closeChat');
            const userInput = document.getElementById('userInput');
            const sendButton = document.getElementById('sendMessage');
            const chatMessages = document.getElementById('chatMessages');
            
            // Toggle chat window
            chatIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                chatWindow.classList.toggle('active');
                if (chatWindow.classList.contains('active')) {
                    userInput.focus();
                }
            });
            
            // Close chat window when clicking outside
            document.addEventListener('click', function(e) {
                if (!chatWindow.contains(e.target) && e.target !== chatIcon) {
                    chatWindow.classList.remove('active');
                }
            });
            
            // Close chat window
            closeChat.addEventListener('click', function(e) {
                e.stopPropagation();
                chatWindow.classList.remove('active');
            });
            
            // Send message function
            function sendMessage() {
                const message = userInput.value.trim();
                if (message === '') return;
                
                // Add user message
                addMessage(message, 'user');
                userInput.value = '';
                
                // Simulate bot response
                setTimeout(() => {
                    addMessage('I am an AI assistant. How can I help you today?', 'bot');
                }, 1000);
            }
            
            // Add message to chat
            function addMessage(text, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}-message`;
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                contentDiv.textContent = text;
                
                const timeDiv = document.createElement('div');
                timeDiv.className = 'message-time';
                timeDiv.textContent = getCurrentTime();
                
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(timeDiv);
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Get current time in HH:MM format
            function getCurrentTime() {
                const now = new Date();
                return now.getHours().toString().padStart(2, '0') + ':' + 
                       now.getMinutes().toString().padStart(2, '0');
            }
            
            // Send message on button click or Enter key
            sendButton.addEventListener('click', sendMessage);
            userInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="contact-info">
                <i class="fas fa-phone"></i> 16781
            </div>
            <div class="header-social">
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
        <div class="header-bottom">
            <img src="/MVC/View/images/logo.png" alt="Logo" class="header-logo">
            <nav class="header-nav">
                <a href="index.php">Home</a>
                <a href="patient_dashboard.php" class="active">Dashboard</a>
                <a href="#services" class="nav-link">Services</a>
                <a href="contact.html">Contact Us</a>
            </nav>
            <?php
            require_once("../Controller/BookingController.php");
            require_once("../Model/Doctor.php");

            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                header('Location: login.php');
                exit();
            }
            ?>
            <div class="header-actions">
                <!-- Notification Icon -->
                <div class="notification-dropdown">
                    <div class="notification-icon">
                        <a href="#" class="notification-link">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge"><?php echo isset($unreadCount) ? $unreadCount : '0'; ?></span>
                        </a>
                    </div>
                    <div class="notification-panel">
                        <div class="notification-header">
                            <h4>Notifications (<?php echo $unreadCount; ?>)</h4>
                            <a href="#" class="mark-all-read">Mark all as read</a>
                        </div>
                        <div class="notification-list">
                            <!-- Sample notifications - replace with dynamic content -->
                            <div class="notification-item unread">
                                <div class="notification-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="notification-content">
                                    <p>Your appointment with Dr. Smith has been confirmed for tomorrow at 2:00 PM</p>
                                    <span class="notification-time">10 min ago</span>
                                </div>
                            </div>
                            <div class="notification-item unread">
                                <div class="notification-icon"><i class="fas fa-bell"></i></div>
                                <div class="notification-content">
                                    <p>Reminder: Annual check-up due next week</p>
                                    <span class="notification-time">1 hour ago</span>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-icon"><i class="fas fa-file-medical"></i></div>
                                <div class="notification-content">
                                    <p>Your lab results are now available</p>
                                    <span class="notification-time">Yesterday</span>
                                </div>
                            </div>
                        </div>
                        <div class="notification-footer">
                            <a href="#" class="view-all">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <div class="user-profile dropdown">
                    <a href="#" class="user-info dropdown-toggle">
                        <?php
                        // Default avatar path - adjust this to your default avatar image
                        $defaultAvatar = '/MVC/View/images/default-avatar.png';
                        $avatarPath = !empty($patient['profile_image']) ? $patient['profile_image'] : $defaultAvatar;
                        ?>
                        <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar" class="user-avatar">
                        <span class="user-name"><?php 
                            $firstName = isset($_SESSION['FN']) ? $_SESSION['FN'] : '';
                            $lastName = isset($_SESSION['LN']) ? $_SESSION['LN'] : '';
                            echo htmlspecialchars(trim($firstName . ' ' . $lastName)); 
                        ?></span>
                        <span class="user-id"><?php 
                            if (isset($_SESSION['login_method']) && isset($_SESSION['login_number'])) {
                                if ($_SESSION['login_method'] === 'ID') {
                                    echo '<i class="fas fa-id-card"></i>NN: ' . htmlspecialchars($_SESSION['login_number']);
                                } else {
                                    echo '<i class="fas fa-id-card"></i>ID: ' . htmlspecialchars($_SESSION['login_number']);
                                }
                            }
                        ?></span>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="process-logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-user">
                <?php 
                $profileImage = !empty($patient['profile_image']) ? $patient['profile_image'] : '/MVC/View/images/default-avatar.png';
                $fullName = (isset($_SESSION['FN']) ? $_SESSION['FN'] : '') . ' ' . (isset($_SESSION['LN']) ? $_SESSION['LN'] : '');
                ?>
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="<?php echo htmlspecialchars(trim($fullName)); ?>" class="sidebar-avatar">
                <h4 class="sidebar-username"><?php echo htmlspecialchars(trim($fullName)); ?></h4>
                <?php if (isset($_SESSION['login_number'])): ?>
                <div class="sidebar-userid">
                    <i class="fas fa-id-card"></i>
                    <span><?php echo htmlspecialchars($_SESSION['login_number']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <ul class="sidebar-menu">
                <li><a href="patient_dashboard.php" ><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="/MVC/View/patient_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/MVC/View/patient_appointments.php" class="active"A><i class="fas fa-calendar-check"></i> Appointments</a></li>
                <li><a href="/MVC/View/book_appointment.php"><i class="fas fa-plus-circle"></i> Book Appointment</a></li>
                <li><a href="/MVC/View/patient_bills.php"><i class="fas fa-credit-card"></i> Billing</a></li>
                <li><a href="/MVC/View/process-logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h1>Welcome to your appointments, <?php echo htmlspecialchars($patient['name']); ?>! 👋</h1>
            </section>

            <div class="row">
            <div class="col-12">
                <div class="appointment-card">
                    <?php if (empty($appointments)): ?>
                        <div class="alert alert-info">
                            You don't have any appointments yet. <a href="book_appointment.php">Book your first appointment</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Specialty</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['doctor_specialty'] ?? 'General Medicine'); ?></td>
                                            <td><?php echo date('M d, Y - h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                                        View
                                                    </button>
                                                    <?php if (in_array(strtolower($appointment['status']), ['upcoming', 'scheduled'])): ?>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm me-1" onclick="rescheduleAppointment(<?php echo $appointment['id']; ?>)">
                                                            Reschedule
                                                        </button>
                                                        <form method="POST" action="/MVC/View/cancel_appointment.php" style="display: inline;">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm me-1" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    <?php elseif (strtolower($appointment['status']) === 'completed'): ?>
                                                        <form method="POST" action="/MVC/View/delete_appointment.php" style="display: inline;">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm me-1" onclick="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?> 
                                                </div>
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
    </div>

    


    
    <script>
    // View appointment details
    function viewAppointment(id) {
        window.location.href = 'single_appoinment.php?id=' + id;
    }
    
    // Reschedule appointment
    function rescheduleAppointment(id) {
        if (confirm('Are you sure you want to reschedule this appointment?')) {
            // You can implement a modal or redirect to reschedule page
            window.location.href = 'reschedule_appointment.php?id=' + id;
        }
    }
    
    // Cancel appointment
    function cancelAppointment(id) {
        if (confirm('Are you sure you want to cancel this appointment?')) {
            fetch('/MVC/Controller/cancel_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ appointment_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment cancelled successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to cancel appointment'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the appointment');
            });
        }
    }
    
    // Delete appointment (for completed appointments)
    function deleteAppointment(id) {
        if (confirm('Are you sure you want to delete this appointment record? This action cannot be undone.')) {
            fetch('/MVC/Controller/delete_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ appointment_id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment deleted successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete appointment'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the appointment');
            });
        }
    }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts if needed
            const charts = document.querySelectorAll('[data-chart]');
            if (charts.length > 0) {
                initializeCharts();
            }
        });


</body>
</html>
