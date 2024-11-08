<?php
require_once 'connection.php';
require_once 'sendsms.php'; // Ensure this file contains the sendWhatsAppMessage function

function sendPaymentStatusSMS() {
    global $con;

    try {
        // Get all payments with status = 1 and join with registration to get user details
        $payment_query = "SELECT p.*, r.phone as customer_phone, r.name as customer_name 
                          FROM payments p 
                          JOIN registration r ON p.lid = r.lid 
                          WHERE p.payment_status = 1";
        $payment_stmt = $con->prepare($payment_query);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();

        // Loop through each payment and send WhatsApp message
        while ($payment_data = $payment_result->fetch_assoc()) {
            if (!empty($payment_data['customer_phone'])) {
                $customer_message = "Dear {$payment_data['customer_name']}, 
Your payment of Rs.{$payment_data['amount']} for Order #{$payment_data['order_id']} is confirmed. 
Thank you for shopping with PetCentral!";
                
                // Send WhatsApp message
                if (sendWhatsAppMessage($payment_data['customer_phone'], $customer_message)) {
                    // If the message is sent successfully, update payment_status to 2
                    $update_query = "UPDATE payments SET payment_status = 2 WHERE payment_id = ?";
                    $update_stmt = $con->prepare($update_query);
                    $update_stmt->bind_param("i", $payment_data['payment_id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    error_log("Customer WhatsApp message sent to: {$payment_data['customer_phone']}. Payment status updated to 2.");
                } else {
                    error_log("Failed to send WhatsApp message to: {$payment_data['customer_phone']}");
                }
            }
        }

        return true;

    } catch (Exception $e) {
        error_log("Error sending payment status WhatsApp message: " . $e->getMessage());
        return false;
    }
}

// Call the function to send WhatsApp messages
sendPaymentStatusSMS();
?>