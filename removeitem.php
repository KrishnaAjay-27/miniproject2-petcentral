<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $cart_id = $_GET['id'];
    $userid = $_SESSION['uid'];

    // Remove the item from the cart
    $sql = "DELETE FROM tbl_cart WHERE cart_id = '$cart_id' AND lid = '$userid'";
    $result = mysqli_query($con, $sql);

    if ($result) {
        // Redirect back to the cart page
        header("Location: mycart.php");
    } else {
        echo "Error removing item: " . mysqli_error($con);
    }
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>
