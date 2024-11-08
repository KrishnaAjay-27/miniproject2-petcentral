<?php
include("header.php");
require('connection.php');
require('sendsms.php'); // Ensure this file contains the sendWhatsAppMessage function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $amount = $_POST['amount'];
    $payment_id = $_POST['payment_id'];
    $user_id = $_POST['lid']; // Get the user_id from the POST data
    $payment_status = 1; // Set to 1 for Completed

    // Begin transaction
    mysqli_begin_transaction($con);

    try {
        // Prepare the SQL statement to insert payment
        $insert_payment_query = "INSERT INTO payments (order_id, lid, amount, payment_status, payment_date, payment_id) VALUES (?, ?, ?, ?, NOW(), ?)";
        $insert_payment_stmt = $con->prepare($insert_payment_query);
        $insert_payment_stmt->bind_param("iiids", $order_id, $user_id, $amount, $payment_status, $payment_id);

        if (!$insert_payment_stmt->execute()) {
            throw new Exception("Payment insertion failed: " . $insert_payment_stmt->error);
        }

        // Update order status in the order_details table
        $update_order_status_query = "UPDATE order_details SET order_status = 1 WHERE order_id = ?";
        $update_order_status_stmt = $con->prepare($update_order_status_query);
        $update_order_status_stmt->bind_param("i", $order_id);

        if (!$update_order_status_stmt->execute()) {
            throw new Exception("Order status update failed: " . $update_order_status_stmt->error);
        }

        // Fetch user phone number from registration table
        $fetch_user_query = "SELECT r.phone, r.name FROM registration r WHERE r.lid = ?";
        $fetch_user_stmt = $con->prepare($fetch_user_query);
        $fetch_user_stmt->bind_param("i", $user_id);
        $fetch_user_stmt->execute();
        $fetch_user_stmt->bind_result($user_phone, $user_name);
        $fetch_user_stmt->fetch();
        $fetch_user_stmt->close();

        // Send WhatsApp message to the user
        if (!empty($user_phone)) {
            // Check if the phone number already starts with a '+' sign
            if ($user_phone[0] !== '+') {
                // Prepend the country code (e.g., +91 for India)
                $user_phone = '+91' . $user_phone; // Change +91 to the appropriate country code
            }
        
            $customer_message = "Dear {$user_name}, 
        Your payment of Rs.{$amount} for Order ID: {$order_id} has been successfully completed. 
        Thank you for shopping with PetCentral!";
            
            // Call the function to send the WhatsApp message
            if (!sendWhatsAppMessage($user_phone, $customer_message)) {
                error_log("Failed to send WhatsApp message to: $user_phone");
            }
        }

        // Fetch quantity from order_details
        $fetch_quantity_query = "SELECT quantity FROM order_details WHERE order_id = ?";
        $fetch_quantity_stmt = $con->prepare($fetch_quantity_query);
        $fetch_quantity_stmt->bind_param("i", $order_id);
        $fetch_quantity_stmt->execute();
        $fetch_quantity_stmt->bind_result($order_quantity);
        $fetch_quantity_stmt->fetch();
        $fetch_quantity_stmt->close();

        // Reduce quantity in product_dog and productpet tables based on fetched order quantity
        $reduce_quantity_query = "UPDATE product_variants SET quantity = quantity - ? WHERE variant_id IN (SELECT size FROM tbl_cart WHERE lid = ?)";
        $reduce_quantity_stmt = $con->prepare($reduce_quantity_query);
        $reduce_quantity_stmt->bind_param("ii", $order_quantity, $user_id);

        if (!$reduce_quantity_stmt->execute()) {
            throw new Exception("Quantity reduction in product_dog failed: " . $reduce_quantity_stmt->error);
        }

        $reduce_pet_quantity_query = "UPDATE productpet SET quantity = quantity - ? WHERE weight IN (SELECT size FROM tbl_cart WHERE lid = ?)";
        $reduce_pet_quantity_stmt = $con->prepare($reduce_pet_quantity_query);
        $reduce_pet_quantity_stmt->bind_param("ii", $order_quantity, $user_id);

        if (!$reduce_pet_quantity_stmt->execute()) {
            throw new Exception("Quantity reduction in productpet failed: " . $reduce_pet_quantity_stmt->error);
        }

        // Proceed to delete cart items
        $delete_cart_query = "DELETE FROM tbl_cart WHERE lid = ?";
        $delete_cart_stmt = $con->prepare($delete_cart_query);
        $delete_cart_stmt->bind_param("i", $user_id);

        if (!$delete_cart_stmt->execute()) {
            throw new Exception("Cart deletion failed: " . $delete_cart_stmt->error);
        }

        // Commit the transaction
        mysqli_commit($con);

        // Insert notification for the user
        $notification_query = "INSERT INTO notifications (lid, message) VALUES (?, ?)";
        $notification_stmt = $con->prepare($notification_query);
        $notification_message = "Your payment of $amount has been successfully completed for Order ID: $order_id.";
        $notification_stmt->bind_param("is", $user_id, $notification_message);

        if (!$notification_stmt->execute()) {
            throw new Exception("Notification insertion failed: " . $notification_stmt->error);
        }

        // Return success response
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($con);

        // Log the error
        error_log("Transaction failed: " . $e->getMessage());

        // Return error response
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}