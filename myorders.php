<?php
include("useraccount.php");
require('connection.php');

if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['uid']; // Get the logged-in user's ID

// Fetch orders for the logged-in user and group products under the same order
$order_query = "
    SELECT 
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
        tbl_order.lid = '$user_id'
    GROUP BY 
        tbl_order.order_id";
$order_result = mysqli_query($con, $order_query);

if (!$order_result) {
    die("Query failed: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table/dist/bootstrap-table.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f1f4f9;
            margin: 0;
            padding: 40px;
        }

        .my-orders {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }

        h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f9c74f;
            color: black;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
        }

        td {
            background-color: #fff;
            color: #333;
            font-size: 15px;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) {
            background-color: #f3f6fa;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th, td {
                padding: 12px 10px;
            }
        }

        @media (max-width: 480px) {
            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="my-orders">
        <h2>My Orders</h2>

        <?php if (mysqli_num_rows($order_result) > 0) { ?>
            <table id="order-table" 
                   class="table table-striped table-bordered"
                   data-search="true"
                   data-show-search-clear-button="true">
                <thead>
                    <tr>
                    <th>SI No</th>
                        <th data-field="order_id">Order ID</th>
                        <th data-field="product_names">Products</th>
                        <th data-field="quantities">Quantities</th>
                       
                        <th data-field="total">Total Amount</th>
                        <th data-field="date">Date</th>
                        <th data-field="order_status">Order Status</th>
                        <th data-field="track">Track Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                      $sno = 1;
                      while ($order = mysqli_fetch_array($order_result)) { ?>
                        <tr>
                        <td><?php echo $sno++; ?></td>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_names']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantities']); ?></td>
                            
                            <td><?php echo 'Rs.' . htmlspecialchars($order['total']); ?></td>
                            <td><?php echo htmlspecialchars($order['date']); ?></td>
                            <td>
                                <?php 
                                if ($order['order_status'] == 0) {
                                    echo '<span class="text-danger">Order Not Placed</span>';
                                } else {
                                    echo '<span class="text-success">Order Placed</span>';
                                }
                                ?>
                            </td>
                            <td><a href="track_order.php?order_id=<?php echo $order['order_id']; ?>">Track Order</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No orders found for your account.</p>
        <?php } ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>

