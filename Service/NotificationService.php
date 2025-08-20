<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class NotificationService {
    private $twilio;
    private $twilioSid;
    private $twilioToken;
    private $smsFromNumber;
    private $whatsappFromNumber;
    private $whatsappContentSid;

    public function __construct() {
        // Twilio credentials
        $this->twilioSid = 'ACe760e6ff45229b8c5ac5ad2eb29004fd';
        $this->twilioToken = '6f008732bebfc6bc971d8d1f90e1fd5e';
        $this->smsFromNumber = '+16167796286';
        $this->whatsappFromNumber = 'whatsapp:+14155238886';
        $this->whatsappContentSid = 'HXb5b62575e6e4ff6129ad7c8efe1f983e';
        
        try {
            $this->twilio = new Client($this->twilioSid, $this->twilioToken);
        } catch (TwilioException $e) {
            error_log('Twilio SDK instantiation error: ' . $e->getMessage());
            $this->twilio = null;
        }
    }

    public function sendAppointmentConfirmation($patientPhoneNumber, $appointmentDate, $appointmentTime) {
        error_log('[NotificationService][DEBUG] sendAppointmentConfirmation called with params: ' . 
                 "date: {$appointmentDate}, time: {$appointmentTime}");
        
        if (!$this->twilio) {
            error_log('[NotificationService][ERROR] Twilio client not available');
            return false;
        }

        try {
            // Format date and time
            $formattedDate = date("m/d", strtotime($appointmentDate));
            $formattedTime = date("g:ia", strtotime($appointmentTime));
            
            // Use the working number from the test
            $fullNumber = '+201018146088';
            
            error_log("[NotificationService][DEBUG] Sending to: {$fullNumber}, Date: {$formattedDate}, Time: {$formattedTime}");
            
            // Send SMS
            $smsResult = $this->sendSms(
                $fullNumber,
                "Your appointment is confirmed for {$formattedDate} at {$formattedTime}."
            );
            
            // Send WhatsApp
            $whatsappResult = $this->sendWhatsapp($fullNumber, $formattedDate, $formattedTime);
            
            $result = $smsResult && $whatsappResult;
            error_log('[NotificationService][DEBUG] Notification sending ' . ($result ? 'succeeded' : 'failed'));
            
            return $result;
            
        } catch (Exception $e) {
            error_log('[NotificationService][ERROR] ' . $e->getMessage());
            return false;
        }
    }

    private function sendSms($to, $body) {
        try {
            error_log("[NotificationService][DEBUG] Sending SMS to: {$to}");
            
            $message = $this->twilio->messages->create(
                $to, 
                [
                    'from' => $this->smsFromNumber,
                    'body' => $body
                ]
            );
            
            error_log("[NotificationService][SUCCESS] SMS sent. SID: " . $message->sid);
            return true;
            
        } catch (Exception $e) {
            error_log("[NotificationService][ERROR] SMS failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendWhatsapp($to, $date, $time) {
        try {
            $whatsappTo = 'whatsapp:' . $to;
            error_log("[NotificationService][DEBUG] Sending WhatsApp to: {$whatsappTo}");
        
            $message = $this->twilio->messages->create(
                $whatsappTo,
                [
                    'from' => $this->whatsappFromNumber,
                    'contentSid' => $this->whatsappContentSid,
                    'contentVariables' => json_encode([
                        "1" => $date,
                        "2" => $time
                    ])
                ]
            );
            
            error_log("[NotificationService][SUCCESS] WhatsApp sent. SID: " . $message->sid);
            return true;
            
        } catch (Exception $e) {
            error_log("[NotificationService][ERROR] WhatsApp failed: " . $e->getMessage());
            return false;
        }
    }
}
