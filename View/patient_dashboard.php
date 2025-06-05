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

// Sample patient data - In a real app, this would come from your database
$patient = [
    'id' => $_SESSION['patient_id'] ?? '2222',
    'name' => $_SESSION['patient_name'] ?? 'Youssif Mohamed',
    'email' => $_SESSION['patient_email'] ?? 'youssif@example.com',
    'profile_image' => 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['patient_name'] ?? 'Youssif+Mohamed') . '&background=2563eb&color=fff',
    'notifications' => [
        
    ]
];

// Get unread notifications count
$unreadCount = count(array_filter($patient['notifications'], fn($n) => !$n['read']));
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
    </style>
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
                <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="/MVC/View/patient_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/MVC/View/patient_appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
                <li><a href="/MVC/View/book_appointment.php"><i class="fas fa-plus-circle"></i> Book Appointment</a></li>
                <li><a href="/MVC/View/patient_bills.php"><i class="fas fa-credit-card"></i> Billing</a></li>
                <li><a href="/MVC/View/process-logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($patient['name']); ?>! 👋</h1>
                <p>Here's what's happening with your health today</p>
            </section>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>2</h3>
                        <p>Upcoming Appointments</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(45deg, #f6c23e, #dda20a);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1</h3>
                        <p>Pending Bills</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(45deg, #e74a3b, #be2617);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-info">
                        <h3>3</h3>
                        <p>Notifications</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="appointment-card">
                <div class="appointment-header">
                    <h4>Upcoming Appointments</h4>
                    <a href="/MVC/View/patient_appointments.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="appointment-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Specialty</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Dr. Ahmed Mohamed</td>
                                <td>Cardiology</td>
                                <td>May 28, 2023 - 10:00 AM</td>
                                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                    <button class="btn btn-sm btn-outline-secondary">Reschedule</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Dr. Sara Ahmed</td>
                                <td>Dermatology</td>
                                <td>May 30, 2023 - 02:30 PM</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/MVC/View/js/notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts if needed
            const charts = document.querySelectorAll('[data-chart]');
            if (charts.length > 0) {
                initializeCharts();
            }
        });

        function initializeCharts() {
            // Your chart initialization code here
            // Example:
            // const ctx = document.getElementById('myChart');
            // if (ctx) {
            //     new Chart(ctx, { ... });
            // }
        }
    </script>
</body>
</html>
