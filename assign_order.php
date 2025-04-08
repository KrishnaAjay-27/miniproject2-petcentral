<?php
session_start();
require('connection.php');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['order_id']) || !isset($_POST['deid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$order_id = intval($_POST['order_id']);
$deid = intval($_POST['deid']);

// Start transaction
mysqli_begin_transaction($con);

try {
    // Get the delivery boy details
    $deliveryBoyQuery = "SELECT * FROM deliveryboy WHERE deid = $deid";
    $deliveryBoyResult = mysqli_query($con, $deliveryBoyQuery);
    
    if (!$deliveryBoyResult || mysqli_num_rows($deliveryBoyResult) == 0) {
        throw new Exception("Delivery boy not found");
    }
    
    $deliveryBoy = mysqli_fetch_assoc($deliveryBoyResult);
    $lid = $deliveryBoy['lid'];
    
    // Check if the delivery boy has reached the daily limit of 10 orders
    // Important: Make sure to count only assignments with detail_id and for today
    $todayOrdersQuery = "
        SELECT COUNT(*) as day_count 
        FROM deliveryboy 
        WHERE lid = '$lid' AND detail_id IS NOT NULL AND DATE(assign_date) = CURDATE()
    ";
    $todayOrdersResult = mysqli_query($con, $todayOrdersQuery);
    $today_count = mysqli_fetch_assoc($todayOrdersResult)['day_count'];
    
    if ($today_count >= 10) {
        throw new Exception("Daily limit reached for this delivery boy");
    }
    
    // Get order details
    $orderQuery = "SELECT * FROM order_details WHERE detail_id = $order_id";
    $orderResult = mysqli_query($con, $orderQuery);
    
    if (!$orderResult || mysqli_num_rows($orderResult) == 0) {
        throw new Exception("Order not found");
    }
    
    $order = mysqli_fetch_assoc($orderResult);
    
    // Create a new row in the deliveryboy table with the same details but new order ID
    $insertQuery = "
        INSERT INTO deliveryboy (
            name, 
            email, 
            phone, 
            pincode, 
            status, 
            detail_id, 
            lid, 
            u_type, 
            doctor_code,
            assign_date
        ) VALUES (
            '" . mysqli_real_escape_string($con, $deliveryBoy['name']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['email']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['phone']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['pincode']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['status']) . "',
            $order_id,
            '" . mysqli_real_escape_string($con, $deliveryBoy['lid']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['u_type']) . "',
            '" . mysqli_real_escape_string($con, $deliveryBoy['doctor_code']) . "',
            NOW()
        )
    ";
    
    $insertResult = mysqli_query($con, $insertQuery);
    
    if (!$insertResult) {
        throw new Exception("Failed to assign order to delivery boy: " . mysqli_error($con));
    }
    
    // Update order status to "Processing"
    $updateOrderQuery = "
        UPDATE order_details 
        SET order_status = 2 
        WHERE detail_id = $order_id
    ";
    
    $updateOrderResult = mysqli_query($con, $updateOrderQuery);
    
    if (!$updateOrderResult) {
        throw new Exception("Failed to update order status: " . mysqli_error($con));
    }
    
    // Get updated count to return to client
    $updatedCountQuery = "
        SELECT COUNT(*) as day_count 
        FROM deliveryboy 
        WHERE lid = '$lid' AND detail_id IS NOT NULL AND DATE(assign_date) = CURDATE()
    ";
    $updatedCountResult = mysqli_query($con, $updatedCountQuery);
    $updated_count = mysqli_fetch_assoc($updatedCountResult)['day_count'];
    
    // Commit transaction
    mysqli_commit($con);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Order assigned successfully',
        'today_count' => $updated_count,
        'limit_reached' => ($updated_count >= 10)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($con);
    
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>
