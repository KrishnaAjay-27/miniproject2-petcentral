<?php
session_start();
require('connection.php');
// Check if product_id is set in the query string
if (!isset($_GET['product_id'])) {
    echo "No product selected.";
    exit();
}

$product_id = $_GET['product_id'];

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Prepare and execute the query to get product details
$query = "SELECT * FROM products WHERE product_id = '$product_id'";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
} else {
    echo "Product not found.";
    exit();
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .product-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .product-detail {
            margin-bottom: 15px;
        }
        .product-detail label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
        <div class="product-detail">
            <label>Description:</label>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
        </div>
        
        <div class="product-detail">
            <label>Brand:</label>
            <p><?php echo htmlspecialchars($product['brand']); ?></p>
        </div>
        <div class="product-detail">
            <label>Species:</label>
            <p><?php echo htmlspecialchars($product['species']); ?></p>
        </div>
        
        <a href="userindex.php" class="button">Back to Products</a>
    </div>
</body>
</html>
