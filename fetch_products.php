<?php
session_start();
require('connection.php');

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch the admin's email from the 'login' table
$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $admin = mysqli_fetch_assoc($result);
    $adminEmail = $admin ? $admin['email'] : 'Admin';
} else {
    $adminEmail = 'Admin'; // Default email in case of query failure
}

// Fetch all product details with corresponding supplier ID, supplier code, brand, and species
$productQuery = "
    SELECT 
        pd.product_id,
        pd.name,
        pd.image1,
        sr.supplier_code,
        sr.name AS supplier_name,
        tp.brand,
        tp.species
    FROM 
        product_dog pd
    JOIN 
        s_registration sr ON pd.sid = sr.sid
    JOIN 
        tblpro tp ON pd.product_id = tp.product_id
"; // Fetching product name, image, supplier code, supplier name, brand, and species

$productResult = mysqli_query($con, $productQuery);

if ($productResult) {
    $products = [];
    while ($product = mysqli_fetch_assoc($productResult)) {
        $products[] = $product; // Store each product in an array
    }
} else {
    echo "Error fetching products: " . mysqli_error($con);
}

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Roboto:400,500,700');
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.3);
        }
        .sidebar .admin-info {
            margin-bottom: 30px;
        }
        .sidebar .admin-info p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            margin: 5px 0;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar a:hover {
            background-color: #34495e;
            color: #ecf0f1;
        }
        .main-content {
            margin-left: 270px; /* Space for sidebar */
            padding: 20px;
            width: calc(100% - 270px);
            min-height: 100vh;
            background-color: #fff;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative; /* Ensure relative positioning for child elements */
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        .header .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .header .logout-btn:hover {
            background-color: #c0392b;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between items */
            padding: 20px;
        }
        .product-card {
            width: 100%; /* Full width of the grid item */
            position: relative;
            box-shadow: 0 2px 7px #dfdfdf;
            background: #fafafa;
            border-radius: 8px; /* Rounded corners */
            overflow: hidden; /* Ensure content doesn't overflow */
        }
        .product-tumb {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px; /* Reduced height for smaller cards */
            background: #f0f0f0;
        }
        .product-tumb img {
            max-width: 100%;
            max-height: 100%;
        }
        .product-details {
            padding: 15px; /* Adjusted padding */
        }
        .product-details h4 {
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #363636;
        }
        .product-details p {
            font-size: 14px; /* Adjusted font size */
            line-height: 20px;
            margin-bottom: 10px;
            color: #999;
        }
        .view-more {
            display: inline-block;
            margin-top: 10px; /* Space above the button */
            padding: 8px 12px; /* Adjusted padding */
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer; /* Change cursor to pointer */
            transition: background-color 0.3s; /* Smooth transition */
            text-align: center; /* Center text */
        }
        .view-more:hover {
            background-color: #0056b3;
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
            background-color: rgba(0, 0, 0, 0.6); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; /* 10% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 600px; /* Maximum width */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow for depth */
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
        .modal-body {
            margin-top: 15px;
        }
        .modal-body img {
            max-width: 100%;
            border-radius: 5px;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .buy--btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .buy--btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-info">
            <p>Welcome, <?php echo htmlspecialchars($adminEmail); ?></p>
        </div>
        <a href="admindashboard.php">Dashboard</a>
        <a href="manageuseradmin.php">Manage Users</a>
        <a href="addcategory.php">Manage Categories</a>
        <a href="addsubcategory.php">Manage Subcategory</a>
        <a href="viewcategory.php">View Categories</a>
        <a href="viewsubcategory.php">View Subcategories</a>
        <a href="addsuppliers.php">Add Suppliers</a>
        <a href="managesupplieadmin.php">Manage Suppliers</a>
        <a href="fetch_products.php">View Products</a>
    </div>

    <div class="main-content">
        <div class="header">
            <a href="admindashboard.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Product List</h1>

        <div class="container">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-tumb">
                            <img src="<?php echo htmlspecialchars($product['image1']); ?>" alt="Product Image">
                        </div>
                        <div class="product-details">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p><strong>Supplier Code:</strong> <?php echo htmlspecialchars($product['supplier_code']); ?></p>
                            <p><strong>Supplier Name:</strong> <?php echo htmlspecialchars($product['supplier_name']); ?></p>
                            <a href="#" class="view-more" onclick="openModal(<?php echo $product['product_id']; ?>)">View More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>

        <!-- The Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modalProductName"></h2>
                <div class="modal-body">
                    <img id="modalProductImage" src="" alt="Product Image">
                    <p><strong>Description:</strong> <span id="modalProductDescription"></span></p>
                    <p><strong>Brand:</strong> <span id="modalProductBrand"></span></p>
                    <p><strong>Species:</strong> <span id="modalProductSpecies"></span></p>
                    <h3>Variants</h3>
                    <div id="modalVariants"></div> <!-- Section to display variants -->
                </div>
                <div class="modal-footer">
                    <button class="buy--btn" onclick="buyProduct()">Buy Now</button>
                </div>
            </div>
        </div>

        <script>
            function openModal(productId) {
                // Fetch product details using AJAX
                fetch('get_product_details.php?id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modalProductName').innerText = data.name;
                        document.getElementById('modalProductImage').src = data.image1;
                        document.getElementById('modalProductDescription').innerText = data.description;
                        document.getElementById('modalProductBrand').innerText = data.brand; // Set brand
                        document.getElementById('modalProductSpecies').innerText = data.species; // Set species

                        // Display variants
                        let variantsHtml = '';
                        if (data.variants && data.variants.length > 0) {
                            data.variants.forEach(variant => {
                                variantsHtml += `
                                    <div>
                                        <strong>Variant ID:</strong> ${variant.variant_id}<br>
                                        <strong>Size:</strong> ${variant.size}<br>
                                        <strong>Quantity:</strong> ${variant.quantity}<br>
                                        <strong>Price:</strong> Rs.${variant.price}<br>
                                    </div>
                                    <hr>
                                `;
                            });
                        } else {
                            variantsHtml = '<p>No variants available.</p>';
                        }
                        document.getElementById('modalVariants').innerHTML = variantsHtml;

                        document.getElementById('myModal').style.display = "block"; // Show the modal
                    });
            }

            function closeModal() {
                document.getElementById('myModal').style.display = "none"; // Hide the modal
            }

            // Close the modal when clicking outside of it
            window.onclick = function(event) {
                if (event.target == document.getElementById('myModal')) {
                    closeModal();
                }
            }
        </script>
    </div>
</body>
</html>