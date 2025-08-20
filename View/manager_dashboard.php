<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../Model/Manager.php';
require_once __DIR__ . '/../Controller/ManagerController.php';

// Check if user is logged in as manager
if (!Manager::isManagerLoggedIn()) {
    header('Location: /MVC/View/manager_login.php');
    exit();
}

// Include required files
require_once __DIR__ . '/../Controller/ManagerController.php';

// Get dashboard statistics
$stats = ManagerController::getDashboardStatistics();

// Extract stats
$total_patients = $stats['stats']['total_patients'];
$total_staff = $stats['stats']['total_staff'];
$total_appointments = $stats['stats']['total_appointments'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Healthcare Management System</title>
    <link rel="stylesheet" href="css/manager_home.css">
    <style>
        /* Action Grid Styles */
        .action-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }
        
        .action-row {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }
        
        .action-tab {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 12px;
            padding: 25px 15px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .action-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 0;
            background: linear-gradient(135deg, #3498db, #2980b9);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 0;
        }
        
        .action-tab:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .action-tab:hover::before {
            height: 100%;
        }
        
        .action-tab i, 
        .action-tab span {
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .action-tab i {
            font-size: 28px;
            margin-bottom: 12px;
            color: #3498db;
            transition: all 0.4s ease;
        }
        
        .action-tab:hover i {
            color: white;
            transform: scale(1.1);
        }
        
        .action-tab span {
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            letter-spacing: 0.3px;
        }
        
        /* Active tab style */
        .action-tab.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(52, 152, 219, 0.25);
        }
        
        .action-tab.active i {
            color: white;
            transform: scale(1.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .action-row {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .action-tab {
                flex: 1 1 calc(50% - 10px);
                min-width: 120px;
            }
        }
        
        @media (max-width: 576px) {
            .action-tab {
                flex: 1 1 100%;
            }
            
            .action-tab i {
                font-size: 20px;
            }
            
            .action-tab span {
                font-size: 13px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f6fa;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
        }

        .logo h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #3498db;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-links a i {
            margin-right: 10px;
            width: 20px;
        }

        .nav-links li.active a,
        .nav-links a:hover {
            background-color: #3498db;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            color: #2c3e50;
        }

        .user-info {
            color: #7f8c8d;
        }

        /* Dashboard Stats */
        .dashboard-stats {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 20px 0;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(52, 152, 219, 0.2);
        }
        
        .stat-card i {
            font-size: 24px;
            color: #3498db;
            background: #e8f4fc;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover i {
            background: #3498db;
            color: white;
        }
        
        .stat-info h3 {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #2c3e50;
            font-size: 1.8em;
            font-weight: 600;
        }

        /* Quick Actions */
        .quick-actions {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .action-btn i {
            margin-right: 10px;
        }

        .action-btn:hover {
            background-color: #2980b9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar .logo h2,
            .nav-links a span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .nav-links a {
                justify-content: center;
            }

            .nav-links a i {
                margin-right: 0;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">
                <h2>Physical Therapy</h2>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <a href="manager_dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="Mangment_panel_staff.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Staff</span>
                    </a>
                </li>
                <li>
                    <a href="inventory_receipts.php">
                        <i class="fas fa-boxes"></i>
                        <span>Manage Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="search_patient.php">
                        <i class="fas fa-search"></i>
                        <span>Search Patients</span>
                    </a>
                </li>
                <li>
                    <a href="update_patient.php">
                        <i class="fas fa-user-edit"></i>
                        <span>Update Patients</span>
                    </a>
                </li>
                <li>
                    <a href="manager_reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="?action=logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['first_name'] ?? 'Manager'); ?></h1>
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Manager'); ?></span>
                </div>
            </header>

            <div class="dashboard-stats">
                <!-- Row 1 -->
                <div class="stats-row">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div class="stat-info">
                            <h3>Total Patients</h3>
                            <p><?php echo $total_patients; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-nurse"></i>
                        <div class="stat-info">
                            <h3>Staff Members</h3>
                            <p><?php echo $total_staff; ?></p>
                        </div>
                    </div>
                </div>
                <!-- Row 2 -->
                <div class="stats-row">
                    <div class="stat-card">
                        <i class="fas fa-user-md"></i>
                        <div class="stat-info">
                            <h3>Total Doctors</h3>
                            <p><?php echo $stats['stats']['total_doctors'] ?? 0; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar-check"></i>
                        <div class="stat-info">
                            <h3>Total Appointments</h3>
                            <p><?php echo $total_appointments; ?></p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <!-- Row 1 -->
                    <div class="action-row">
                        <a href="Mangment_panel_staff.php" class="action-tab">
                            <i class="fas fa-users"></i>
                            <span>Manage Staff</span>
                        </a>
                        <a href="manger_search_patient.php" class="action-tab">
                            <i class="fas fa-search"></i>
                            <span>Search Patient</span>
                        </a>
                        <a href="manager_update_patient.php" class="action-tab">
                            <i class="fas fa-edit"></i>
                            <span>Update Patient</span>
                        </a>
                    </div>
                    
                    <!-- Row 2 -->
                    <div class="action-row">
                        <a href="inventory_receipts.php" class="action-tab">
                            <i class="fas fa-boxes"></i>
                            <span>Inventory</span>
                        </a>
                        <a href="manager_reports.php" class="action-tab">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports & Analytics</span>
                        </a>
                        <a href="manger_patient_management.php" class="action-tab">
                            <i class="fas fa-user-injured"></i>
                            <span>Patients</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Add active class to current nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'manager_dashboard.php';
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage || 
                   (currentPage === '' && href === 'manager_dashboard.php')) {
                    link.parentElement.classList.add('active');
                } else {
                    link.parentElement.classList.remove('active');
                }
            });
            
            // Other actions dropdown toggle
            const otherActionsBtn = document.getElementById('other-actions-btn');
            const otherActionsDropdown = document.getElementById('other-actions-dropdown');
            
            if (otherActionsBtn && otherActionsDropdown) {
                otherActionsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    otherActionsDropdown.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!otherActionsBtn.contains(e.target) && !otherActionsDropdown.contains(e.target)) {
                        otherActionsDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>