<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    echo "Please log in to add items to your wishlist.";
    exit();
}

if (isset($_POST['product_id'])) {
    $user_id = $_SESSION['uid'];
    $product_id = $_POST['product_id'];

    $check_query = "SELECT * FROM tbl_wishlist WHERE product_id = ? AND lid = ?";
    $check_stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $product_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "This item is already in your wishlist.";
    } else {
        $query = "INSERT INTO tbl_wishlist (product_id, lid, PostedDate) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $product_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "Product added to wishlist successfully!";
        } else {
            echo "Error adding product to wishlist.";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($check_stmt);
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>