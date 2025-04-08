<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
$lid = isset($_GET['lid']) ? intval($_GET['lid']) : 0;

if (!$lid) {
    echo json_encode(['error' => 'Invalid delivery boy ID']);
    exit();
}

$query = "SELECT * FROM chatmessage 
          WHERE lid = ? AND id > ? 
          ORDER BY send_at ASC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $lid, $last_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Update read status for messages received by delivery boy
    if ($row['sender_id'] != $lid && !$row['is_read']) {
        $update_query = "UPDATE chatmessage SET is_read = 1 WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'i', $row['id']);
        mysqli_stmt_execute($update_stmt);
    }

    $messages[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'message' => $row['message'],
        'message_type' => $row['message_type'],
        'image_path' => $row['image_path'],
        'voice_path' => $row['voice_path'],
        'is_read' => $row['is_read'],
        'send_at' => $row['send_at']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>
