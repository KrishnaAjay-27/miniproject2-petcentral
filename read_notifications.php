<?php
    require('connection.php');
    session_start();

    if (isset($_GET['id'])) {
        $notification_id = $_GET['id'];
        
        // Update the notification to mark it as read
        $query = "UPDATE notifications SET is_read = 1 WHERE id = '$notification_id'";
        mysqli_query($con, $query);
        
        // Redirect to the notification details page or back to the dashboard
        header("Location: userindex.php");
        exit();
    }
?>
