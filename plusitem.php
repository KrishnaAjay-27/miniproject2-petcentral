<?php
session_start();
require('connection.php');

if (isset($_GET['id']) && isset($_SESSION['uid'])) {
    $cart_id = $_GET['id'];
    $userid = $_SESSION['uid'];

    // Fetch the current quantity in the cart
    $query = "SELECT quantity, product_id, size FROM tbl_cart WHERE cart_id = '$cart_id' AND lid = '$userid'";
    $result = mysqli_query($con, $query);
    $cart_item = mysqli_fetch_assoc($result);

    if ($cart_item) {
        $current_quantity = $cart_item['quantity'];
        $product_id = $cart_item['product_id'];
        $size = $cart_item['size'];

        // Fetch the original quantity from the product_variants table
        $variant_query = "SELECT quantity FROM product_variants WHERE variant_id = '$size'";
        $variant_result = mysqli_query($con, $variant_query);
        $variant = mysqli_fetch_assoc($variant_result);

        if ($variant) {
            $original_quantity = $variant['quantity'];

            // Check if the current quantity can be increased
            if ($current_quantity < $original_quantity) {
                $new_quantity = $current_quantity + 1;

                // Update the cart with the new quantity
                $update_query = "UPDATE tbl_cart SET quantity = '$new_quantity' WHERE cart_id = '$cart_id'";
                mysqli_query($con, $update_query);
            } else {
                // Optionally, you can set a session message or alert
                $_SESSION['message'] = "You cannot exceed the available quantity of $original_quantity.";
            }
        }
    }

    // Redirect back to the cart or wherever you want
    header("Location: mycart.php");
    exit();
} else {
    // Handle the case where the user is not logged in or the ID is not set
    header("Location: login.php");
    exit();
}
?>