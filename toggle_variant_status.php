<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    exit(json_encode(['success' => false, 'message' => 'Connection failed: ' . mysqli_connect_error()]));
}

if (isset($_POST['variant_id']) && isset($_POST['current_status'])) {
    $variant_id = intval($_POST['variant_id']);
    $new_status = ($_POST['current_status'] == 0) ? 1 : 0;
    
    $updateQuery = "UPDATE product_variants SET status = $new_status WHERE variant_id = $variant_id";
    
    if (mysqli_query($con, $updateQuery)) {
        echo json_encode(['success' => true, 'message' => 'Variant status updated successfully', 'new_status' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating variant status: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

mysqli_close($con);
?>
