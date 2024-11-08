<?php
require_once 'sendsms.php';

// Test credentials
echo "<h2>Testing Twilio Credentials</h2>";
if (testTwilioCredentials()) {
    echo "Credentials are valid<br>";
} else {
    echo "Credentials are invalid<br>";
}

// Test SMS sending
$test_number = "1234567890"; // Replace with your actual number
$test_message = "Test message from PetCentral at " . date('Y-m-d H:i:s');

echo "<h2>Testing SMS Sending</h2>";
$result = sendSMS($test_number, $test_message);
echo $result ? "SMS sent successfully" : "SMS sending failed";

