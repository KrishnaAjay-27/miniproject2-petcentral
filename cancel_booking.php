<?php
session_start();
require('connection.php');

if(!isset($_SESSION['uid']) || !isset($_POST['booking_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userid = $_SESSION['uid'];
$booking_id = mysqli_real_escape_string($con, $_POST['booking_id']);

// Start transaction
mysqli_begin_transaction($con);

try {
    // Verify booking belongs to user and is pending
    $check_query = "SELECT * FROM vaccination_bookings 
                    WHERE booking_id = '$booking_id' 
                    AND lid = '$userid' 
                    AND booking_status = 'pending'";
    $result = mysqli_query($con, $check_query);

    if(mysqli_num_rows($result) == 0) {
        throw new Exception('Invalid booking or already cancelled');
    }

    // Update booking status
    $update_query = "UPDATE vaccination_bookings 
                    SET booking_status = 'cancelled' 
                    WHERE booking_id = '$booking_id'";
    
    if(!mysqli_query($con, $update_query)) {
        throw new Exception('Error updating booking');
    }

    // Commit transaction
    mysqli_commit($con);
    echo json_encode(['success' => true]);

} catch(Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($con);
?>