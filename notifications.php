<?php

require('connection.php');
include('header.php');
if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['uid']; // Get the logged-in user's ID

// Fetch notifications for the logged-in user
$notification_query = "SELECT * FROM notifications WHERE lid = ? ORDER BY created_at DESC";
$stmt = $con->prepare($notification_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notification_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }

        .notification-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .notification {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9f9f9; /* Light background for notifications */
            transition: background-color 0.3s;
        }

        .notification:hover {
            background-color: #e9ecef; /* Slightly darker on hover */
        }

        .notification-message {
            flex: 1;
            color: #333;
            font-size: 16px; /* Increased font size for better readability */
        }

        .notification-time {
            font-size: 12px;
            color: #999;
            margin-left: 10px; /* Space between message and time */
        }

        .mark-read {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
            font-weight: bold; /* Bold for emphasis */
        }

        .notification-status {
            font-size: 12px;
            color: green; /* Color for newly unread notifications */
            font-weight: bold;
        }

        .notification-read {
            color: #ff5722; /* Color for read notifications */
            font-weight: bold;
        }

        .notification:last-child {
            border-bottom: none; /* Remove border for the last notification */
        }
    </style>
</head>
<body>
    <div class="notification-container">
        <h2>Your Notifications</h2>

        <?php if (mysqli_num_rows($notification_result) > 0) { ?>
            <?php while ($notification = mysqli_fetch_array($notification_result)) { ?>
                <div class="notification">
                    <div class="notification-message">
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <span class="notification-time">
                            <?php echo date('Y-m-d H:i:s', strtotime($notification['created_at'])); ?>
                        </span>
                    </div>
                    <?php if ($notification['is_read'] == 0) { ?>
                        <div class="mark-read" onclick="markAsRead(<?php echo $notification['id']; ?>)">Mark as Read</div>
                    <?php } else { ?>
                        <span class="notification-status notification-read">Read</span>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No notifications found.</p>
        <?php } ?>
    </div>

    <script>
        function markAsRead(notificationId) {
            fetch('mark_notification_read.php?id=' + notificationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload the page to update the notification list
                    } else {
                        alert("Failed to mark notification as read.");
                    }
                });
        }
    </script>
</body>
</html>
