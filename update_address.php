<?php
session_start();
require('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['lid'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $landmark = $_POST['landmark'];
    $pincode = $_POST['pincode'];
    $roadname = $_POST['roadname'];
    $district = $_POST['district'];

    // Validate input (you can add more validation as needed)
    if (empty($phone) || empty($address) || empty($landmark) || empty($pincode) || empty($roadname) || empty($district)) {
        echo "All fields are required.";
        exit();
    }

    // Prepare the update query
    $update_query = "UPDATE registration SET phone = ?, address = ?, landmark = ?, pincode = ?, roadname = ?, district = ? WHERE lid = ?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("ssssssi", $phone, $address, $landmark, $pincode, $roadname, $district, $user_id);

    if ($stmt->execute()) {
        // Redirect back to the order confirmation page with a success message
        header("Location: order_confirmation.php?id=" . $_GET['id'] . "&success=1");
    } else {
        // Handle error
        echo "Error updating address: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
} else {
    echo "Invalid request.";
}
?>