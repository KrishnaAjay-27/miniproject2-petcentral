<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
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
$productQuery = "
    SELECT 
        pd.product_id,
        pd.name,
        pd.image1,
        sr.supplier_code,
        sr.name AS supplier_name
    FROM 
        product_dog pd
    JOIN 
        s_registration sr ON pd.sid = sr.sid
"; // Fetching product name, image, supplier code, and supplier name

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

// // Fetch the count of registered users from the 'registration' table
// $userCountQuery = "SELECT COUNT(*) AS count FROM registration";
// $userCountResult = mysqli_query($con, $userCountQuery);
// $userCount = $userCountResult ? mysqli_fetch_assoc($userCountResult)['count'] : 0;

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f4f4f4;
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
            transition: width 0.3s;
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
            margin-left: 270px;
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
       
.user-count-box {
    background-color: #f1c40f; /* Yellow background color */
    color: black; /* Black text color for better contrast */
    border: none;
    border-radius: 8px; /* Slightly larger border-radius */
    padding: 10px 20px; /* Reduced padding to make the box smaller */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    font-size: 16px; /* Slightly smaller font size */
    font-weight: bold;
    position: absolute; /* Absolute positioning */
    left: 20px; /* Distance from the left edge */
    top: 200px; /* Further down from the top edge */
    max-width: 200px; /* Reduced width */
}
    </style>
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive grid */
            gap: 20px; /* Space between items */
            padding: 20px;
        }
        .product {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px; /* Increased padding for better spacing */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left; /* Align text to the left */
            display: flex; /* Use flexbox for alignment */
            flex-direction: column; /* Stack items vertically */
            justify-content: space-between; /* Space between items */
            transition: transform 0.2s, box-shadow 0.2s; /* Smooth hover effect */
        }
        .product:hover {
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Deeper shadow on hover */
        }
        .product img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px; /* Space below the image */
        }
        .product-info {
            display: flex; /* Use flexbox for alignment */
            flex-direction: column; /* Stack items vertically */
            margin-bottom: 10px; /* Space below the info */
        }
        .view-more {
            display: inline-block;
            margin-top: auto; /* Push to the bottom */
            padding: 10px 15px; /* Increased padding */
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer; /* Change cursor to pointer */
            transition: background-color 0.3s; /* Smooth transition */
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
    </style>
</head>
<body>
    
    <h1>Product List</h1>

    <div class="container">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($product['image1']); ?>" alt="Product Image">
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p><strong>Supplier Code:</strong> <?php echo htmlspecialchars($product['supplier_code']); ?></p>
                        <p><strong>Supplier Name:</strong> <?php echo htmlspecialchars($product['supplier_name']); ?></p> <!-- Displaying supplier name -->
                    </div>
                    <a href="#" class="view-more" onclick="openModal(<?php echo $product['product_id']; ?>)">View More</a>
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
            <img id="modalProductImage" src="" alt="Product Image" style="max-width: 100%;">
            <p id="modalProductDescription"></p>
            <p><strong>Brand:</strong> <span id="modalProductBrand"></span></p>
            <p><strong>Species:</strong> <span id="modalProductSpecies"></span></p>
            <p><strong>Price:</strong> $<span id="modalProductPrice"></span></p>
            
            <h3>Variants</h3>
            <div id="modalVariants"></div> <!-- Section to display variants -->
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
                    document.getElementById('modalProductBrand').innerText = data.brand;
                    document.getElementById('modalProductSpecies').innerText = data.species;
                    document.getElementById('modalProductPrice').innerText = data.price;

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
</body>
</html>
    <div class="sidebar">
        <div class="admin-info">
            <p>Welcome, <?php echo htmlspecialchars($adminEmail); ?></p>
        </div>
        <a href="admindashboard.php">Dashboard</a>
        <a href="manageuseradmin.php">Manage Users</a>
        <a href="addcategory.php">Manage Categories </a>
        <a href="addsubcategory.php">Manage Subcategory</a>
        <a href="viewcategory.php">View Categories</a>
        <a href="viewsubcategory.php">View Sub categories</a>
        <a href="addsuppliers.php">Add Suppliers</a>
        <a href="managesupplieadmin.php">Manage Supliers</a>
        <a href="fetch_products.php">View product</a>
    </div>
    <div class="main-content">
        <div class="header">
            <a href="admindashboard.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <!-- <h1>Dashboard Content</h1>
        <div class="user-count-box">
            Registered Users: <?php echo htmlspecialchars($userCount); ?>
        </div> -->
        <!-- Add your dashboard content here -->
    </div>
</body>
</html>
