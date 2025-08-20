<?php
session_start();
require_once '../Controller/DoctorController.php';
$controller = new DoctorController();

$schedule = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $specialty = $_POST["specialty"];
    if (!empty($specialty)) {
        $schedule = $controller->getScheduleBySpecialty($specialty);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="css/book_appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: auto;
            font-family: 'Tajawal', sans-serif;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            width: 95%;
            max-width: 700px;
            display: flex;
            flex-direction: column;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            background: linear-gradient(135deg, #00b4db, #0083b0);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-logo {
            height: 30px;
            width: auto;
        }

        .modal-header span {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close:hover {
            color: #f1f1f1;
            transform: scale(1.1);
        }


        .modal-body {
            padding: 25px;
            text-align: center;
            overflow-y: visible;
        }

        .success-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-15px);}
            60% {transform: translateY(-5px);}
        }

        .modal-body h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .confirmation-text {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .booking-details {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel {
            background-color: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-cancel:hover {
            background-color: #e0e0e0;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-confirm:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-confirm:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        /* Schedule Table Styles */
        .schedule-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .schedule-table-wrapper {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .schedule-table th {
            background: #00BCD4;
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 500;
        }
        .schedule-table td {
            padding: 1rem;
            text-align: center;
            border: 1px solid #e0f7fa;
        }
        .time-slot {
            color: #0288D1;
            font-weight: 500;
            background: #f5f5f5;
        }
        /* Original slot style */
        .available-slot {
            background: #4CAF50;
            color: white;
            padding: 0.8rem 0.6rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transform: scale(1);
            z-index: 1;
        }
        
        /* Grid layout for multiple doctors */
        .doctors-slot {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            min-height: 40px;
            align-content: flex-start;
            width: 100%;
        }
        
        /* Base slot style for all doctors */
        .doctors-slot .available-slot,
        .doctors-slot .unavailable-slot {
            color: white;
            background: #4CAF50;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.85em;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 36px;
            width: 100%;
            font-weight: 500;
        }

        /* Unavailable slot style */
        .unavailable-slot {
            background: #FF0000 !important; /* Strong red */
            color: white;
            height: 100px; /* Increased height */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            margin: 2px 0;
            box-sizing: border-box;
        }
        
        .unavailable-slot .doctor-name {
            margin: 4px 0;
            color: white !important;
            font-size: 1em;
            font-weight: 600;
            line-height: 1.2;
            word-break: break-word;
            text-align: center;
        }
        
        /* Set consistent heights for all slot types */
        /* Single doctor slot */
        .available-slot,
        .unavailable-slot {
            min-height: 100px;
            height: 100%;
        }
        
        /* Two doctors - vertical stack */
        .two-doctors .available-slot,
        .two-doctors .unavailable-slot {
            height: 100px;
        }
        
        /* 2x2 grid */
        .four-doctors .available-slot,
        .four-doctors .unavailable-slot {
            height: 100px;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .four-doctors .doctor-name {
            font-size: 0.95em;
            line-height: 1.2;
            margin: 2px 0;
            word-break: break-word;
        }
        
        /* Many doctors grid */
        .many-doctors .available-slot,
        .many-doctors .unavailable-slot {
            height: 100px;
        }
        
        /* Layout for 2 doctors - stacked vertically */
        .two-doctors {
            flex-direction: column;
            gap: 2px;
        }
        
        #paymob-iframe-container {
            width: 100%;
            min-height: 500px;
            height: auto;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .two-doctors .available-slot,
        .two-doctors .unavailable-slot {
            width: 100%;
            flex: 0 0 auto;
            margin: 2px 0;
            padding: 4px 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Layout for 3-4 doctors (2x2 grid) */
        .four-doctors .available-slot,
        .four-doctors .unavailable-slot,
        .many-doctors .available-slot,
        .many-doctors .unavailable-slot {
            width: calc(50% - 2px);
            flex: 0 0 calc(50% - 2px);
            height: 60px;
        }
        
        /* Ensure text is properly aligned */
        .doctor-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 1em;
            margin: 2px 0;
            color: white !important;
            font-weight: 500;
            line-height: 1.3;
        }
        
        .availability-status {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 4px;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .slot-location {
            font-size: 0.8em;
            opacity: 0.9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .four-doctors .available-slot {
            width: calc(50% - 2px);
            height: 50%;
        }
        
        .many-doctors .available-slot {
            width: 100%;
            height: 25%;
            font-size: 0.8em;
        }
        
        .doctor-info {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }
        
        .doctor-name {
            font-weight: bold;
            font-size: 0.9em;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #333;
        }
        
        .slot-location {
            font-size: 0.7em;
            color: #666;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .available-slot:hover {
            background: #1B5E20;
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }
        
        /* Ensure hover effect works on all slot types */
        .two-doctors .available-slot:hover,
        .four-doctors .available-slot:hover,
        .many-doctors .available-slot:hover,
        .doctors-slot .available-slot:hover {
            background: #1B5E20 !important;
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }
        
        .doctors-slot .available-slot:hover {
            background: #1B5E20 !important;
        }
        
        .available-slot small {
            display: block;
            margin: 4px 0 6px;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .slot-location, .slot-notes {
            font-size: 0.8rem;
            margin: 6px 0 0;
            padding: 6px 0 0;
            border-top: 1px solid rgba(255,255,255,0.3);
            white-space: normal;
            line-height: 1.3;
            overflow: visible;
            text-overflow: clip;
            word-break: break-word;
        }
        
        .slot-location {
            color: #e0f7fa;
        }
        
        .slot-notes {
            font-style: italic;
            opacity: 0.9;
        }
        
        .slot-location i, .slot-notes i {
            margin-right: 6px;
            font-size: 0.8rem;
            vertical-align: middle;
        }
        .booked-slot {
            background: #ffebee;
            color: #c62828;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: not-allowed;
        }
        
        .unavailable-slot {
            background: #f44336;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: not-allowed;
        }
        .schedule-table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Search Section Styles */
        .search-section {
            max-width: 90%;
            margin: 1.5rem auto;
            padding: 1.5rem 2rem;
            background: linear-gradient(to right, #ffffff, #f8f9fa);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .search-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        .search-form {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .form-group {
            flex: 2;
            min-width: 400px;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1.25rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: #fff;
            height: 50px;
        }
        .form-control:hover {
            border-color: #2193b0;
        }
        .form-control:focus {
            outline: none;
            border-color: #2193b0;
            box-shadow: 0 0 0 4px rgba(33, 147, 176, 0.1);
        }
        .search-btn {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 0 2.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            height: 50px;
            flex: 0 0 auto;
        }
        .search-btn:hover {
            background: linear-gradient(135deg, #1c7a94, #5bb8cc);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 147, 176, 0.3);
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const specialtySelect = document.getElementById('specialty');
            const doctorSelect = document.getElementById('doctor_id');
            
            specialtySelect.addEventListener('change', function() {
                const selectedSpecialty = this.value;
                const doctorOptions = doctorSelect.querySelectorAll('option');
                
                doctorOptions.forEach(option => {
                    if (option.value === '') return; // Skip the 'Select Doctor' option
                    
                    const doctorSpecialty = option.getAttribute('data-specialty');
                    if (!selectedSpecialty || doctorSpecialty === selectedSpecialty) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Reset doctor selection
                doctorSelect.value = '';
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
        <img src="images/logo.png" alt="Logo" class="header-logo">
        <nav class="header-nav">
                <a href="index.php">Home</a>
                <a href="book_appointment.php" class="active">Book Appointment</a>
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

$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();
?>
            <div class="header-actions">
                <div class="user-profile dropdown">
                    <a href="#" class="user-info dropdown-toggle">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name"><?php 
                            $firstName = isset($_SESSION['FN']) ? $_SESSION['FN'] : '';
                            $lastName = isset($_SESSION['LN']) ? $_SESSION['LN'] : '';
                            echo htmlspecialchars($firstName . ' ' . $lastName); 
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

    <!-- Page Banner -->
    <div class="page-banner appointment-banner">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <h1>Book Appointment</h1>
            <div class="breadcrumb">
                <a href="/">Home</a>
                <span class="separator">></span>
                <span>Book Appointment</span>
            </div>
        </div>
    </div>

    <div class="search-section">
        <h2>Search Doctor Schedule</h2>
        <form id="searchForm" method="post" class="search-form">
            <div class="form-group">
                <label for="specialty">Specialty:</label>
                <select name="specialty" id="specialty" class="form-control">
                    <option value="">Select Specialty</option>
                    <option value="Cardiology">Cardiology</option>
                    <option value="Dermatology">Dermatology</option>
                    <option value="Pediatrics">Pediatrics</option>
                    <option value="Orthopedics">Orthopedics</option>
                </select>
            </div>
            <div class="form-group">
                <label for="doctor_id">Doctor:</label>
                <select name="doctor_id" id="doctor_id" class="form-control">
                    <option value="">Select Doctor</option>
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor['id'] ?>" data-specialty="<?= htmlspecialchars($doctor['specialty'] ?? '') ?>">
                                <?= htmlspecialchars($doctor['name'] ?? $doctor['FN'] . ' ' . $doctor['LN']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="search-btn">View Schedule</button>
        </form>
    </div>

    <div class="schedule-container">
        <h3>Doctor Schedule</h3>
        <div class="schedule-table-wrapper">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th class="time-header">Time</th>
                        <th>Saturday</th>
                        <th>Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $timeSlots = [
                        '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
                        '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'
                    ];
                    
                    // First, reorganize the schedule data by day and time
                    $scheduleByDayTime = [];
                    foreach ($schedule as $slot) {
                        $day = $slot['day_of_week'];
                        if (!isset($scheduleByDayTime[$day])) {
                            $scheduleByDayTime[$day] = [];
                        }
                        // Add the slot to the corresponding day
                        $scheduleByDayTime[$day][] = $slot;
                    }
                    
                    foreach ($timeSlots as $time): 
                        $currentTime = strtotime($time);
                        $currentTimeStr = date('H:i:s', $currentTime);
                    ?>
                        <tr>
                            <td class="time-slot"><?= $time ?></td>
                            <?php 
                            $days = ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday"];
                            foreach ($days as $day): 
                                $matchingSlots = [];
                                $hasBooked = false;
                                
                                // Find all matching slots for this day and time
                                if (isset($scheduleByDayTime[$day])) {
                                    foreach ($scheduleByDayTime[$day] as $slot) {
                                        $startTime = strtotime($slot['start_time']);
                                        $endTime = strtotime($slot['end_time']);
                                        
                                        if ($currentTime >= $startTime && $currentTime <= $endTime) {
                                            $isBooked = isset($slot['booked_slots']) && in_array($currentTimeStr, $slot['booked_slots']);
                                            if ($isBooked) {
                                                $hasBooked = true;
                                                break;
                                            }
                                            $matchingSlots[] = $slot;
                                        }
                                    }
                                }
                                
                                // Determine the number of available doctors
                                $availableDoctors = [];
                                $unavailableDoctors = [];
                                
                                foreach ($matchingSlots as $slot) {
                                    if ($slot['availability'] === 'Unavailable') {
                                        $unavailableDoctors[] = $slot;
                                    } else {
                                        $availableDoctors[] = $slot;
                                    }
                                }
                                
                                $totalAvailable = count($availableDoctors);
                                $totalUnavailable = count($unavailableDoctors);
                                $totalDoctors = $totalAvailable + $totalUnavailable;
                                
                                // Calculate grid class based on number of available doctors
                                $gridClass = '';
                                $useGrid = false;
                                if ($totalAvailable > 1) {  // Only use grid for 2 or more doctors
                                    $useGrid = true;
                                    if ($totalAvailable <= 2) {
                                        $gridClass = 'two-doctors';
                                    } else if ($totalAvailable <= 4) {
                                        $gridClass = 'four-doctors';
                                    } else {
                                        $gridClass = 'many-doctors';
                                    }
                                }
                                ?>
                                <td>
                                    <?php 
                                    if ($hasBooked): ?>
                                        <div class="booked-slot">Booked</div>
                                    <?php 
                                    elseif ($totalAvailable > 0 || $totalUnavailable > 0): 
                                        // Calculate total number of doctors (both available and unavailable)
                                        $totalDoctors = count($availableDoctors) + count($unavailableDoctors);
                                        
                                        // Determine grid class based on total number of doctors
                                        $gridClass = '';
                                        if ($totalDoctors > 1) {
                                            if ($totalDoctors === 2) {
                                                $gridClass = 'two-doctors';
                                            } elseif ($totalDoctors <= 4) {
                                                $gridClass = 'four-doctors';
                                            } else {
                                                $gridClass = 'many-doctors';
                                            }
                                        }
                                        
                                        if ($totalDoctors > 0): ?>
                                            <div class="doctors-slot <?= $gridClass ?>">
                                                <?php 
                                                // First show available doctors
                                                foreach ($availableDoctors as $slot): 
                                                    $doctorName = "Dr. {$slot['FN']} {$slot['LN']}";
                                                    $location = $slot['location'] ?? '';
                                                ?>
                                                    <div class="available-slot" 
                                                         data-time="<?= $currentTimeStr ?>" 
                                                         data-day="<?= $day ?>"
                                                         data-doctor-id="<?= $slot['doctor_id'] ?>"
                                                         data-doctor-name="<?= htmlspecialchars($doctorName) ?>"
                                                         data-location="<?= htmlspecialchars($location) ?>"
                                                         data-slot-fee="<?= isset($slot['slot_fee']) ? (int)$slot['slot_fee'] : 0 ?>">
                                                        <div class="availability-status">Available</div>
                                                        <div class="doctor-name"><?= htmlspecialchars($doctorName) ?></div>
                                                        <?php if (!empty($location)): ?>
                                                            <div class="slot-location">
                                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($location) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php 
                                                endforeach; 
                                                
                                                // Then show unavailable doctors
                                                foreach ($unavailableDoctors as $slot): 
                                                    $doctorName = "Dr. {$slot['FN']} {$slot['LN']}";
                                                    $location = $slot['location'] ?? '';
                                                ?>
                                                    <div class="unavailable-slot">
                                                        <div class="doctor-name"><?= htmlspecialchars($doctorName) ?></div>
                                                        <div class="availability-status">Unavailable</div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php 
                                        endif; 
                                    else: ?>
                                        <!-- Empty cell if no doctors -->
                                    <?php 
                                    endif; 
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
        </table>
    </div>
    </div>
    <footer class="main-footer">
    <div class="footer-main">
        <div class="footer-col about">
            <img src="images/logo.png" alt="Andalusia Hospital" class="footer-logo">
            <p class="footer-goal">
                Committed to your recovery and well-being — combining expert care with the latest in physical therapy
                technology to help you move better, live stronger.
            </p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
        <div class="footer-col site-content">
            <h3>Site Content</h3>
            <div class="footer-underline"></div>
            <div class="footer-links">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">Doctors</a></li>
                </ul>
                <ul>
                    <li><a href="#">Offers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Blog Map</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-col contact-info">
            <h3>Contacts Info</h3>
            <div class="footer-underline"></div>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> 26 July Mehwar Road intersection with Wahat Road, 6th October
                    City. Egypt.</li>
                <li><i class="fas fa-envelope"></i> info@msa.edu.eg</li>
                <li><i class="fas fa-phone"></i> 16672</li>
            </ul>
        </div>
        <div class="footer-col subscribe">
            <h3>Subscribe Now To The Mailing List</h3>
            <form class="subscribe-form">
                <input type="email" placeholder="Enter Your Email" required>
                <button type="submit">Subscribe</button>
            </form>
            <div class="footer-map">
                <iframe
                    src="https://www.google.com/maps?q=26+July+Mehwar+Road+intersection+with+Wahat+Road,+6th+October+City,+Egypt&output=embed"
                    width="100%" height="120" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>All rights reserved for Faculty of Physical Therapy at MSA University ©2025</span>
    </div>
</footer>
    <!-- Booking and Payment Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="logo-container">
                    <img src="images/logo.png" alt="Hospital Logo" class="modal-logo">
                    <span>Andalusia Hospital</span>
                </div>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Step 1: Booking Confirmation -->
                <div id="bookingConfirmationStep">
                    <div class="success-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Confirm Your Booking</h3>
                    <p class="confirmation-text">Please review your appointment details before proceeding to payment.</p>
                    
                    <div class="booking-details">
                        <div class="detail-row">
                            <span class="detail-label">Doctor:</span>
                            <span id="modalDoctorName" class="detail-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span id="modalAppointmentDate" class="detail-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Time:</span>
                            <span id="modalTimeSlot" class="detail-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Fee:</span>
                            <span class="detail-value" id="modalFee">0 EGP</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button id="cancelBooking" class="btn btn-cancel">Cancel</button>
                        <button id="proceedToPayment" class="btn btn-confirm">Proceed to Payment</button>
                    </div>
                </div>

                <!-- Step 2: Payment Form -->
                <div id="paymentStep" style="display: none;">
                    <div class="success-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Complete Payment</h3>
                    <p class="confirmation-text">Please enter your payment details to confirm your appointment</p>
                    
                    <div id="paymob-iframe-container" style="width: 100%; height: 400px; margin: 20px 0;">
                        <!-- Paymob iframe will be loaded here -->
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading payment...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button id="backToBooking" class="btn btn-cancel">Back</button>
                        <button id="confirmPayment" class="btn btn-confirm" style="display: none;">Confirm Payment</button>
                    </div>
                </div>

                <!-- Step 3: Success Message -->
                <div id="successStep" style="display: none; text-align: center;">
                    <div class="success-icon" style="color: #4CAF50;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Appointment Confirmed!</h3>
                    <p class="confirmation-text">Your appointment has been successfully booked and payment received.</p>
                    <div class="booking-details" id="bookingReference">
                        <!-- Booking reference will be shown here -->
                    </div>
                    <button id="closeModal" class="btn btn-confirm" style="margin-top: 20px;">Done</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Store booking data
    let currentBooking = {
        doctorId: null,
        doctorName: null,
        dayOfWeek: null,
        timeSlot: null,
        location: null,
        notes: null,
        paymentToken: null,
        bookingReference: null,
        amount: 0 // Will be set from slot_fee
    };

    // Debug: Log when the script loads
    console.log('Script loaded, setting up click handlers');

    // Get the modal elements
    const modal = document.getElementById('bookingModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBooking');
    const proceedToPaymentBtn = document.getElementById('proceedToPayment');
    const backToBookingBtn = document.getElementById('backToBooking');
    const confirmPaymentBtn = document.getElementById('confirmPayment');
    const closeModalBtn = document.getElementById('closeModal');
    
    // Get step containers
    const bookingStep = document.getElementById('bookingConfirmationStep');
    const paymentStep = document.getElementById('paymentStep');
    const successStep = document.getElementById('successStep');
    const paymobIframeContainer = document.getElementById('paymob-iframe-container');
    let paymobIframe = null;

    // Set up click handlers for available slots
    document.addEventListener('DOMContentLoaded', function() {
        // Log all available slots for debugging
        const slots = document.querySelectorAll('.available-slot');
        console.log('Found', slots.length, 'available slots');
        
        // Add click event listeners to each available slot
        slots.forEach(slot => {
            slot.addEventListener('click', function() {
                console.log('Slot clicked:', this);
                
                // Get data from the slot
                const doctorId = this.getAttribute('data-doctor-id');
                const doctorName = this.getAttribute('data-doctor-name');
                const dayOfWeek = this.getAttribute('data-day');
                const timeSlot = this.getAttribute('data-time');
                const location = this.getAttribute('data-location');
                const notes = this.getAttribute('data-notes');
                const displayTime = this.closest('tr').cells[0].textContent.trim();
                
                console.log('Slot data:', {
                    doctorId,
                    doctorName,
                    dayOfWeek,
                    timeSlot,
                    location,
                    notes,
                    displayTime
                });
                
                // Get slot fee from data attribute, default to 0 if not set
                const slotFee = parseInt(this.getAttribute('data-slot-fee')) || 0;
                
                // Store booking data
                currentBooking = {
                    doctorId: doctorId,
                    doctorName: doctorName,
                    dayOfWeek: dayOfWeek,
                    timeSlot: timeSlot,
                    location: location,
                    notes: notes,
                    displayTime: displayTime,
                    amount: slotFee // Use the slot fee from the database
                };
                
                console.log('Updated currentBooking with amount:', currentBooking);
                
                // Update modal content
                document.getElementById('modalDoctorName').textContent = doctorName;
                
                // Get the next occurrence of this day of week
                const nextDate = getNextDayOfWeek(dayOfWeek);
                document.getElementById('modalAppointmentDate').textContent = 
                    nextDate.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                
                document.getElementById('modalTimeSlot').textContent = displayTime;
                
                // Update fee display - show 'Free' if amount is 0
                const feeDisplay = document.getElementById('modalFee');
                feeDisplay.textContent = slotFee === 0 ? 'Free' : slotFee + ' EGP';
                
                // Show the modal
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        });
    });

    // Helper function to get the next occurrence of a specific day of the week
    function getNextDayOfWeek(dayName) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const today = new Date();
        const dayIndex = days.indexOf(dayName);
        
        if (dayIndex === -1) return today; // Invalid day name
        
        const todayIndex = today.getDay();
        let daysUntilNext = dayIndex - todayIndex;
        
        if (daysUntilNext <= 0) {
            // If the day has already occurred this week, get next week's occurrence
            daysUntilNext += 7;
        }
        
        const nextDate = new Date(today);
        nextDate.setDate(today.getDate() + daysUntilNext);
        return nextDate;
    }

    // Close modal when clicking on X or Cancel button
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Reset to booking step for next time
        showStep('booking');
        // Clear any existing iframe
        if (paymobIframe) {
            paymobIframe.remove();
            paymobIframe = null;
        }
    }

    // Function to create a free appointment without payment
    async function createFreeAppointment() {
        try {
            // Get the next occurrence of the selected day
            const appointmentDate = getNextDayOfWeek(currentBooking.dayOfWeek);
            const formattedDate = appointmentDate.toISOString().split('T')[0];
            
            // Prepare the appointment data according to database schema
            const appointmentData = {
                doctor_id: currentBooking.doctorId,
                patient_id: <?= $_SESSION['user_id'] ?? 0 ?>, // Get patient ID from session
                appointment_time: currentBooking.timeSlot,
                day_of_week: currentBooking.dayOfWeek,
                location: currentBooking.location || 'Clinic',
                notes: currentBooking.notes || 'Free appointment booking',
                visit_type: 'Consultation',
                action: 'create_free_appointment' // Special action for free appointments
            };
            
            console.log('Creating free appointment with data:', appointmentData);
            
            // Send the appointment data to the server using the BookingController
            const response = await fetch('../Controller/BookingController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(appointmentData).toString()
            });

            const responseText = await response.text();
            console.log('Server raw response:', responseText);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}. Response: ${responseText}`);
            }

            try {
                const result = JSON.parse(responseText);
                console.log('Appointment creation response:', result);

                if (result.success) {
                    // Update the UI to show success
                    currentBooking.bookingReference = result.booking_reference || 'FREE-' + Date.now();
                    updateUIForSuccess();
                } else {
                    throw new Error(result.error || result.message || 'Failed to create appointment');
                }
            } catch (jsonError) {
                console.error('Failed to parse JSON:', jsonError);
                throw new Error('Received an invalid response from the server. Check the console for the raw response.');
            }
        } catch (error) {
            console.error('Error creating free appointment:', error);
            alert('Failed to book appointment: ' + (error.message || 'Please try again.'));
            if (proceedToPaymentBtn) {
                proceedToPaymentBtn.disabled = false;
                proceedToPaymentBtn.innerHTML = 'Proceed to Payment';
            }
        }
    }
    
    // Function to update UI after successful booking (for both free and paid)
    function updateUIForSuccess() {
        // Mark the slot as booked in the UI
        const slot = document.querySelector(`[data-time="${currentBooking.timeSlot}"][data-day="${currentBooking.dayOfWeek}"]`);
        if (slot) {
            slot.classList.remove('available-slot');
            slot.classList.add('booked-slot');
            slot.innerHTML = 'Booked';
            slot.onclick = null;
        }
        
        // Update UI to show success
        document.getElementById('bookingReference').innerHTML = `
            <div class="detail-row">
                <span class="detail-label">Booking Reference:</span>
                <span class="detail-value">${currentBooking.bookingReference || 'N/A'}</span>
            </div>
            ${currentBooking.amount > 0 ? `
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">${currentBooking.amount} EGP</span>
            </div>` : ''}
        `;
        
        // Show success step
        showStep('success');
        
        // Close modal and redirect to dashboard after 2 seconds
        setTimeout(() => {
            closeModal();
            window.location.href = 'patient_dashboard.php';
        }, 2000);
    }
    
    // Close modal when clicking outside of it or on X button
    window.onclick = function(event) {
        if (event.target == modal || event.target.classList.contains('close')) {
            closeModal();
        }
    };
    
    // Close modal from success step
    closeModalBtn.onclick = closeModal;
    
    // Close modal when clicking cancel button
    document.getElementById('cancelBooking').onclick = closeModal;
    
    // Back to booking from payment
    backToBookingBtn.onclick = function() {
        showStep('booking');
    };
    
    // Proceed to payment or confirm free appointment
    proceedToPaymentBtn.onclick = function() {
        // Show loading state
        this.disabled = true;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Check if this is a free appointment (fee = 0)
        if (currentBooking.amount === 0) {
            // For free appointments, skip payment and directly confirm
            createFreeAppointment();
        } else {
            // For paid appointments, proceed with payment flow
            createBookingForPayment();
        }
        
        // Reset button state if there's an error
        setTimeout(() => {
            if (this.disabled) {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        }, 10000); // Reset after 10 seconds if no response
    };
    
    // Function to load Paymob iframe
    function loadPaymobIframe() {
        if (!currentBooking.paymentToken) {
            alert('Payment token not available. Please try again.');
            return;
        }
        
        paymobIframeContainer.innerHTML = ''; // Clear loading spinner
        
        // Create iframe
        paymobIframe = document.createElement('iframe');
        paymobIframe.id = 'paymob-iframe';
        paymobIframe.src = `https://accept.paymob.com/api/acceptance/iframes/929491?payment_token=${currentBooking.paymentToken}`;
        paymobIframe.style.width = '100%';
        paymobIframe.style.height = '400px';
        paymobIframe.style.border = '1px solid #ddd';
        paymobIframe.style.borderRadius = '8px';
        
        // Add event listener for iframe load
        paymobIframe.onload = function() {
            console.log('Paymob iframe loaded');
        };
        
        paymobIframeContainer.appendChild(paymobIframe);
        
        // Listen for messages from Paymob iframe
        window.addEventListener('message', handlePaymobMessage, false);
    }
    
    // Handle messages from Paymob iframe
    function handlePaymobMessage(event) {
        // Make sure the message is from Paymob
        if (event.origin !== 'https://accept.paymob.com') {
            return;
        }
        
        console.log('Message from Paymob:', event.data);
        
        // Handle different types of messages from Paymob
        if (event.data.type) {
            switch(event.data.type) {
                case 'TRANSACTION_COMPLETED':
                    console.log('Payment completed successfully', event.data);
                    // Show success message
                    showStep('success');
                    break;
                case 'TRANSACTION_FAILED':
                    console.log('Payment failed', event.data);
                    // Show success step since booking is already created
                    showStep('success');
                    break;
                case 'PAYMENT_WIDGET_LOADED':
                    console.log('Payment widget loaded');
                    break;
                default:
                    console.log('Unhandled Paymob event:', event.data);
            }
        }
    }
    
    // Create payment token for Paymob iframe
    async function createBookingForPayment() {
        // First, create the booking
        try {
            // Get the appointment date (next occurrence of the selected day)
            const appointmentDate = getNextDayOfWeek(currentBooking.dayOfWeek).toISOString().split('T')[0];
            
            // Prepare booking data
            const bookingData = {
                doctor_id: currentBooking.doctorId,
                patient_id: <?= $_SESSION['user_id'] ?? 0 ?>,
                appointment_time: currentBooking.timeSlot,
                day_of_week: currentBooking.dayOfWeek,
                appointment_date: appointmentDate,
                location: currentBooking.location || 'Clinic',
                notes: currentBooking.notes || 'Paid appointment booking',
                status: 'Scheduled',
                visit_type: 'Consultation',
                action: 'book',
                amount: currentBooking.amount
            };
            
            console.log('Creating booking with data:', bookingData);
            
            // Create the booking
            const response = await fetch('../Controller/BookingController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(bookingData).toString()
            });
            
            const responseText = await response.text();
            console.log('Booking creation response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error('Invalid JSON response from server');
            }
            
            if (result && result.success) {
                // Store the booking reference
                currentBooking.bookingReference = result.booking_reference || 'PAID-' + Date.now();
                
                // Mark the slot as booked in the UI
                const slot = document.querySelector(`[data-time="${currentBooking.timeSlot}"][data-day="${currentBooking.dayOfWeek}"]`);
                if (slot) {
                    slot.classList.remove('available-slot');
                    slot.classList.add('booked-slot');
                    slot.innerHTML = 'Booked';
                    slot.onclick = null;
                }
                
                // Now proceed with payment initiation
                const paymentData = {
                    doctor_id: currentBooking.doctorId,
                    day_of_week: currentBooking.dayOfWeek,
                    appointment_time: currentBooking.timeSlot,
                    appointment_date: appointmentDate,
                    location: currentBooking.location || 'Clinic',
                    notes: currentBooking.notes || 'Appointment booking',
                    amount: currentBooking.amount,
                    action: 'initiate_payment',
                    booking_reference: currentBooking.bookingReference
                };
                
                console.log('Initiating payment with data:', paymentData);
                
                // Send AJAX request to initiate payment
                $.ajax({
                    url: '../Controller/BookingController.php?action=initiate_payment',
                    type: 'POST',
                    dataType: 'json',
                    data: paymentData,
                    success: function(response) {
                        console.log('Payment initiation response:', response);
                        if (response && response.success && response.payment_token) {
                            // Store payment token
                            currentBooking.paymentToken = response.payment_token;
                            
                            // Proceed to payment step
                            showStep('payment');
                        } else {
                            const errorMsg = response && response.error ? response.error : 'Failed to initiate payment';
                            console.error('Payment initiation failed:', errorMsg);
                            // Still show success since booking is created
                            showStep('success');
                        }
                        proceedToPaymentBtn.disabled = false;
                        proceedToPaymentBtn.innerHTML = 'Proceed to Payment';
                    },
                    error: function(xhr, status, error) {
                        console.error('Payment initiation error:', { status, error, response: xhr.responseText });
                        // Still show success since booking is created
                        showStep('success');
                        proceedToPaymentBtn.disabled = false;
                        proceedToPaymentBtn.innerHTML = 'Proceed to Payment';
                    }
                });
                
            } else {
                throw new Error(result.error || 'Failed to create appointment');
            }
        } catch (error) {
            console.error('Error creating booking:', error);
            alert('Error creating booking: ' + (error.message || 'Please try again.'));
            proceedToPaymentBtn.disabled = false;
            proceedToPaymentBtn.innerHTML = 'Proceed to Payment';
        }
    }
    
    // Complete booking after successful payment in iframe
    async function completeBooking(paymentSuccess) {
        if (!paymentSuccess) {
            alert('Payment failed. Please try again.');
            showStep('booking');
            return;
        }
        
        // Prepare booking data
        const appointmentDate = getNextDayOfWeek(currentBooking.dayOfWeek).toISOString().split('T')[0];
        const bookingData = {
            doctor_id: currentBooking.doctorId,
            patient_id: <?= $_SESSION['user_id'] ?? 0 ?>,
            appointment_time: currentBooking.timeSlot,
            day_of_week: currentBooking.dayOfWeek,
            appointment_date: appointmentDate,
            location: currentBooking.location || 'Clinic',
            notes: currentBooking.notes || 'Paid appointment booking',
            status: 'Scheduled',
            visit_type: 'Consultation',
            action: 'book',
            amount: currentBooking.amount
        };
        
        try {
            // Create the booking in the database
            const response = await fetch('../Controller/BookingController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(bookingData).toString()
            });
            
            const responseText = await response.text();
            console.log('Booking creation response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error('Invalid JSON response from server');
            }
            
            if (result && result.success) {
                // Store the booking reference
                currentBooking.bookingReference = result.booking_reference || 'PAID-' + Date.now();
                
                // Mark the slot as booked in the UI
                const slot = document.querySelector(`[data-time="${currentBooking.timeSlot}"][data-day="${currentBooking.dayOfWeek}"]`);
                if (slot) {
                    slot.classList.remove('available-slot');
                    slot.classList.add('booked-slot');
                    slot.innerHTML = 'Booked';
                    slot.onclick = null;
                }
                
                // Show success message
                showStep('success');
            } else {
                throw new Error(result.error || 'Failed to create appointment');
            }
        } catch (error) {
            console.error('Error creating booking:', error);
            alert('Error creating booking after payment: ' + (error.message || 'Please contact support.'));
            showStep('booking');
            return;
        }
        
        // Update UI to show booking reference and amount
        document.getElementById('bookingReference').innerHTML = `
            <div class="detail-row">
                <span class="detail-label">Booking Reference:</span>
                <span class="detail-value">${currentBooking.bookingReference || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">${currentBooking.amount} EGP</span>
            </div>`;
        
        // Show success step
        showStep('success');
        
        // Close modal and redirect to dashboard after 2 seconds
        setTimeout(() => {
            closeModal();
            window.location.href = 'patient_dashboard.php';
        }, 2000);
        
        // Remove message listener to prevent memory leaks
        window.removeEventListener('message', handlePaymobMessage);
    }

    // Function to show a specific step
    function showStep(step) {
        // Hide all steps first
        bookingStep.style.display = 'none';
        paymentStep.style.display = 'none';
        successStep.style.display = 'none';
        
        // Show the requested step
        if (step === 'booking') {
            bookingStep.style.display = 'block';
        } else if (step === 'payment') {
            // Only show payment step if there's an amount to pay
            if (currentBooking.amount > 0) {
                paymentStep.style.display = 'block';
                // Load Paymob iframe when showing payment step
                if (!paymobIframe && currentBooking.paymentToken) {
                    loadPaymobIframe();
                }
            } else {
                // If it's a free appointment, skip to success
                showStep('success');
            }
        } else if (step === 'success') {
            successStep.style.display = 'block';
        }
    }

    // Handle payment confirmation (this would be called from the Paymob iframe callback)
    // The actual implementation would depend on Paymob's callback mechanism
    function onPaymentSuccess(transactionId) {
        console.log('Payment successful, transaction ID:', transactionId);
        completeBooking(true);
    }
    
    function onPaymentFailed(error) {
        console.error('Payment failed:', error);
        completeBooking(false);
    }
    </script>
</body>
</html>
