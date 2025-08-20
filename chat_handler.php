<?php
require_once 'Model/Patient.php';

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    session_start();
    
    if (!isset($_SESSION['patient_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $patient = new Patient();
    $data = json_decode(file_get_contents('php://input'), true);
    $response = [];

    try {
        switch ($_POST['action']) {
            case 'send_message':
                if (empty($data['appointment_id']) || empty($data['message'])) {
                    throw new Exception('Missing required fields');
                }
                
                $result = $patient->sendChatMessage(
                    $data['appointment_id'],
                    'patient',
                    $data['message']
                );
                
                if (!$result['success']) {
                    throw new Exception($result['error'] ?? 'Failed to send message');
                }
                
                $response = [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'message_id' => $result['message_id']
                ];
                break;
                
            case 'get_messages':
                if (empty($data['appointment_id'])) {
                    throw new Exception('Appointment ID is required');
                }
                
                $messages = $patient->getChatHistory($data['appointment_id']);
                $response = [
                    'success' => true,
                    'messages' => $messages
                ];
                break;
                
            case 'check_new_messages':
                if (empty($data['appointment_id']) || !isset($data['last_seen_id'])) {
                    throw new Exception('Missing required parameters');
                }
                
                $count = $patient->getUnreadMessageCount(
                    $data['appointment_id'],
                    $data['last_seen_id']
                );
                
                $response = [
                    'success' => true,
                    'has_new_messages' => $count > 0,
                    'count' => $count
                ];
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit;
}
?>
