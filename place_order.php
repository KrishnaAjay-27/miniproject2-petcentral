<?php
session_start(); // Start the session
require('connection.php'); // Include your database connection

if (isset($_POST['checkout'])) {
    if (isset($_SESSION['uid'])) {
        $userid = $_SESSION['uid']; // This is the lid
        $cart_total = $_POST['total'];

        // Check if the total is less than ₹100,000
        if ($cart_total < 100000) {
            // Insert order into tbl_order
            $sql = "INSERT INTO tbl_order(lid, total, date) VALUES('$userid', '$cart_total', NOW())";
            $res1 = mysqli_query($con, $sql);

            if ($res1) {
                $order_id = mysqli_insert_id($con);

                // Insert each item in the cart into the order details
                $order_items_sql = "
                    SELECT 
                        tbl_cart.cart_id,  -- Fetch cart_id
                        tbl_cart.product_id, 
                        tbl_cart.petid, 
                        tbl_cart.quantity, 
                        tbl_cart.size AS variant_id,  -- Use size as the variant ID
                        product_dog.name AS product_name, 
                        product_dog.image1 AS product_image, 
                        product_variants.price AS price,  -- Fetch price from product_variants
                        productpet.product_name AS pet_product_name,  -- Fetch pet product name
                        productpet.price AS pet_price  -- Fetch price from productpet
                    FROM 
                        tbl_cart 
                    LEFT JOIN 
                        product_dog ON tbl_cart.product_id = product_dog.product_id 
                    LEFT JOIN 
                        product_variants ON tbl_cart.size = product_variants.variant_id  -- Join with product_variants using size
                    LEFT JOIN 
                        productpet ON tbl_cart.petid = productpet.petid 
                    WHERE 
                        tbl_cart.lid = '$userid'
                ";
                $order_items_res = mysqli_query($con, $order_items_sql);
                while ($item = mysqli_fetch_array($order_items_res)) {
                    $cart_id = $item['cart_id'];  // Get cart_id
                    $product_id = $item['product_id'];
                    $petid = $item['petid'];
                    $quantity = $item['quantity'];
                    $product_name = $item['product_name'] ?: $item['pet_product_name']; // Use pet product name if dog product name is not set
                    $image = $item['image1'] ?: $item['product_image']; // Use appropriate image
                    $price = $item['price'] ?: $item['pet_price']; // Use pet price if dog price is not set
                
                    // Insert into order details including lid
                    $insert_order_details_sql = "
                        INSERT INTO order_details(order_id, cart_id, product_id, petid, quantity, product_name, image, price, lid) 
                        VALUES('$order_id', '$cart_id', '$product_id', '$petid', '$quantity', '$product_name', '$image', '$price', '$userid')
                    ";
                    mysqli_query($con, $insert_order_details_sql);
                }

                // Redirect to order confirmation page
                header("Location: order_confirmation.php?id=$order_id");
                exit(); // Ensure no further code is executed
            } else {
                echo "<script>alert('Failed to place the order. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('The total amount must be less than ₹100,000 to place an order.');</script>";
        }
    } else {
        header("Location: login.php");
        exit();
    }
} else {
    // If accessed directly without POST data
    header("Location: mycart.php");
    exit();
}
?>
