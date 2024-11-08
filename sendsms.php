<?php
require_once 'vendor/autoload.php'; // If using Composer
use Twilio\Rest\Client;

require('connection.php');

function sendWhatsAppMessage($to_number, $message) {
    // Your Twilio Account SID and Auth Token
    $account_sid = 'AC62053f58b59fb05c6c45baae390f51a3'; // Replace with your Twilio Account SID
    $auth_token = '5793fbce6e93204c84d6e0403688661e';// Replace with your Twilio Auth Token
    $twilio_phone_number = '+19162998178'; // Replace with your Twilio WhatsApp number

    try {
        $client = new Client($account_sid, $auth_token);
        
        $client->messages->create(
           $to_number, // WhatsApp recipient
            [
                'from' =>   $twilio_phone_number,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("WhatsApp Error: " . $e->getMessage());
        return false;
    }
}
