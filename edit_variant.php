<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['variant_id'])) {
    $variant_id = intval($_GET['variant_id']);
    $variantQuery = "SELECT pv.*, pd.product_id FROM product_variants pv 
                     JOIN product_dog pd ON pv.product_id = pd.product_id 
                     WHERE pv.variant_id = $variant_id";
    $variantResult = mysqli_query($con, $variantQuery);
    $variant = mysqli_fetch_assoc($variantResult);

    if (!$variant) {
        die("Variant not found");
    }
} else {
    die("No variant ID provided");
}

// Handle form submission for updating the variant
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $size = mysqli_real_escape_string($con, $_POST['size']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    $updateQuery = "UPDATE product_variants SET size = '$size', quantity = $quantity, price = $price WHERE variant_id = $variant_id";
    if (mysqli_query($con, $updateQuery)) {
        // Redirect to viewproduct.php with the product_id
        header("Location: viewproduct.php?product_id=" . $variant['product_id']);
        exit();
    } else {
        $error_message = "Error updating variant: " . mysqli_error($con);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Variant</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Variant</h1>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="size">Size:</label>
            <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($variant['size']); ?>" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo $variant['quantity']; ?>" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $variant['price']; ?>" required>

            <input type="submit" value="Update Variant">
        </form>
        <a href="viewproduct.php?product_id=<?php echo $variant['product_id']; ?>" class="back-link">Back to Product</a>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>