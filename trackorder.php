<?php
require('connection.php');
include('header.php');


$order_details = null; // Variable to hold order details
$error_message = ""; // Variable to hold error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the order ID from the form
    $order_id = $_POST['order_id'];

    // Validate the order ID
    if (!empty($order_id)) {
        // Fetch order details for the given order ID
        $query = "SELECT 
                      tbl_order.order_id, 
                      GROUP_CONCAT(order_details.product_name SEPARATOR ', ') AS product_names, 
                      GROUP_CONCAT(order_details.quantity SEPARATOR ', ') AS quantities,
                      GROUP_CONCAT(order_details.price SEPARATOR ', ') AS prices,
                      tbl_order.total, 
                      tbl_order.date, 
                      order_details.order_status 
                  FROM 
                      order_details 
                  JOIN 
                      tbl_order ON order_details.order_id = tbl_order.order_id 
                  WHERE 
                      tbl_order.order_id = ? AND tbl_order.lid = ?";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("ii", $order_id, $_SESSION['uid']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $order_details = $result->fetch_assoc(); // Fetch the order details
        } else {
            $error_message = "No order found with this ID.";
        }
        $stmt->close();
    } else {
        $error_message = "Please enter a valid order ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .progress {
            height: 20px;
        }
        .progress-bar {
            background-color: #28a745; /* Green color for the progress bar */
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Track Your Order</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="order_id">Enter Order ID:</label>
            <input type="text" class="form-control" id="order_id" name="order_id" required>
        </div>
        <button type="submit" class="btn btn-primary">Track Order</button>
    </form>

    <?php if ($error_message): ?>
        <div class="alert alert-danger mt-3"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($order_details): ?>
        <h3 class="mt-4">Order Details</h3>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_details['order_id']); ?></p>
        <p><strong>Products name:</strong> <?php echo htmlspecialchars($order_details['product_names']); ?></p>
        <p><strong>Quantities:</strong> <?php echo htmlspecialchars($order_details['quantities']); ?></p>
        <p><strong>Prices:</strong> <?php echo htmlspecialchars($order_details['prices']); ?></p>
        <p><strong>Total Amount:</strong> Rs. <?php echo htmlspecialchars($order_details['total']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($order_details['date']); ?></p>
        <p><strong>Status:</strong> 
            <?php 
            echo $order_details['order_status'] == 0 ? 'Order Not Placed' : 'Order Placed'; 
            ?>
        </p>

        <!-- Progress Bar -->
        <h4>Order Status</h4>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: <?php echo ($order_details['order_status'] + 1) * 25; ?>%;" aria-valuenow="<?php echo ($order_details['order_status'] + 1) * 25; ?>" aria-valuemin="0" aria-valuemax="100">
                <?php 
                switch ($order_details['order_status']) {
                    case 1:
                        echo "Ordered";
                        break;
                    case 2:
                        echo "Shipped";
                        break;
                   
                    case 3:
                        echo "Delivered";
                        break;
                    default:
                        echo "Unknown Status";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
