<?php
session_start();
require('connection.php');

if (isset($_SESSION['uid'])) {
    $user_id = $_SESSION['uid'];

    // Update notifications to mark them as read
    $update_query = "UPDATE notifications SET is_read = 1 WHERE lid = ? AND is_read = 0";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notifications.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
}
?>
