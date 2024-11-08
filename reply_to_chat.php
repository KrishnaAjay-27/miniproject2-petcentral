<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chat_id = $_POST['chat_id'];
    $reply = $_POST['reply'];
    $medicine = $_POST['medicine']; // Get the medicine input

    // Update the chat message with the doctor's reply and medicine
    $stmt = $con->prepare("UPDATE chat_message SET reply = ?, medicine = ?, reply_status = 'replied' WHERE chatid = ?");
    $stmt->bind_param("ssi", $reply, $medicine, $chat_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Reply sent to the user!'); window.location.href = 'view_chat_message.php';</script>";
}

mysqli_close($con);
?>