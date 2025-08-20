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

// Set default date range (last 30 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

// Handle date range from request
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
}

// Get report type
$reportType = $_GET['type'] ?? 'overview';

// Get report data
try {
    $reportData = ManagerController::getReportData($reportType, $startDate, $endDate);
    
    // Handle PDF export
    if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L',
            'default_font' => 'dejavusans',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);
        
        ob_start();
        include 'report_pdf_template.php';
        $html = ob_get_clean();
        
        $mpdf->SetTitle('Report - ' . date('Y-m-d'));
        $mpdf->SetAuthor('Healthcare Management System');
        $mpdf->SetCreator('Healthcare Management System');
        
        $mpdf->WriteHTML($html);
        $mpdf->Output('report_' . date('Y-m-d') . '.pdf', 'D');
        exit();
    }
} catch (Exception $e) {
    $error = 'Error generating report: ' . $e->getMessage();
    error_log($error);
    $reportData = ['error' => $error];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Healthcare Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/MVC/Public/images/favicon.ico">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/MVC/Public/css/manager.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4bb543;
            --danger: #f44336;
            --warning: #ff9800;
            --info: #2196f3;
            --light: #f8f9fa;
            --dark: #212529;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --font-sans: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
            --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
            --border-radius: 0.5rem;
            --border-radius-lg: 0.75rem;
        }

        /* Base Styles */
        body {
            font-family: var(--font-sans);
            background-color: #f5f7fb;
            color: var(--gray-800);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Container */
        .container {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
            max-width: 100%;
            padding: 0;
            margin: 0;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #1e2b38 100%);
            color: white;
            padding: 1.5rem 1rem;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar .logo h2,
            .sidebar .nav-links li a span,
            .sidebar .nav-links li a .badge {
                display: none;
            }
            
            .sidebar .nav-links li a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .report-filters .row {
                flex-direction: column;
            }
            
            .report-filters .col-md-3,
            .report-filters .col-md-4 {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .report-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .report-actions .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }

        .logo {
            padding: 0 0.5rem 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            text-align: left;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            margin: 0.25rem 0 0;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-links li {
            margin-bottom: 0.25rem;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-links a i {
            margin-right: 0.75rem;
            width: 24px;
            text-align: center;
            font-size: 1.1em;
            transition: var(--transition);
        }

        .nav-links li.active a,
        .nav-links a:hover {
            background-color: var(--primary);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-links li.active a {
            box-shadow: var(--shadow-sm);
        }
        
        .nav-links .badge {
            margin-left: auto;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px 30px;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            width: calc(100% - 250px);
            max-width: 100%;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        /* Reports Container */
        .reports-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin: 20px 0;
            width: 100%;
            box-sizing: border-box;
        }

        .reports-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .report-header h1 {
            color: var(--dark);
            font-size: 1.75rem;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .report-header h1 i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .report-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .report-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            background: var(--gray-100);
            padding: 1rem;
            border-radius: var(--border-radius);
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
            flex: 1;
        }
        
        .filter-group label {
            font-size: 0.85rem;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: white;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }
        
        .btn-apply {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            height: 100%;
        }
        
        .btn-apply:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .report-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            background: var(--gray-100);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            align-items: flex-end;
            box-shadow: var(--shadow-sm);
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
            flex: 1;
        }
        
        .filter-group label {
            font-size: 0.85rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-weight: 500;
            display: block;
        }
        
        .filter-group select,
        .filter-group input,
        .filter-group .form-control {
            padding: 0.6rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: white;
            width: 100%;
        }
        
        .filter-group select:focus,
        .filter-group input:focus,
        .filter-group .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
            outline: none;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .report-card h3 {
            color: var(--secondary-color);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            margin-bottom: 20px;

/* Report Filters */
.report-filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    background: var(--gray-100);
    padding: 1rem;
    border-radius: var(--border-radius);
    align-items: center;
}

/* Filter Group */
.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    flex: 1;
}

.filter-group label {
    font-size: 0.85rem;
    color: var(--gray-600);
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.filter-group select,
.filter-group input {
    padding: 0.5rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.95rem;
    transition: var(--transition);
    background-color: white;
}

.filter-group select:focus,
.filter-group input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    outline: none;
}

/* Apply Button */
.btn-apply {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    height: 100%;
}

.btn-apply:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
}

/* Report Filters */
.report-filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    background: var(--gray-100);
    padding: 1.25rem;
    border-radius: var(--border-radius);
    align-items: flex-end;
    box-shadow: var(--shadow-sm);
}

/* Filter Group */
.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    flex: 1;
}

.filter-group label {
    font-size: 0.85rem;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
    font-weight: 500;
    display: block;
}

.filter-group select,
.filter-group input,
.filter-group .form-control {
    padding: 0.6rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.95rem;
    transition: var(--transition);
    background-color: white;
    width: 100%;
}

.filter-group select:focus,
.filter-group input:focus,
.filter-group .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
    outline: none;
}

/* Filter Actions */
.filter-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* Report Grid */
.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* Report Card */
.report-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: var(--shadow);
}

.report-card h3 {
    color: var(--secondary-color);
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

/* Chart Container */
.chart-container {
    position: relative;
    height: 250px;
    margin-bottom: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

/* Stat Card */
.stat-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    box-shadow: var(--shadow);
}

.stat-card h4 {
    margin: 0 0 10px 0;
    color: var(--dark-gray);
    font-size: 14px;
}

.stat-card p {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: var(--secondary-color);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.6rem 1.25rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    font-size: 0.9rem;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
}

.btn i {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.btn-sm {
    padding: 0.4rem 0.9rem;
    font-size: 0.85rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
}

/* Button Variations */
.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-outline-primary {
    background-color: transparent;
    color: var(--primary);
    border: 1px solid var(--primary);
}

.btn-outline-primary:hover {
    background-color: var(--primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-success {
    background-color: var(--success);
    color: white;
}

.btn-success:hover {
    background-color: #3da33a;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.btn-danger:hover {
    background-color: #e0352c;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-warning {
    background-color: var(--warning);
    color: white;
}

.btn-warning:hover {
    background-color: #e68a00;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-info {
    background-color: var(--info);
    color: white;
}

.btn-info:hover {
    background-color: #0d8aee;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-light {
    background-color: var(--light);
    color: var(--dark);
}

.btn-light:hover {
    background-color: #e2e6ea;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-dark {
    background-color: var(--dark);
    color: white;
}

.btn-dark:hover {
    background-color: #1d2124;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

/* Card Styles */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    border-left: 4px solid var(--primary);
    overflow: hidden;
    background: #fff;
    margin-bottom: 1.5rem;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Stats Card Variations */
.stat-card {
    border-left-width: 4px;
    border-left-style: solid;
}

.stat-card.primary {
    border-left-color: var(--primary);
}

.stat-card.success {
    border-left-color: var(--success);
}

.stat-card.info {
    border-left-color: var(--info);
}

.stat-card.warning {
    border-left-color: var(--warning);
}

.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    margin-bottom: 1rem;
}

.stat-card.primary .stat-icon {
    background-color: var(--primary);
}

.stat-card.success .stat-icon {
    background-color: var(--success);
}

.stat-card.info .stat-icon {
    background-color: var(--info);
}

.stat-card.warning .stat-icon {
    background-color: var(--warning);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0.5rem 0;
    color: var(--gray-900);
}

.stat-title {
    font-size: 0.875rem;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.stat-change {
    display: flex;
    align-items: center;
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.stat-change.increase {
    color: var(--success);
}

.stat-change.decrease {
    color: var(--danger);
}

/* Table Styles */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 1.5rem 0;
}

.data-table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse;
    font-size: 0.9rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.data-table th,
.data-table td {
    padding: 1rem 1.25rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-100);
    white-space: nowrap;
}

.data-table th {
    background-color: var(--primary);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    padding: 0.9rem 1.25rem;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tbody tr:hover {
    background-color: rgba(67, 97, 238, 0.05);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    width: fit-content;
}

.status-badge i {
    margin-right: 4px;
    font-size: 0.6rem;
}

.status-badge.completed {
    background-color: rgba(75, 181, 67, 0.1);
    color: #4bb543;
}

.status-badge.pending {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.status-badge.cancelled {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-badge.confirmed {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}
</style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <h2>HealthCare+</h2>
                <p>Management System</p>
            </div>
            <ul class="nav-links">
                <li>
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
                <li class="active">
                    <a href="manager_reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                        <span class="badge">New</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-user-md"></i>
                        <span>Doctors</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="mt-4">
                    <a href="logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <main class="main-content">
            <!-- Top Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <button class="btn btn-light" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="ms-3 text-muted">Reports & Analytics</span>
                </div>
            </div>

            <!-- Reports Container -->
            <div class="reports-container">
                <div class="report-header">
                    <h1><i class="fas fa-chart-line text-primary"></i> Reports & Analytics</h1>
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-light dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i>Email Report</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-save me-2"></i>Save Template</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Report Settings</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Report Filters -->
                <div class="report-filters card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="report-type" class="form-label fw-medium text-muted small mb-1">Report Type</label>
                                <select id="report-type" class="form-select">
                                    <option value="overview">Overview Dashboard</option>
                                    <option value="appointments">Appointments</option>
                                    <option value="revenue">Revenue & Payments</option>
                                    <option value="patients">Patient Statistics</option>
                                    <option value="inventory">Inventory</option>
                                    <option value="staff">Staff Performance</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="time-period" class="form-label fw-medium text-muted small mb-1">Time Period</label>
                                <select id="time-period" class="form-select" onchange="applyFilters()">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week" selected>This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_quarter">This Quarter</option>
                                    <option value="last_quarter">Last Quarter</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4" id="custom-date-range" style="display: none;">
                                <label for="date-range" class="form-label fw-medium text-muted small mb-1">Date Range</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    <input type="text" id="date-range" class="form-control" placeholder="Select date range">
                                </div>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="applyFilters()">
                                    <i class="fas fa-filter me-1"></i> Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Total Appointments</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0"><?php echo $reportData['total_appointments'] ?? '0'; ?></h3>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-arrow-up me-1"></i> 12%
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">vs. <?php echo $reportData['total_appointments_last_week'] ?? '0'; ?> last week</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Total Revenue</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0">$<?php echo number_format($reportData['total_revenue'] ?? '0', 2); ?></h3>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-arrow-up me-1"></i> 8%
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">vs. $<?php echo number_format($reportData['total_revenue_last_month'] ?? '0', 2); ?> last month</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-info h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">New Patients</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0"><?php echo $reportData['new_patients'] ?? '0'; ?></h3>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-arrow-up me-1"></i> 5%
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">vs. <?php echo $reportData['new_patients_last_month'] ?? '0'; ?> last month</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card stat-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Export Data</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Avg. Session Time</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="mb-0"><?php echo $reportData['avg_session_time'] ?? '0'; ?>m</h3>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        <i class="fas fa-arrow-down me-1"></i> 2%
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">vs. <?php echo $reportData['avg_session_time_last_week'] ?? '0'; ?>m last week</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue Chart -->
                    <div class="col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-pie text-success me-2"></i>
                                        Revenue by Service
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            This Month
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueDropdown">
                                            <li><a class="dropdown-item" href="#">This Week</a></li>
                                            <li><a class="dropdown-item active" href="#">This Month</a></li>
                                            <li><a class="dropdown-item" href="#">This Year</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 300px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-indicator" style="background-color: #4361ee"></span>
                                            <span class="ms-2 small">Physical Therapy</span>
                                        </div>
                                        <span class="fw-medium">$8,540</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-indicator" style="background-color: #3f37c9"></span>
                                            <span class="ms-2 small">Massage Therapy</span>
                                        </div>
                                        <span class="fw-medium">$4,230</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-indicator" style="background-color: #4895ef"></span>
                                            <span class="ms-2 small">Consultation</span>
                                        </div>
                                        <span class="fw-medium">$2,780</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="legend-indicator" style="background-color: #4cc9f0"></span>
                                            <span class="ms-2 small">Other Services</span>
                                        </div>
                                        <span class="fw-medium">$1,450</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="report-card">
                    <h3>Recent Appointments</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Therapist</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData['recent_appointments'])): ?>
                                    <?php foreach ($reportData['recent_appointments'] as $appointment): ?>
                                        <tr>
                                            <td>#<?php echo $appointment['id']; ?></td>
                                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['therapist_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                                    <?php echo $appointment['status']; ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($appointment['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No appointments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Print Styles -->
    <style media="print">
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        
        body * {
            visibility: hidden;
        }
        
        .reports-container, .reports-container * {
            visibility: visible;
        }
        
        .reports-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
        }
        
        .action-buttons, .sidebar {
            display: none !important;
        }
    </style>

    <script>
        // Export to PDF function
        function exportToPDF() {
            // Add loading state
            const btn = document.querySelector('.btn-pdf');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
            
            // Add timestamp to URL to prevent caching
            const timestamp = new Date().getTime();
            window.open('manager_reports.php?export=pdf&_=' + timestamp, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
        
        // Export to Excel function
        function exportToExcel() {
            // Add loading state
            const btn = document.querySelector('.btn-excel');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
            
            // In a real implementation, this would generate an Excel file
            // For now, we'll simulate a delay and show a message
            setTimeout(() => {
                alert('Excel export functionality will be implemented here');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1000);
        }
        
        // Initialize charts and other functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Appointments Chart
            const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
            const appointmentsChart = new Chart(appointmentsCtx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Appointments',
                        data: [12, 19, 15, 17, 10, 8, 5],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Therapy', 'Massage', 'Consultation', 'Rehabilitation'],
                    datasets: [{
                        data: [1200, 800, 500, 300],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // Show/hide custom date range
            document.getElementById('time-period').addEventListener('change', function() {
                const customRange = document.getElementById('custom-range');
                if (this.value === 'custom') {
                    customRange.style.display = 'block';
                } else {
                    customRange.style.display = 'none';
                }
            });
        });
        
        function applyFilters() {
            const reportType = document.getElementById('report-type').value;
            const timePeriod = document.getElementById('time-period').value;
            let startDate = '';
            let endDate = '';
            
            if (timePeriod === 'custom') {
                startDate = document.getElementById('start-date').value;
                endDate = document.getElementById('end-date').value;
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date cannot be after end date');
                    return;
                }
            }
            
            // Here you would typically make an AJAX call to update the report data
            console.log('Applying filters:', { reportType, timePeriod, startDate, endDate });
            // For now, we'll just show an alert
            alert('Filters applied! In a real implementation, this would refresh the report data.');
        }
        
        function exportToPDF() {
            // In a real implementation, this would generate a PDF of the report
            alert('Exporting to PDF...');
        }
        
        function exportToExcel() {
            // In a real implementation, this would generate an Excel file of the report
            alert('Exporting to Excel...');
        }
    </script>
</body>
</html>
