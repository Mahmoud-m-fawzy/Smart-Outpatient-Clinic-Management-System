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
            width: 90%;
            max-width: 500px;
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
            padding: 30px;
            text-align: center;
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
        .available-slot {
            background: #4CAF50;
            color: white;
            padding: 0.8rem 0.6rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .available-slot:hover {
            background: #43A047;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
                    foreach ($timeSlots as $time): ?>
                        <tr>
                            <td class="time-slot"><?= $time ?></td>
                    <?php foreach (["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday"] as $day): ?>
                        <?php
                        $currentTime = strtotime($time);
                        $currentTimeStr = date('H:i:s', $currentTime);
                        $slotAvailable = false;
                        $isBooked = false;

                        // Check schedule for this day and time
                        foreach ($schedule as $slot) {
                            if ($slot['day_of_week'] === $day) {
                                $startTime = strtotime($slot['start_time']);
                                $endTime = strtotime($slot['end_time']);
                                
                                if ($currentTime >= $startTime && $currentTime <= $endTime) {
                                    $slotAvailable = true;
                                    // Check if this slot is booked
                                    if (isset($slot['booked_slots']) && in_array($currentTimeStr, $slot['booked_slots'])) {
                                        $isBooked = true;
                                    }
                                    $doctorName = $slot['doctor_name'];
                                    break;
                                }
                            }
                        }

                        if ($slotAvailable): ?>
                            <td>
                                <?php if ($isBooked): ?>
                                    <div class="booked-slot">Booked</div>
                                <?php elseif ($slot['availability'] === 'Unavailable'): ?>
                                    <div class="unavailable-slot">
                                        Unavailable
                                    </div>
                                <?php else: ?>
                                    <div class="available-slot" 
                                         data-time="<?= $currentTimeStr ?>" 
                                         data-day="<?= $day ?>"
                                         data-doctor-id="<?= $slot['doctor_id'] ?>"
                                         data-doctor-name="<?= htmlspecialchars($doctorName) ?>"
                                         data-location="<?= htmlspecialchars($slot['location'] ?? 'Not specified') ?>"
                                         data-notes="<?= htmlspecialchars($slot['notes'] ?? 'No notes') ?>">
                                        Available<br>
                                        <small><?= htmlspecialchars($doctorName) ?></small>
                                        <?php if (!empty($slot['location'])): ?>
                                            <div class="slot-location">
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($slot['location']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
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
    <!-- FawryPay-like Confirmation Modal -->
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
                <div class="success-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Confirm Your Booking</h3>
                <p class="confirmation-text">Do you want to book this appointment?</p>
                
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
                </div>
                
                <div class="action-buttons">
                    <button id="cancelBooking" class="btn btn-cancel">Cancel</button>
                    <button id="confirmBooking" class="btn btn-confirm">Confirm Booking</button>
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
        notes: null
    };

    // Debug: Log when the script loads
    console.log('Script loaded, setting up click handlers');

    // Get the modal elements
    const modal = document.getElementById('bookingModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBooking');
    const confirmBtn = document.getElementById('confirmBooking');

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
                
                // Store booking data
                currentBooking = {
                    doctorId: doctorId,
                    doctorName: doctorName,
                    dayOfWeek: dayOfWeek,
                    timeSlot: timeSlot,
                    location: location,
                    notes: notes,
                    displayTime: displayTime
                };
                
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
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    cancelBtn.onclick = function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Handle booking confirmation
    confirmBtn.addEventListener('click', function() {
        // Disable button to prevent double submission
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        console.log('Sending appointment data:', {
            doctor_id: currentBooking.doctorId,
            day_of_week: currentBooking.dayOfWeek,
            appointment_time: currentBooking.timeSlot
        });
        
        // Get the appointment date (next occurrence of the selected day)
        const appointmentDate = getNextDayOfWeek(currentBooking.dayOfWeek).toISOString().split('T')[0];
        
        // Prepare request data
        const requestData = {
            doctor_id: currentBooking.doctorId,
            day_of_week: currentBooking.dayOfWeek,
            appointment_time: currentBooking.timeSlot,
            appointment_date: appointmentDate,
            location: currentBooking.location,
            notes: currentBooking.notes
        };
        
        console.log('Sending request data:', requestData);
        
        // Send AJAX request to book the appointment
        $.ajax({
            url: '../Controller/BookingController.php?action=book',
            type: 'POST',
            dataType: 'json',
            data: requestData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Appointment booked successfully!');
                    // Refresh the page to update the schedule
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.error || 'Failed to book appointment'));
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Confirm Booking';
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    readyState: xhr.readyState
                });
                
                let errorMsg = 'Network error. Please try again.';
                if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.error || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText || errorMsg;
                    }
                }
                alert('Error: ' + errorMsg);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm Booking';
            }
        });
    });
    </script>
</body>
</html>
