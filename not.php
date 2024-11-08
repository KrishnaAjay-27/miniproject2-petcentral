<?php
session_start(); // Start the session
require('connection.php');

// Check if sid is set in the session
if (!isset($_SESSION['sid'])) {
    die("Supplier ID is not set in the session.");
}

$supplier_id = $_SESSION['sid']; // Get the supplier ID from the session

// Fetch notifications for the supplier
$notifications_query = "SELECT message, created_at FROM `not` WHERE lid = ? ORDER BY created_at DESC";
$notifications_stmt = $con->prepare($notifications_query);
$notifications_stmt->bind_param("i", $supplier_id);
$notifications_stmt->execute();
$notifications_result = $notifications_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Notifications</title>
</head>
<body>
    <h1>Notifications</h1>
    <ul>
        <?php while ($notification = $notifications_result->fetch_assoc()): ?>
            <li>
                <strong><?php echo htmlspecialchars($notification['created_at']); ?>:</strong>
                <?php echo htmlspecialchars($notification['message']); ?>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>