<?php
include("header.php");
require('connection.php');

// Fetch user details from the registration table
$user_id = $_SESSION['uid']; // Assuming the user ID is stored in the session
$user_query = "SELECT name, address, landmark, pincode, roadname, district, phone, email FROM registration WHERE lid = ?";
$user_stmt = $con->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_details = $user_result->fetch_assoc();

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Fetch order details
    $order_query = "SELECT * FROM tbl_order WHERE order_id = ?";
    $order_stmt = $con->prepare($order_query);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    // Check if the order exists
    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
    } else {
        echo "Order not found.";
        exit();
    }

    // Fetch order items including size
    $order_items_query = "
        SELECT 
            order_details.*, 
            tbl_cart.size 
        FROM 
            order_details 
        JOIN 
            tbl_cart ON order_details.product_id = tbl_cart.product_id 
        WHERE 
            order_details.order_id = ?";
    $order_items_stmt = $con->prepare($order_items_query);
    $order_items_stmt->bind_param("i", $order_id);
    $order_items_stmt->execute();
    $order_items_result = $order_items_stmt->get_result();

    // First check if order details already exist
    $check_order_query = "SELECT * FROM order_details WHERE order_id = ?";
    $check_stmt = $con->prepare($check_order_query);
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // If order details don't exist, insert them from cart
        $cart_query = "SELECT * FROM tbl_cart WHERE lid = ?";
        $cart_stmt = $con->prepare($cart_query);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        while ($cart_item = $cart_result->fetch_assoc()) {
            $insert_order_detail = "INSERT INTO order_details 
                (order_id, product_id, product_name, quantity, price, order_status) 
                VALUES (
                    ?, ?, ?, ?, ?, 0)";
            $detail_stmt = $con->prepare($insert_order_detail);
            $detail_stmt->bind_param("iisid", 
                $order_id,
                $cart_item['product_id'],
                $cart_item['product_name'],
                $cart_item['quantity'],
                $cart_item['price']
            );
            $detail_stmt->execute();
        }
    }
} else {
    echo "No order ID provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .order-confirmation {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .user-details {
            margin-top: 20px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="order-confirmation">
        <h2>Order Confirmation</h2>
        <p>Thank you for your order, <strong><?php echo htmlspecialchars($user_details['name']); ?></strong>!</p>
        <p>Your order ID is: <strong><?php echo $order['order_id']; ?></strong></p>
        <p>Total Amount: <strong>Rs<?php echo $order['total']; ?></strong></p>
        <p>Date: <strong><?php echo $order['date']; ?></strong></p>

        <h3>User Details:</h3>
        <div class="user-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user_details['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_details['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user_details['address']); ?></p>
            <p><strong>Landmark:</strong> <?php echo htmlspecialchars($user_details['landmark']); ?></p>
            <p><strong>Pincode:</strong> <?php echo htmlspecialchars($user_details['pincode']); ?></p>
            <p><strong>Road Name:</strong> <?php echo htmlspecialchars($user_details['roadname']); ?></p>
            <p><strong>District:</strong> <?php echo htmlspecialchars($user_details['district']); ?></p>
        </div>
<!-- 
        <button class="btn" id="updateAddressBtn">Update Address</button> -->

        <h3>Order Details:</h3>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
            <?php while ($item = mysqli_fetch_array($order_items_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo 'Rs.' . $item['price']; ?></td>
                    <td><?php echo 'Rs.' . ($item['price'] * $item['quantity']); ?></td>
                </tr>
            <?php } ?>
        </table>
        <form id="paymentForm" method="POST">
            <input type="hidden" name="amount" value="<?php echo $order['total']; ?>">
            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
            <input type="hidden" name="lid" value="<?php echo $_SESSION['uid']; ?>">
            <button type="button" class="btn" onclick="confirmPayment()">Pay Now</button>
        </form>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
          function confirmPayment() {
            if (confirm("Are you sure you want to proceed with the payment?")) {
                var amount = $('input[name="amount"]').val();
                var order_id = $('input[name="order_id"]').val();
                var lid = $('input[name="lid"]').val(); 

                var options = {
                    "key": "rzp_test_ZpwHCsO9Dbl1DQ",
                    "amount": amount * 100,
                    "currency": "INR",
                    "name": "Petcentral",
                    "description": "Order ID: " + order_id,
                    "handler": function (response) {
                        // First, update order details
                        $.ajax({
                            url: 'process_payment.php',
                            type: 'POST',
                            data: {
                                order_id: order_id,
                                amount: amount,
                                payment_id: response.razorpay_payment_id,
                                lid: lid
                            },
                            success: function (response) {
                                try {
                                    console.log("Raw response:", response); // Debug log
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response);
                                    }
                                    if (response.success) {
                                        alert("Payment successful! Your order has been processed.");
                                        window.location.href = 'userindex.php';
                                    } else {
                                        alert("Payment failed: " + (response.error || "Unknown error"));
                                        console.error("Payment error:", response.error);
                                    }
                                } catch (e) {
                                    console.error("Response parsing error:", e);
                                    console.log("Raw response that caused error:", response);
                                    // If payment was actually successful but had response issues
                                    if (response.includes("success") || response.includes("true")) {
                                        alert("Payment processed successfully!");
                                        window.location.href = 'userindex.php';
                                    } else {
                                        alert("Payment status unclear. Please check your order history or contact support.");
                                    }
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error:", {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                // If payment was successful but had communication issues
                                alert("Payment completed. Please check your order history.");
                                window.location.href = 'userindex.php';
                            }
                        });
                    },
                    "prefill": {
                        "name": "<?php echo htmlspecialchars($user_details['name']); ?>",
                        "email": "<?php echo htmlspecialchars($user_details['email']); ?>",
                        "contact": "<?php echo htmlspecialchars($user_details['phone']); ?>"
                    },
                    "theme": {
                        "color": "#F37254"
                    }
                };

                var rzp1 = new Razorpay(options);
                rzp1.open();
            }
          }

          // Modal functionality
          var modal = document.getElementById("updateAddressModal");
          var btn = document.getElementById("updateAddressBtn");
          var span = document.getElementsByClassName("close")[0];

          btn.onclick = function() {
              modal.style.display = "block";
          }

          span.onclick = function() {
              modal.style.display = "none";
          }

          window.onclick = function(event) {
              if (event.target == modal) {
                  modal.style.display = "none";
              }
          }
        </script>

        <!-- Modal for updating address -->
        <div id="updateAddressModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Update Address</h3>
                <form method="POST" action="update_address.php">
                    <input type="hidden" name="lid" value="<?php echo $user_id; ?>">
                    <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo $row['phone'];?>" required pattern="[6-9]\d{9}" maxlength="10">

                <label>Address</label>
                <input type="text" name="address" value="<?php echo $row['address'];?>" required>

                <label>Landmark</label>
                <input type="text" name="landmark" value="<?php echo $row['landmark'];?>" required>

                <label>Road Name</label>
                <input type="text" name="roadname" value="<?php echo $row['roadname'];?>" required>

                <label>District</label>
<select name="district" required>
    <option value="" disabled>Select District</option>
    <option value="Thiruvananthapuram" <?php if ($row['district'] == "Thiruvananthapuram") echo "selected"; ?>>Thiruvananthapuram</option>
    <option value="Kollam" <?php if ($row['district'] == "Kollam") echo "selected"; ?>>Kollam</option>
    <option value="Pathanamthitta" <?php if ($row['district'] == "Pathanamthitta") echo "selected"; ?>>Pathanamthitta</option>
    <option value="Alappuzha" <?php if ($row['district'] == "Alappuzha") echo "selected"; ?>>Alappuzha</option>
    <option value="Kottayam" <?php if ($row['district'] == "Kottayam") echo "selected"; ?>>Kottayam</option>
    <option value="Idukki" <?php if ($row['district'] == "Idukki") echo "selected"; ?>>Idukki</option>
    <option value="Ernakulam" <?php if ($row['district'] == "Ernakulam") echo "selected"; ?>>Ernakulam</option>
    <option value="Thrissur" <?php if ($row['district'] == "Thrissur") echo "selected"; ?>>Thrissur</option>
    <option value="Palakkad" <?php if ($row['district'] == "Palakkad") echo "selected"; ?>>Palakkad</option>
    <option value="Malappuram" <?php if ($row['district'] == "Malappuram") echo "selected"; ?>>Malappuram</option>
    <option value="Kozhikode" <?php if ($row['district'] == "Kozhikode") echo "selected"; ?>>Kozhikode</option>
    <option value="Wayanad" <?php if ($row['district'] == "Wayanad") echo "selected"; ?>>Wayanad</option>
    <option value="Kannur" <?php if ($row['district'] == "Kannur") echo "selected"; ?>>Kannur</option>
    <option value="Kasaragod" <?php if ($row['district'] == "Kasaragod") echo "selected"; ?>>Kasaragod</option>
</select>

                    <input type="text" name="district" value="<?php echo htmlspecialchars($user_details['district']); ?>" required>
                    <button type="submit" class="btn">Update Address</button>
                </form>
            </div>
        </div>

        <a href="userindex.php">Continue Shopping</a>
    </div>
</body>
</html>
