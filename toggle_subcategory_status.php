<?php
// Start session
session_start();
require('connection.php');
// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if 'subid' and 'action' parameters are set
if (isset($_GET['subid']) && isset($_GET['action'])) {
    $subid = intval($_GET['subid']);
    $action = $_GET['action'];

    // Determine the new status based on the action
    if ($action == 'activate') {
        $newStatus = 0; // Set status to activate
    } elseif ($action == 'deactivate') {
        $newStatus = 1; // Set status to deactivate
    } else {
        die("Invalid action");
    }

    // Update the subcategory status
    $updateQuery = "UPDATE subcategory SET status = ? WHERE subid = ?";
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'ii', $newStatus, $subid);
    if (mysqli_stmt_execute($stmt)) {
        header('Location: viewsubcategory.php');
        exit();
    } else {
        echo "Error updating status: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
} else {
    die("Required parameters not set");
}

// Close the database connection
mysqli_close($con);
?>
