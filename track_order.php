<?php
require('connection.php');
include('header.php');

if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['uid']; // Get the logged-in user's ID

// Check if order ID is provided
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order details for the logged-in user
    $order_query = "
        SELECT 
            tbl_order.order_id, 
            tbl_order.total, 
            tbl_order.date, 
            order_details.order_status, 
            order_details.product_name, 
            order_details.quantity, 
            order_details.price,
            payments.payment_status
        FROM 
            tbl_order 
        JOIN 
            order_details ON tbl_order.order_id = order_details.order_id 
        JOIN 
            payments ON tbl_order.order_id = payments.order_id 
        WHERE 
            tbl_order.order_id = ? AND tbl_order.lid = ?";
    
    $stmt = $con->prepare($order_query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows == 0) {
        echo "<p>No order found for this ID.</p>";
        exit();
    }
    
    // Fetch all products for the order_id
    $order_details = [];
    while ($row = mysqli_fetch_assoc($order_result)) {
        $order_details[] = $row;
    }
} else {
    echo "<p>Order ID not provided.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }

        .order-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .order-details {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .order-details h3 {
            margin: 0;
            color: #333;
        }

        .order-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
    align-items: center; /* This aligns items vertically */
}

.order-item span {
    width: 33%; /* Adjusts the width of each element for better alignment */
    text-align: left;
}

.order-item span:last-child {
    text-align: right; /* Align price to the right */
}


        .order-status {
            font-weight: bold;
            color: green;
        }

        .delivery-status {
            font-weight: bold;
            color: red;
        }

        .total-amount {
            font-weight: bold;
            color: green;
        }

        /* Dot Progress Bar */
        .progress-dots {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .dot {
            height: 20px;
            width: 20px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .dot.active {
            background-color: #007bff;
        }

        .progress-line {
            flex-grow: 1;
            height: 4px;
            background-color: #bbb;
            position: relative;
        }

        .progress-line.active {
            background-color: #007bff;
        }

        .progress-step {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <h2>TRACK ORDER STATUS</h2>

        <?php
        // Display order details (only once for the entire order)
        $order = $order_details[0]; // The first row gives us the order-wide details

        // Set the active step based on order status
        $placed_active = $order['order_status'] >= 1 ? 'active' : '';
        $shipped_active = $order['order_status'] >= 2 ? 'active' : '';
        $delivered_active = $order['order_status'] == 3 ? 'active' : '';
        ?>
        <div class="order-details">
            <h3>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h3>
            <p>Date: <?php echo date('Y-m-d H:i:s', strtotime($order['date'])); ?></p>
            <p>Payment Status: 
                <span class="order-status">
                    <?php 
                    switch ($order['payment_status']) {
                        case 0:
                            echo 'Pending';
                            break;
                        case 1:
                            echo 'Completed';
                            break;
                        case 2:
                            echo 'Failed';
                            break;
                        default:
                            echo 'Unknown Status';
                    }
                    ?>
                </span>
            </p>
            <p>Delivery Status: 
                <span class="delivery-status">
                    <?php 
                    switch ($order['order_status']) {
                        case 0:
                            echo 'Pending';
                            break;
                        case 1:
                            echo 'On Progress';
                            break;
                        case 2:
                            echo 'Shipped';
                            break;
                        case 3:
                            echo 'Delivered';
                            break;
                        default:
                            echo 'Unknown Status';
                    }
                    ?>
                </span>
            </p>

            <!-- Dot Progress for order tracking -->
            <div class="progress-dots">
                <span class="dot <?php echo $placed_active; ?>"></span>
                <span class="progress-line <?php echo $placed_active; ?>"></span>
                <span class="dot <?php echo $shipped_active; ?>"></span>
                <span class="progress-line <?php echo $shipped_active; ?>"></span>
                <span class="dot <?php echo $delivered_active; ?>"></span>
            </div>

            <div class="progress-step">
                <span>Placed</span>
                <span>Shipped</span>
                <span>Delivered</span>
            </div>

            <h4>Items Purchased From PetCentral:</h4>
            <?php foreach ($order_details as $item) { ?>
            <div class="order-item">
                <span><strong style="color: #007bff;">Product Name:</strong> <?php echo htmlspecialchars($item['product_name']); ?></span>
                <span><strong style="color: #28a745;">Qty:</strong> <?php echo htmlspecialchars($item['quantity']); ?></span>
                <span><strong style="color: #dc3545;">Price:</strong>  <?php echo htmlspecialchars($item['price']); ?></span>
            </div>
            <?php } ?>
            
            <div class="order-summary">
                <div>
                    <h4>Total Amount</h4>
                    <p class="total-amount">Rs. <?php echo htmlspecialchars($order['total']); ?></p>
                </div>
            </div>
        </div>

        <a href="myorders.php" class="button">Back to My Orders</a>
    </div>
</body>
</html>
