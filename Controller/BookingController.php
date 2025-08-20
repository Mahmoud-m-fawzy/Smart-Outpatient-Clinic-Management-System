<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../Model/Patient.php");
require_once("../Model/Doctor.php");
require_once("../Model/Database.php");
require_once(__DIR__ . "/../Service/NotificationService.php");

class BookingController {
    private $patient;
    private $db;
    private $link;

    public function __construct() {
        $this->patient = new Patient();
        $this->db = new Database();
        $this->link = $this->db->connectToDB();
    }

    // Paymob API Configuration
    private $paymobApiKey = 'ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRBMU1URTJPQ3dpYm1GdFpTSTZJakUzTlRBMU1UZ3dNakV1TURnNU56TWlmUS50eEhhd0tNR1YzbzVNQWxZWUc4THI0OE1QeExVaEY2T0hiOEZhNGxkc241N0ctaHpUdEFoQUtUclgtZ0dNNkY3anZYVVc1TWZZSTlYS0ZCR1RHY2MxZw==';
    private $paymobIntegrationId = '5134946'; 
    private $paymobIframeId = '929491'; 
    
    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            return;
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        if ($action === 'book') {
            $this->handleBooking();
        } elseif ($action === 'initiate_payment') {
            $this->initiatePayment();
        } elseif ($action === 'create_free_appointment') {
            $this->createFreeAppointment();
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    }

    private function sendAppointmentNotification($patientId, $doctorId, $appointmentDate, $appointmentTime) {
        // Initialize NotificationService
        $notificationService = new NotificationService();
        
        // Get patient's phone number from database
        $patient = new Patient();
        $patientData = $patient->getPatientById($patientId);
        
        if (!$patientData || empty($patientData['phone_number'])) {
            error_log("Could not send notification: Patient phone number not found");
            return false;
        }
        
        // Get doctor's name
        $doctor = new Doctor();
        $doctorData = $doctor->getDoctorById($doctorId);
        $doctorName = $doctorData ? $doctorData['name'] : 'your doctor';
        
        // Format the date and time
        $formattedDate = date('l, F j, Y', strtotime($appointmentDate));
        $formattedTime = date('g:i A', strtotime($appointmentTime));
        
        // Send notifications
        try {
            // Send SMS
            $smsMessage = "Your appointment with Dr. $doctorName has been booked for $formattedDate at $formattedTime. Thank you!";
            $notificationService->sendSms($patientData['phone_number'], $smsMessage);
            
            // Send WhatsApp
            $whatsappMessage = "Your appointment with Dr. $doctorName has been booked for $formattedDate at $formattedTime. Thank you!";
            $notificationService->sendWhatsapp($patientData['phone_number'], $formattedDate, $formattedTime);
            
            return true;
        } catch (Exception $e) {
            error_log("Error sending notifications: " . $e->getMessage());
            return false;
        }
    }
    
    private function handleBooking() {
        header('Content-Type: application/json');
        
        try {
            // Log received data for debugging
            error_log('Received POST data: ' . print_r($_POST, true));
            error_log('Session data: ' . print_r($_SESSION, true));
            
            // Validate input
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $doctor_id = $_POST['doctor_id'] ?? null;
            $appointment_date = $_POST['appointment_date'] ?? null;
            $time_slot = $_POST['appointment_time'] ?? null;  // Changed from time_slot to appointment_time
            $day_of_week = $_POST['day_of_week'] ?? null;
            $location = $_POST['location'] ?? 'Not specified';
            $notes = $_POST['notes'] ?? 'No notes';
            $patient_id = $_SESSION['user_id'] ?? null;

            error_log("Validating fields - doctor_id: $doctor_id, appointment_date: $appointment_date, time_slot: $time_slot, day_of_week: $day_of_week, patient_id: $patient_id");

            if (!$doctor_id || !$appointment_date || !$time_slot || !$day_of_week || !$patient_id) {
                throw new Exception('Missing required fields');
            }

            // Start transaction
            mysqli_begin_transaction($this->link);

            // 1. Check if the slot is still available
            $checkSql = "SELECT id, availability, start_time, end_time FROM doctor_schedule 
                       WHERE doctor_id = ? 
                       AND day_of_week = ? 
                       AND start_time <= ? 
                       AND end_time >= ?";
            
            $checkStmt = mysqli_prepare($this->link, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "isss", $doctor_id, $day_of_week, $time_slot, $time_slot);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($result) === 0) {
                // Log the details of why no slot was found
                error_log("No matching schedule found for doctor_id: $doctor_id, day: $day_of_week, time: $time_slot");
                
                // Check if there are any schedules for this doctor and day (for debugging)
                $debugSql = "SELECT * FROM doctor_schedule WHERE doctor_id = ? AND day_of_week = ?";
                $debugStmt = mysqli_prepare($this->link, $debugSql);
                mysqli_stmt_bind_param($debugStmt, "is", $doctor_id, $day_of_week);
                mysqli_stmt_execute($debugStmt);
                $debugResult = mysqli_stmt_get_result($debugStmt);
                
                if (mysqli_num_rows($debugResult) > 0) {
                    $slots = [];
                    while ($row = mysqli_fetch_assoc($debugResult)) {
                        $slots[] = "ID: {$row['id']}, Start: {$row['start_time']}, End: {$row['end_time']}, Available: {$row['availability']}";
                    }
                    error_log("Available schedules for this doctor/day: " . implode(" | ", $slots));
                } else {
                    error_log("No schedules found for doctor_id: $doctor_id on $day_of_week");
                }
                mysqli_stmt_close($debugStmt);
                
                throw new Exception('This time slot is no longer available or not scheduled');
            }
            
            // Check if the slot is available
            $slot = mysqli_fetch_assoc($result);
            if ($slot['availability'] !== 'Available') {
                throw new Exception('This time slot is already booked');
            }
            mysqli_stmt_close($checkStmt);
            
            // 2. Create the appointment
            $apptSql = "INSERT INTO appointment 
                      (patient_id, doctor_id, appointment_time, status, day_of_week, location, notes) 
                      VALUES (?, ?, ?, 'Scheduled', ?, ?, ?)";
            $apptStmt = mysqli_prepare($this->link, $apptSql);
            mysqli_stmt_bind_param($apptStmt, "iissss", 
                $patient_id, 
                $doctor_id, 
                $time_slot, 
                $day_of_week,
                $location,
                $notes
            );
            
            if (!mysqli_stmt_execute($apptStmt)) {
                throw new Exception('Failed to create appointment: ' . mysqli_error($this->link));
            }
            $appointment_id = mysqli_insert_id($this->link);
            mysqli_stmt_close($apptStmt);
            
            // 3. Update the slot availability
            $updateSql = "UPDATE doctor_schedule 
                         SET availability = 'Unavailable' 
                         WHERE doctor_id = ? 
                         AND day_of_week = ? 
                         AND start_time <= ? 
                         AND end_time >= ?";
            
            $updateStmt = mysqli_prepare($this->link, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "isss", $doctor_id, $day_of_week, $time_slot, $time_slot);
            
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception('Failed to update schedule');
            }
            
            // Commit transaction
            mysqli_commit($this->link);
            
            // Use constant phone number for testing
            $testPhoneNumber = "+20108146088";
            error_log("[BookingController][DEBUG] Using test phone number: {$testPhoneNumber}");
            
            try {
                error_log("[BookingController][DEBUG] Sending test notification to: {$testPhoneNumber}");
                
                $notificationService = new NotificationService();
                $notificationResult = $notificationService->sendAppointmentConfirmation(
                    $testPhoneNumber,
                    $appointment_date,
                    $time_slot
                );
                
                if ($notificationResult === false) {
                    error_log("[BookingController][ERROR] Failed to send test notification to: {$testPhoneNumber}");
                } else {
                    error_log("[BookingController][SUCCESS] Test notification sent to: {$testPhoneNumber}");
                }
            } catch (Exception $e) {
                error_log('[BookingController][EXCEPTION] Error in notification: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            
            echo json_encode([
                'success' => true, 
                'appointment_id' => $appointment_id,
                'message' => 'Appointment booked successfully! You will receive a confirmation shortly.'
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($this->link)) {
                mysqli_rollback($this->link);
            }
            
            error_log('Booking error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createFreeAppointment() {
        header('Content-Type: application/json');
        
        try {
            // Validate required fields
            $required = ['doctor_id', 'appointment_time', 'day_of_week'];
            $missing = [];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                throw new Exception('Missing required fields: ' . implode(', ', $missing));
            }
            
            error_log('Received POST data: ' . print_r($_POST, true));
            
            // Prepare appointment data according to database schema
            $appointmentData = [
                'doctor_id' => $_POST['doctor_id'],
                'patient_id' => $_SESSION['user_id'],
                'day_of_week' => $_POST['day_of_week'],
                'appointment_time' => $_POST['appointment_time'],
                'location' => $_POST['location'] ?? 'Clinic',
                'notes' => $_POST['notes'] ?? 'Free appointment booking',
                'status' => 'Scheduled',
                'visit_type' => 'Consultation' // Default visit type
            ];
            
            // Start transaction
            mysqli_begin_transaction($this->link);
            
            // 1. Check if the slot is still available
            $checkSql = "SELECT id, availability FROM doctor_schedule 
                       WHERE doctor_id = ? 
                       AND day_of_week = ? 
                       AND start_time <= ? 
                       AND end_time >= ?";
            
            error_log('Checking slot availability with SQL: ' . $checkSql);
            error_log('With params: ' . print_r([
                'doctor_id' => $appointmentData['doctor_id'],
                'day_of_week' => $appointmentData['day_of_week'],
                'start_time' => $appointmentData['appointment_time'],
                'end_time' => $appointmentData['appointment_time']
            ], true));
            
            $checkStmt = mysqli_prepare($this->link, $checkSql);
            if (!$checkStmt) {
                throw new Exception('Failed to prepare statement: ' . mysqli_error($this->link));
            }
            
            $bound = mysqli_stmt_bind_param($checkStmt, "isss", 
                $appointmentData['doctor_id'], 
                $appointmentData['day_of_week'], 
                $appointmentData['appointment_time'], 
                $appointmentData['appointment_time']
            );
            
            if (!$bound) {
                throw new Exception('Failed to bind parameters: ' . mysqli_error($this->link));
            }
            
            $executed = mysqli_stmt_execute($checkStmt);
            if (!$executed) {
                throw new Exception('Failed to execute query: ' . mysqli_stmt_error($checkStmt));
            }
            
            $result = mysqli_stmt_get_result($checkStmt);
            if (!$result) {
                throw new Exception('Failed to get result set: ' . mysqli_error($this->link));
            }
            
            if (mysqli_num_rows($result) === 0) {
                throw new Exception('This time slot is no longer available or not scheduled');
            }
            
            // Check if the slot is available
            $slot = mysqli_fetch_assoc($result);
            if ($slot['availability'] !== 'Available') {
                throw new Exception('This time slot is already booked');
            }
            mysqli_stmt_close($checkStmt);
            
            // 2. Create the appointment
            $apptSql = "INSERT INTO appointment 
                      (patient_id, doctor_id, day_of_week, appointment_time, 
                      location, notes, status, visit_type) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                      
            $apptStmt = mysqli_prepare($this->link, $apptSql);
            mysqli_stmt_bind_param($apptStmt, "iissssss", 
                $appointmentData['patient_id'],
                $appointmentData['doctor_id'],
                $appointmentData['day_of_week'],
                $appointmentData['appointment_time'],
                $appointmentData['location'],
                $appointmentData['notes'],
                $appointmentData['status'],
                $appointmentData['visit_type']
            );
            
            error_log('Executing SQL: ' . $apptSql);
            error_log('With params: ' . print_r($appointmentData, true));
            
            if (!mysqli_stmt_execute($apptStmt)) {
                throw new Exception('Failed to create appointment: ' . mysqli_error($this->link));
            }
            $appointment_id = mysqli_insert_id($this->link);
            mysqli_stmt_close($apptStmt);
            
            // 3. Update the slot availability
            $updateSql = "UPDATE doctor_schedule 
                         SET availability = 'Unavailable' 
                         WHERE doctor_id = ? 
                         AND day_of_week = ? 
                         AND start_time <= ? 
                         AND end_time >= ?";
            
            $updateStmt = mysqli_prepare($this->link, $updateSql);
            if (!$updateStmt) {
                throw new Exception('Failed to prepare update statement: ' . mysqli_error($this->link));
            }
            
            $bound = mysqli_stmt_bind_param($updateStmt, "isss", 
                $appointmentData['doctor_id'], 
                $appointmentData['day_of_week'], 
                $appointmentData['appointment_time'], 
                $appointmentData['appointment_time']
            );
            
            if (!$bound) {
                mysqli_stmt_close($updateStmt);
                throw new Exception('Failed to bind update parameters: ' . mysqli_error($this->link));
            }
            
            if (!mysqli_stmt_execute($updateStmt)) {
                $error = mysqli_stmt_error($updateStmt);
                mysqli_stmt_close($updateStmt);
                throw new Exception('Failed to update schedule: ' . $error);
            }
            
            // Close the statement
            mysqli_stmt_close($updateStmt);
            
            // Commit the transaction
            mysqli_commit($this->link);
            
            // Generate booking reference
            $booking_reference = 'FREE-' . strtoupper(uniqid());
            
            // Use constant phone number for testing
            $testPhoneNumber = "+20108146088";
            error_log("[BookingController][DEBUG] [Free Appointment] Using test phone number: {$testPhoneNumber}");
            
            try {
                error_log("[BookingController][DEBUG] Sending free appointment test notification to: {$testPhoneNumber}");
                
                $notificationService = new NotificationService();
                $notificationResult = $notificationService->sendAppointmentConfirmation(
                    $testPhoneNumber,
                    $appointmentData['day_of_week'], // Using day_of_week as date
                    $appointmentData['appointment_time']
                );
                
                if ($notificationResult === false) {
                    error_log("[BookingController][ERROR] Failed to send free appointment test notification to: {$testPhoneNumber}");
                } else {
                    error_log("[BookingController][SUCCESS] Free appointment test notification sent to: {$testPhoneNumber}");
                }
            } catch (Exception $e) {
                error_log('[BookingController][EXCEPTION] Error in free appointment notification: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            
            echo json_encode([
                'success' => true,
                'booking_reference' => $booking_reference,
                'appointment_id' => $appointment_id,
                'message' => 'Free appointment booked successfully! You will receive a confirmation shortly.'
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if (isset($this->link)) {
                error_log('Rolling back transaction due to error: ' . $e->getMessage());
                mysqli_rollback($this->link);
            } else {
                error_log('Could not rollback - database connection not available');
            }
            
            $errorMessage = 'An error occurred while processing your request. Please try again.';
            error_log('Free appointment error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            // Ensure we're sending valid JSON
            if (!headers_sent()) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => $errorMessage,
                    'debug' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
                ]);
            } else {
                // If headers were already sent, log the error
                error_log('Headers already sent when trying to send error response');
                // Try to send a simple error message
                echo json_encode(['success' => false, 'error' => $errorMessage]);
            }
            
            // Make sure no other output is sent
            exit;
        }
    }
    
    private function initiatePayment() {
        header('Content-Type: application/json');
        
        try {
            // Validate required fields
            $required = ['doctor_id', 'appointment_date', 'appointment_time', 'day_of_week', 'amount'];
            $missing = [];
            $data = [];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
            
            if (!empty($missing)) {
                throw new Exception('Missing required fields: ' . implode(', ', $missing));
            }
            
            // 1. Authenticate with Paymob API
            $authResponse = $this->paymobAuthenticate();
            if (!$authResponse['success']) {
                throw new Exception('Payment gateway authentication failed');
            }
            
            $authToken = $authResponse['token'];
            
            // 2. Create order
            $orderId = uniqid('ORD-');
            $amountCents = $data['amount'] * 100; // Convert to cents
            
            $orderData = [
                'auth_token' => $authToken,
                'delivery_needed' => false,
                'amount_cents' => $amountCents,
                'currency' => 'EGP',
                'items' => [
                    [
                        'name' => 'Medical Appointment',
                        'amount_cents' => $amountCents,
                        'description' => 'Appointment with doctor',
                        'quantity' => 1
                    ]
                ]
            ];
            
            $orderResponse = $this->paymobRequest('ecommerce/orders', $orderData);
            
            if (!isset($orderResponse['id'])) {
                throw new Exception('Failed to create payment order');
            }
            
            // 3. Get payment key
            $paymentKeyData = [
                'auth_token' => $authToken,
                'amount_cents' => $amountCents,
                'expiration' => 3600, // 1 hour
                'order_id' => $orderResponse['id'],
                'billing_data' => [
                    'apartment' => 'NA',
                    'email' => $_SESSION['email'] ?? 'patient@example.com',
                    'floor' => 'NA',
                    'first_name' => $_SESSION['name'] ?? 'Patient',
                    'street' => 'NA',
                    'building' => 'NA',
                    'phone_number' => $_SESSION['phone'] ?? '+201234567890',
                    'shipping_method' => 'NA',
                    'postal_code' => 'NA',
                    'city' => 'NA',
                    'country' => 'EG',
                    'last_name' => 'NA',
                    'state' => 'NA'
                ],
                'currency' => 'EGP',
                'integration_id' => $this->paymobIntegrationId,
                'lock_order_when_paid' => 'true'
            ];
            
            $paymentKeyResponse = $this->paymobRequest('acceptance/payment_keys', $paymentKeyData);
            
            if (!isset($paymentKeyResponse['token'])) {
                throw new Exception('Failed to generate payment key');
            }
            
            // 4. Generate booking reference
            $bookingReference = 'BOOK-' . strtoupper(uniqid());
            
            // Return payment token and booking reference
            echo json_encode([
                'success' => true,
                'payment_token' => $paymentKeyResponse['token'],
                'booking_reference' => $bookingReference,
                'iframe_id' => $this->paymobIframeId
            ]);
            
        } catch (Exception $e) {
            error_log('Payment initiation error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function paymobAuthenticate() {
        try {
            $data = [
                'api_key' => $this->paymobApiKey
            ];
            
            error_log("Attempting to authenticate with Paymob API");
            $response = $this->paymobRequest('auth/tokens', $data, 'POST', false);
            
            error_log("Paymob auth response: " . print_r($response, true));
            
            if (isset($response['token'])) {
                return ['success' => true, 'token' => $response['token']];
            }
            
            $errorMsg = $response['detail'] ?? 'Authentication failed. No token received.';
            error_log("Paymob authentication failed: " . $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
            
        } catch (Exception $e) {
            error_log("Exception in paymobAuthenticate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function paymobRequest($endpoint, $data, $method = 'POST', $includeAuth = true) {
        $url = 'https://accept.paymob.com/api/' . ltrim($endpoint, '/');
        
        // Log the request
        error_log("Paymob API Request to: " . $url);
        error_log("Request Data: " . print_r($data, true));
        
        $ch = curl_init($url);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false, // Only for testing, remove in production
            CURLOPT_SSL_VERIFYHOST => 0,     // Only for testing, remove in production
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true
        ]);
        
        if ($method === 'POST') {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            error_log("Sending JSON: " . $jsonData);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        // Get cURL info for debugging
        $curlInfo = curl_getinfo($ch);
        
        // Log cURL info and response
        error_log("cURL Info: " . print_r($curlInfo, true));
        error_log("cURL Error: " . $curlError);
        error_log("cURL Error No: " . $curlErrno);
        error_log("HTTP Status: " . $httpCode);
        error_log("Response: " . $response);
        
        if ($curlErrno) {
            $errorMsg = 'cURL error #' . $curlErrno . ': ' . $curlError;
            error_log($errorMsg);
            curl_close($ch);
            throw new Exception($errorMsg);
        }
        
        curl_close($ch);
        
        if (empty($response)) {
            throw new Exception('Empty response from payment gateway');
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from payment gateway: ' . json_last_error_msg() . ' - ' . $response);
        }
        
        if ($httpCode >= 400) {
            $errorMsg = $decodedResponse['detail'] ?? 'Payment gateway error';
            if (is_array($errorMsg)) {
                $errorMsg = implode(', ', $errorMsg);
            }
            throw new Exception('HTTP ' . $httpCode . ': ' . $errorMsg);
        }
        
        return $decodedResponse;
    }
    
    public function __destruct() {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}

// Handle the request
if (isset($_REQUEST['action'])) {
    $controller = new BookingController();
    $controller->handleRequest();
}
