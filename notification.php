<?php
require('connection.php');
session_start();
include('head.php');

$uid = $_SESSION['uid'];
$supplier_id = null;
$zero_quantity_products = [];

// Fetch supplier ID based on user ID
$sidQuery = "SELECT sid FROM s_registration WHERE lid='$uid'";
$sidResult = mysqli_query($con, $sidQuery);

if ($sidResult) {
    $sidRow = mysqli_fetch_assoc($sidResult);
    $supplier_id = intval($sidRow['sid']);
}

if ($supplier_id) {
    // Fetch zero quantity products for this supplier
    $query = "
        SELECT 
            COALESCE(pd.product_name, pp.product_name) AS product_name,
            COALESCE(pv.size, 'N/A') AS size,
            COALESCE(pv.quantity, pp.quantity) AS quantity
        FROM 
            (SELECT product_id, product_name FROM product_dog WHERE sid = ?
             UNION ALL
             SELECT petid AS product_id, product_name FROM productpet WHERE sid = ?) AS products
        LEFT JOIN 
            product_variants pv ON products.product_id = pv.product_id
        LEFT JOIN 
            product_dog pd ON products.product_id = pd.product_id
        LEFT JOIN 
            productpet pp ON products.product_id = pp.petid
        WHERE 
            COALESCE(pv.quantity, pp.quantity) = 0
    ";

    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $supplier_id, $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $zero_quantity_products[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zero Stock Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Zero Stock Products</h2>
    <?php if (empty($zero_quantity_products)): ?>
        <p>No products with zero stock.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($zero_quantity_products as $product): ?>
                <li>
                    <?php echo htmlspecialchars($product['product_name']); ?> 
                    (Size: <?php echo htmlspecialchars($product['size']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="view_orders.php" class="back-link">Back to Order Details</a>
</div>
</body>
</html>