<?php
require('connection.php');
require_once 'vendor/autoload.php'; // If using Composer
use Twilio\Rest\Client;

function sendStockAlert($to_number, $product_names) {
    // Your Twilio Account SID and Auth Token
    $account_sid = 'AC62053f58b59fb05c6c45baae390f51a3';
    $auth_token = '5793fbce6e93204c84d6e0403688661e';
    $twilio_number = '+19162998178'; // Replace with your Twilio phone number


    try {
        $client = new Client($account_sid, $auth_token);
        
        $message = "Low Stock Alert!\n\nThe following products are out of stock:\n";
        foreach ($product_names as $product) {
            $message .= "- " . $product . "\n";
        }
        $message .= "\nPlease update your inventory.";

        $client->messages->create(
            $to_number,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return false;
    }
}
?>