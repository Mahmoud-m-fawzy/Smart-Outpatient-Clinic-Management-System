<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/StaffController.php';

// Initialize controller
$controller = new StaffController();

// Define days (0 = Sunday, 6 = Saturday)
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday'];

// Set Wednesday (index 3) as the default day
$defaultDay = 3; // Wednesday
$currentDay = date('w');

// Use the day from URL if provided, otherwise use Wednesday as default
$activeDay = isset($_GET['day']) ? max(0, min(5, (int)$_GET['day'])) : $defaultDay;

// Get the selected day name
$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Saturday'];
$selectedDay = $dayNames[$activeDay] ?? date('l');

// Get status board data for the selected day
$result = $controller->getStatusBoardData($activeDay);

// Get unavailable slots for the selected day
$unavailableSlots = $controller->getUnavailableSlotsByDay($selectedDay);

// Set default rooms in case of error
$rooms = [];

if ($result['success']) {
    $rooms = $result['rooms'];
    
    // Add unavailable slots to each room
    foreach ($rooms as &$room) {
        $room['unavailable_slots'] = $unavailableSlots[$room['name']] ?? [];
    }
    unset($room); // Break the reference
} else {
    // Log error and show empty board
    error_log('Error loading status board: ' . ($result['error'] ?? 'Unknown error'));
    // Create empty rooms as fallback
    $roomNames = ['Clinic A', 'Clinic B', 'Clinic C', 'Clinic D'];
    foreach ($roomNames as $roomName) {
        $rooms[] = [
            'name' => $roomName,
            'status' => 'Free',
            'patient' => '',
            'therapist' => '',
            'end_time' => null,
            'appointment_status' => null,
            'appointment_number' => '',
            'next_appointment' => null,
            'unavailable_slots' => $unavailableSlots[$roomName] ?? []
        ];
    }
}
?> 
<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة حالة العيادات</title>
    <link rel="stylesheet" href="css/status_board.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .status-transition {
            transition: all 0.5s ease-in-out;
        }
        .pulse-animation {
            animation: pulse 0.5s ease-in-out;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            direction: ltr;
        }
        .days-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .day-tab {
            padding: 10px 25px;
            background: #e0e0e0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        .day-tab.active {
            background: #1976d2;
            color: white;
        }
        .day-tab.today {
            position: relative;
        }
        .day-tab.today::after {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            background: #ff5722;
            border-radius: 50%;
        }
        .board-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            padding: 48px 3vw;
            max-width: 1600px;
            margin: 0 auto;
        }

        @media (max-width: 1400px) {
            .board-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .board-container {
                grid-template-columns: 1fr;
                padding: 24px;
            }
        }

        .room-card {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 22px;
            border: none;
            box-shadow: 0 8px 32px rgba(30,60,114,0.13), 0 1.5px 6px rgba(42,82,152,0.08);
            min-height: 240px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid #2563eb;
            backdrop-filter: blur(8px);
        }

        .room-card:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 16px 48px rgba(30,60,114,0.18), 0 2px 8px rgba(42,82,152,0.13);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px 16px;
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .room-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .room-name i {
            font-size: 1.3rem;
            color: #2563eb;
        }

        .status-badge {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 6px 16px 6px 12px;
            border-radius: 999px;
            border: 2px solid #2563eb;
            background: rgba(255,255,255,0.7);
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge.occupied {
            border-color: #64748b;
            color: #64748b;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .status-dot.free {
            background: #22c55e;
            box-shadow: 0 0 6px #22c55e44;
        }

        .status-dot.occupied {
            background: #ef4444;
            box-shadow: 0 0 6px #ef444444;
        }

        .appointment-info {
            padding: 16px 28px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 1.1rem;
            color: #334155;
        }

        .info-label {
            font-weight: 500;
            color: #64748b;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        .countdown {
            font-family: 'Roboto Mono', monospace;
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin: 16px 0;
            color: #2563eb;
            letter-spacing: 1px;
        }

        .next-appointment {
            background: rgba(0, 0, 0, 0.02);
            padding: 16px 28px;
            border-top: 1px dashed rgba(0, 0, 0, 0.08);
        }

        .next-appointment .label {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .next-appointment .info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .appointment-number {
            font-weight: 600;
            color: #1e293b;
        }

        .time-left {
            font-size: 1rem;
            font-weight: 600;
            color: #f59e0b;
            background: #fef3c7;
            padding: 4px 12px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin: 15px 0 20px;">
        <div style="display: flex; justify-content: center; gap: 15px;">
            <a href="/MVC/view/staff_dashboard.php" class="dashboard-button" style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; transition: all 0.3s;">
                <i class="fas fa-arrow-right" style="margin-right: 8px;"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
    <div class="days-tabs">
        <?php 
        foreach ($days as $dayIndex => $dayName): 
            $isToday = $dayIndex === $currentDay;
            $isActive = $dayIndex === $activeDay;
            $tabClasses = ['day-tab'];
            
            if ($isActive) {
                $tabClasses[] = 'active';
            }
            if ($isToday) {
                $tabClasses[] = 'today';
            }
            
            // Ensure today is always active by default if no day is selected
            if (!isset($_GET['day']) && $isToday) {
                $isActive = true;
                $tabClasses = ['day-tab', 'active', 'today'];
            }
        ?>
            <button class="<?php echo implode(' ', $tabClasses); ?>" onclick="window.location.href='?day=<?php echo $dayIndex; ?>'">
                <?php echo $dayName; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="board-container">
        <?php foreach ($rooms as $index => $room): ?>
            <?php
                $remaining = ($room['status'] === 'Occupied' && $room['end_time']) ? $room['end_time'] - time() : null;
                $badgeClass = $room['status'] === 'Free' ? 'status-badge free' : 'status-badge occupied';
                $dotClass = $room['status'] === 'Free' ? 'status-dot free' : 'status-dot occupied';
                $nextAppointment = $room['next_appointment'] ?? null;
            ?>
            <?php
                // Use start_time from the room data
                $startTime = $room['start_time'] ?? time();
                if (is_string($startTime)) {
                    $startTime = strtotime($startTime);
                }
                $endTime = $room['end_time'] ?? 0;
                if (is_string($endTime)) {
                    $endTime = strtotime($endTime);
                }
            ?>
            <div class="room-card" 
                data-status="<?php echo $room['status']; ?>" 
                data-starttime="<?php echo $startTime; ?>"
                data-endtime="<?php echo $endTime; ?>"
                <?php if (isset($room['next_appointment'])): ?>
                data-next-time="<?php echo $room['next_appointment']['start_time']; ?>"
                <?php endif; ?>
                id="room-<?php echo $index; ?>">
                <div class="card-header">
                    <div class="debug-info" style="font-size: 12px; color: #666; margin-bottom: 5px; background: #f5f5f5; padding: 4px 8px; border-radius: 4px;">
                        <?php if (isset($room['start_time'])): ?>
                            <?php 
                                $totalDuration = $endTime - $startTime;
                                $minutes = floor($totalDuration / 60);
                                $seconds = $totalDuration % 60;
                            ?>
                            <div>Start: <?php echo date('H:i:s', $startTime); ?></div>
                            <div>End: <?php echo $endTime ? date('H:i:s', $endTime) : 'N/A'; ?></div>
                            <div>Total: <?php echo "$minutes m $seconds s"; ?></div>
                        <?php endif; ?>
                    </div>
                    <h3 class="room-name">
                        <i class="fas fa-clinic-medical"></i>
                        <?php echo htmlspecialchars($room['name']); ?>
                    </h3>

                </div>

                <div class="appointment-info">
                    <?php if ($room['status'] === 'Occupied'): ?>
                        <div class="info-row">
                            <span class="info-label">Appointment #</span>
                            <span class="info-value">#<?php echo htmlspecialchars($room['appointment_number'] ?? '--'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Patient</span>
                            <span class="info-value"><?php echo htmlspecialchars($room['patient'] ?: '--'); ?></span>
                        </div>
                        <div class="info-row" style="display: flex; align-items: center; gap: 8px;">
                            <span class="info-label">Doctor</span>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="info-value" style="font-weight: 500;"><?php echo htmlspecialchars($room['therapist'] ?: '--'); ?></span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: <?php echo $room['status'] === 'Occupied' ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo $room['status'] === 'Occupied' ? '#b91c1c' : '#166534'; ?>; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; white-space: nowrap;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?php echo $room['status'] === 'Occupied' ? '#dc2626' : '#16a34a'; ?>;"></span>
                                    <?php echo $room['status'] === 'Occupied' ? 'OCCUPIED' : 'AVAILABLE'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Debug: start_time=<?php echo date('Y-m-d H:i:s', $room['start_time'] ?? 0); ?> end_time=<?php echo date('Y-m-d H:i:s', $room['end_time'] ?? 0); ?> now=<?php echo date('Y-m-d H:i:s'); ?> -->
                        <div id="timer-<?php echo $index; ?>" class="countdown" style="color: #dc2626; font-weight: 500; margin-top: 8px;">
                            <?php 
                                $now = time();
                                $startTime = $room['start_time'] ?? 0;
                                $endTime = $room['end_time'] ?? 0;
                                $appointmentDuration = $endTime - $startTime;
                                $elapsed = $now - $startTime;
                                $remaining = max(0, $appointmentDuration - $elapsed);
                                
                                if (!$startTime || !$endTime) {
                                    echo 'No time data';
                                } else if ($now < $startTime) {
                                    // Time until appointment starts
                                    $untilStart = $startTime - $now;
                                    $hours = floor($untilStart / 3600);
                                    $minutes = floor(($untilStart % 3600) / 60);
                                    $seconds = $untilStart % 60;
                                    echo 'Starts in: ' . sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                } else if ($now < $endTime) {
                                    // Time remaining in appointment (based on actual duration)
                                    $hours = floor($remaining / 3600);
                                    $minutes = floor(($remaining % 3600) / 60);
                                    $seconds = $remaining % 60;
                                    echo 'Time left: ' . sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                } else {
                                    // Appointment ended
                                    echo 'Appointment completed';
                                }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="available-content" style="text-align: center;">
                            <?php if (!empty($room['unavailable_slots'])): ?>
                                <div style="margin-top: 12px;">
                                    <div style="font-weight: 600; color: #1d4ed8; margin-bottom: 8px; font-size: 1.1rem;">
                                        Next Appointment:
                                    </div>
                                    <?php 
                                    $slotIndex = 0;
                                    foreach ($room['unavailable_slots'] as $slot): 
                                        // Set timezone to match your server's timezone
                                        date_default_timezone_set('Asia/Riyadh'); // Adjust this to your server's timezone
                                        
                                        // Get current date in Y-m-d format
                                        $currentDate = date('Y-m-d');
                                        
                                        // Get start and end times directly from the slot
                                        $startTime = isset($slot['start_time']) ? $slot['start_time'] : $slot['start'];
                                        $endTime = isset($slot['end_time']) ? $slot['end_time'] : $slot['end'];
                                        
                                        // Create full datetime strings in server's timezone
                                        $slotStart = strtotime($currentDate . ' ' . $startTime . ' ' . date_default_timezone_get());
                                        $slotEnd = strtotime($currentDate . ' ' . $endTime . ' ' . date_default_timezone_get());
                                        
                                        // If end time is before start time, it means it's on the next day
                                        if ($slotEnd <= $slotStart) {
                                            $slotEnd = strtotime('+1 day', $slotEnd);
                                        }
                                        
                                        // Debug output with timezone info
                                        error_log("Server Timezone: " . date_default_timezone_get());
                                        error_log("Current Time: " . date('Y-m-d H:i:s'));
                                        error_log("Slot Start: " . date('Y-m-d H:i:s', $slotStart));
                                        error_log("Slot End: " . date('Y-m-d H:i:s', $slotEnd));
                                        
                                        $currentTime = time();
                                        $isActive = ($currentTime >= $slotStart && $currentTime < $slotEnd);
                                        $slotId = 'slot-' . $index . '-' . $slotIndex++;
                                        
                                        // Debug output
                                        error_log("Slot Times - Start: " . date('Y-m-d H:i:s', $slotStart) . ", End: " . date('Y-m-d H:i:s', $slotEnd));
                                    ?>
                                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                <div>
                                                    <?php if (isset($slot['patient_name'])): ?>
                                                        <div style="font-size: 1.5rem; color: #1e40af; font-weight: 700; margin-bottom: 4px;">
                                                            <?php echo htmlspecialchars($slot['patient_name']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div style="font-size: 0.9rem; color: #4b5563; font-weight: 400; margin-top: 2px;">
                                                        Dr. <?php echo htmlspecialchars($slot['doctor']); ?>
                                                    </div>
                                                </div>
                                                <div id="status-<?php echo $slotId; ?>" class="status-text" style="font-size: 0.9rem; color: #4b5563; margin-top: 8px; transition: all 0.3s ease-in-out;">
                                                    Available
                                                </div>
                                                <div id="countdown-<?php echo $slotId; ?>" class="countdown" style="font-weight: bold; font-size: 1.2rem; color: #1d4ed8; font-family: 'Courier New', monospace;">
                                                    <?php 
                                                    if ($isActive) {
                                                        // Show time until end when active
                                                        $showTime = max(0, $slotEnd - $currentTime);
                                                    } else if ($currentTime >= $slotStart) {
                                                        // If we're past the slot time, show elapsed time
                                                        $showTime = $currentTime - $slotStart;
                                                    } else {
                                                        // Show time until start when in future
                                                        $showTime = $slotStart - $currentTime;
                                                    }
                                                    $hours = floor($showTime / 3600);
                                                    $min = floor(($showTime % 3600) / 60);
                                                    $sec = $showTime % 60;
                                                    echo sprintf('%02d:%02d:%02d', $hours, $min, $sec);
                                                    ?>
                                                </div>
                                            </div>
                                            <script>
                                                // Initialize immediately
                                                (function() {
                                                    var countdownEl = document.getElementById('countdown-<?php echo $slotId; ?>');
                                                    var statusEl = document.getElementById('status-<?php echo $slotId; ?>');
                                                    
                                                    // Get the current date in YYYY-MM-DD format
                                                    var now = new Date();
                                                    var currentDate = now.getFullYear() + '-' + 
                                                                   String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                                                                   String(now.getDate()).padStart(2, '0');
                                                    
                                                    // Get the exact times from PHP (already in server's timezone)
                                                    var slotStart = <?php echo $slotStart; ?>; // Unix timestamp in server timezone
                                                    var slotEnd = <?php echo $slotEnd; ?>;     // Unix timestamp in server timezone
                                                    
                                                    // Get current time in server's timezone (in seconds)
                                                    var nowServer = Math.floor(Date.now() / 1000);
                                                    
                                                    // Calculate timezone offset in seconds
                                                    var timezoneOffset = new Date().getTimezoneOffset() * 60;
                                                    
                                                    // Adjust for timezone differences
                                                    var slotStartAdjusted = slotStart - timezoneOffset;
                                                    var slotEndAdjusted = slotEnd - timezoneOffset;
                                                    
                                                    // Convert to milliseconds for Date object
                                                    var slotStartMs = slotStartAdjusted * 1000;
                                                    var slotEndMs = slotEndAdjusted * 1000;
                                                    
                                                    // Debug output
                                                    console.log('Server Time:', new Date(slotStartMs).toISOString(), 'to', new Date(slotEndMs).toISOString());
                                                    console.log('Local Time:', new Date().toString());
                                                    
                                                    function updateCountdown() {
                                                        var now = Math.floor(Date.now() / 1000);
                                                        var timeDiff;
                                                        
                                                        if (now < slotStart) {
                                                            // Before appointment - countdown to start
                                                            timeDiff = slotStart - now;
                                                            var hours = Math.floor(timeDiff / 3600);
                                                            var min = Math.floor((timeDiff % 3600) / 60);   
                                                            var sec = timeDiff % 60;
                                                            countdownEl.textContent = 
                                                                (hours < 10 ? '0' + hours : hours) + ':' +
                                                                (min < 10 ? '0' + min : min) + ':' + 
                                                                (sec < 10 ? '0' + sec : sec);
                                                            countdownEl.style.color = '#1d4ed8'; // Blue for countdown
                                                            statusEl.textContent = 'Available';
                                                            statusEl.style.color = '#4b5563';
                                                            statusEl.style.fontWeight = 'normal';
                                                        } else if (now < slotEnd) {
                                                            // During appointment - countdown to end
                                                            timeDiff = slotEnd - now;
                                                            var hours = Math.floor(timeDiff / 3600);
                                                            var min = Math.floor((timeDiff % 3600) / 60);
                                                            var sec = timeDiff % 60;
                                                            countdownEl.textContent = 
                                                                (hours < 10 ? '0' + hours : hours) + ':' +
                                                                (min < 10 ? '0' + min : min) + ':' + 
                                                                (sec < 10 ? '0' + sec : sec);
                                                            
                                                            // Add occupied effect if just became occupied
                                                            if (statusEl.textContent !== 'Occupied') {
                                                                countdownEl.style.color = '#dc2626';
                                                                statusEl.textContent = 'Occupied';
                                                                statusEl.style.color = '#dc2626';
                                                                statusEl.style.fontWeight = 'bold';
                                                                
                                                                // Get patient info for announcement and update UI
                                                                const card = countdownEl.closest('.room-card');
                                                                if (card) {
                                                                    // Get patient and room info for announcement
                                                                    const patientName = card.querySelector('.info-row:nth-child(2) .info-value')?.textContent?.trim() || 'يوسف محمد';
                                                                    let roomName = card.querySelector('.room-name')?.textContent?.trim() || 'العيادة';
                                                                    // Convert room name to Arabic if it's in English
                                                                    const roomMap = {
                                                                        'Clinic A': 'العيادة أ',
                                                                        'Clinic B': 'العيادة ب',
                                                                        'Clinic C': 'العيادة ج',
                                                                        'Clinic D': 'العيادة د',
                                                                        'Room': 'العيادة'
                                                                    };
                                                                    roomName = roomMap[roomName] || roomName;
                                                                    const appointmentNumber = card.querySelector('.info-row:first-child .info-value')?.textContent?.trim() || '';
                                                                     
                                                                    // Create and speak professional announcement in Arabic with patient name
                                                                    const announcement = `السيد/ ${patientName}، يرجى التوجه إلى ${roomName}`;
                                                                    speak(announcement);
                                                                     
                                                                    // Add pulse animation
                                                                    card.classList.add('pulse-animation');
                                                                    card.style.borderLeft = '6px solid #dc2626';
                                                                    card.style.boxShadow = '0 4px 6px -1px rgba(220, 38, 38, 0.2)';
                                                                    
                                                                    // Remove animation after it completes
                                                                    setTimeout(() => {
                                                                        card.classList.remove('pulse-animation');
                                                                    }, 500);
                                                                }
                                                            }
                                                            
                                                            // Add goal celebration effect when countdown reaches zero
                                                            if (timeDiff <= 0) {
                                                                triggerGoalCelebration(countdownEl);
                                                            }
                                                        } else {
                                                            // After appointment - mark as available with effect
                                                            if (statusEl.textContent !== 'Available') {
                                                                countdownEl.textContent = '00:00:00';
                                                                countdownEl.style.color = '#10b981'; // Green for available
                                                                statusEl.textContent = 'Available';
                                                                statusEl.style.color = '#10b981';
                                                                statusEl.style.fontWeight = '600';
                                                                
                                                                // Update the card to show available state with animation
                                                                const card = document.querySelector(`#room-<?php echo $index; ?>`);

                                                            }
                                                        }
                                                    }
                                                    
                                                    // Update immediately and then every second
                                                    updateCountdown();
                                                    setInterval(updateCountdown, 1000);
                                                    
                                                    // Goal celebration effect
                                                    function triggerGoalCelebration(element) {
                                                        // Add animation class
                                                        element.classList.add('goal-celebration');
                                                        
                                                        // Remove animation class after it completes
                                                        setTimeout(() => {
                                                            element.classList.remove('goal-celebration');
                                                        }, 2000);
                                                    }
                                                    
                                                    // Add CSS for goal celebration
                                                    var style = document.createElement('style');
                                                    style.textContent = `
                                                        @keyframes goal {
                                                            0% { transform: scale(1); }
                                                            50% { transform: scale(1.5); color: #16a34a; }
                                                            100% { transform: scale(1); }
                                                        }
                                                        .goal-celebration {
                                                            animation: goal 1s ease-in-out;
                                                            color: #16a34a !important;
                                                            font-weight: bold !important;
                                                        }
                                                    `;
                                                    document.head.appendChild(style);
                                                })();
                                            </script>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="countdown" style="color: #22c55e; font-size: 2.2rem; margin: 20px 0 10px; font-weight: 600;">
                                    Available Today
                                </div>
                                <?php if (isset($room['next_appointment'])): ?>
                                    <div class="next-appointment-info" style="background: #f0f9ff; border-radius: 12px; padding: 12px; margin-top: 15px;">
                                        <div style="font-size: 0.9rem; color: #0369a1; margin-bottom: 5px;">
                                            Next: #<?php echo htmlspecialchars($room['next_appointment']['appointment_number'] ?? '--'); ?>
                                        </div>
                                        <div class="time-until" style="font-family: 'Roboto Mono', monospace; font-weight: 600; color: #0ea5e9;">
                                            <?php 
                                                $timeUntil = $room['next_appointment']['start_time'] - time();
                                                if ($timeUntil > 0) {
                                                    $hours = floor($timeUntil / 3600);
                                                    $minutes = floor(($timeUntil % 3600) / 60);
                                                    echo $hours > 0 ? "in $hours h $minutes m" : "in $minutes m";
                                                } else {
                                                    echo 'Starting now';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($room['next_appointment'])): ?>
                    <div class="next-appointment">
                        <div class="label">Next Appointment</div>
                        <div class="info">
                            <span class="appointment-number">#<?php echo htmlspecialchars($room['next_appointment']['appointment_number'] ?? '--'); ?></span>
                            <span class="time-left" id="next-timer-<?php echo $index; ?>">
                                <?php 
                                    $timeLeft = $room['next_appointment']['start_time'] - time();
                                    if ($timeLeft > 0) {
                                        $hours = floor($timeLeft / 3600);
                                        $minutes = floor(($timeLeft % 3600) / 60);
                                        echo $hours > 0 ? "$hours h $minutes m" : "$minutes m";
                                    } else {
                                        echo 'Now';
                                    }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
    // Function to speak the given text in Arabic
    function speak(text) {
        if ('speechSynthesis' in window) {
            const speech = new SpeechSynthesisUtterance();
            speech.text = text;
            speech.lang = 'ar-SA'; // Arabic (Saudi Arabia)
            speech.volume = 1;
            speech.rate = 0.9; // Slightly slower for better Arabic pronunciation
            speech.pitch = 1;
            window.speechSynthesis.speak(speech);
        }
    }
    
    function updateTimers() {
        const now = Math.floor(Date.now() / 1000);
        
        document.querySelectorAll('.room-card').forEach(function(card, idx) {
            let status = card.getAttribute('data-status');
            const startTime = parseInt(card.getAttribute('data-starttime'));
            const endTime = parseInt(card.getAttribute('data-endtime'));
            
            const badge = document.getElementById('badge-' + idx);
            const timerDiv = document.getElementById('timer-' + idx);
            const nextTimerDiv = document.getElementById('next-timer-' + idx);
            
            // Update current appointment timer
            if (status === 'Occupied' && startTime && endTime) {
                // Convert timestamps to seconds (in case they're in milliseconds)
                const start = startTime.toString().length > 10 ? Math.floor(startTime / 1000) : startTime;
                const end = endTime.toString().length > 10 ? Math.floor(endTime / 1000) : endTime;
                
                // Calculate remaining time in seconds
                const remaining = Math.max(0, end - now);
                
                if (remaining > 0) {
                    // Calculate hours, minutes, and seconds
                    const hours = Math.floor(remaining / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;
                    
                    // Always show time in HH:MM:SS format
                    const timeString = [
                        hours.toString().padStart(2, '0'),
                        minutes.toString().padStart(2, '0'),
                        seconds.toString().padStart(2, '0')
                    ].join(':');
                    
                    if (timerDiv) {
                        timerDiv.textContent = timeString;
                        
                        // Add visual feedback for time running low
                        if (remaining < 300) { // Less than 5 minutes
                            timerDiv.style.color = '#ff4444';
                            timerDiv.style.fontWeight = 'bold';
                        } else if (remaining < 900) { // Less than 15 minutes
                            timerDiv.style.color = '#ff9900';
                        } else {
                            timerDiv.style.color = '';
                            timerDiv.style.fontWeight = '';
                        }
                    }
                } else {
                    // Session ended, refresh page to update status
                    window.location.reload();
                }
            }
            
            // Update next appointment timer
            if (nextTimerDiv) {
                const nextAppointmentTime = parseInt(card.getAttribute('data-next-time'));
                if (nextAppointmentTime) {
                    const timeLeft = nextAppointmentTime - now;
                    if (timeLeft > 0) {
                        const hours = Math.floor(timeLeft / 3600);
                        const minutes = Math.floor((timeLeft % 3600) / 60);
                        nextTimerDiv.textContent = hours > 0 ? `${hours} ساعة و ${minutes} دقيقة` : `${minutes} دقيقة`;
                    } else {
                        nextTimerDiv.textContent = 'الآن';
                        // Refresh page to update to current appointment
                        setTimeout(() => window.location.reload(), 60000);
                    }
                }
            }
        });
    }
    
    // Update timers every second
    setInterval(updateTimers, 1000);
    
    // Initial update
    window.onload = function() {
        updateTimers();
        // Refresh page every 5 minutes to sync with server
        setTimeout(() => window.location.reload(), 5 * 60 * 1000);
    };
    
    // Auto-refresh the page every 30 minutes to prevent any memory leaks
    setTimeout(() => window.location.reload(), 30 * 60 * 1000);
    </script>
</body>
</html>