<?php
session_start();
require('connection.php');

// Check if delivery boy is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $detail_id = mysqli_real_escape_string($con, $_POST['detail_id']);

    // Update the order status to delivered (3)
    $updateOrderQuery = "
        UPDATE order_details 
        SET order_status = 3 
        WHERE detail_id = '$detail_id'
    ";
    mysqli_query($con, $updateOrderQuery);

    // Update the delivery boy status to delivered (2)
    $updateDeliveryBoyQuery = "
        UPDATE deliveryboy 
        SET status = 2 
        WHERE lid = '$deid'
    ";
    mysqli_query($con, $updateDeliveryBoyQuery);

    echo "Order and delivery boy status updated successfully!";
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>