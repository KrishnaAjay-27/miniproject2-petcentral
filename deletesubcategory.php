<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}


if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['subid'])) {
    $subid = intval($_GET['subid']);
    $deleteQuery = "DELETE FROM subcategory WHERE subid = $subid";
    
    if (mysqli_query($con, $deleteQuery)) {
        header('Location: viewsubcategory.php');
        exit();
    } else {
        die("Error: " . mysqli_error($con));
    }
}

mysqli_close($con);
?>
