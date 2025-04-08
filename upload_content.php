<?php
session_start();
require('connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

if (!isset($_FILES['content_file']) || !isset($_POST['video_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$video_id = (int)$_POST['video_id'];
$upload_dir = 'uploads/content/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_name = 'content_' . uniqid() . '_' . basename($_FILES['content_file']['name']);
$file_path = $upload_dir . $file_name;

if (move_uploaded_file($_FILES['content_file']['tmp_name'], $file_path)) {
    $query = "UPDATE doctor_pet_videos SET content_file = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "si", $file_path, $video_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'File upload failed']);
}