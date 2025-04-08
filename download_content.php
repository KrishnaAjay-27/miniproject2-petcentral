<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid']) || !isset($_GET['id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$video_id = (int)$_GET['id'];

$query = "SELECT content_file FROM doctor_pet_videos WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $video_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    if ($row['content_file'] && file_exists($row['content_file'])) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . basename($row['content_file']) . '"');
        readfile($row['content_file']);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo 'Content file not found';
    }
} else {
    header('HTTP/1.1 404 Not Found');
    echo 'Video not found';
}