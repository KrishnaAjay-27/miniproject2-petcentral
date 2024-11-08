<?php
include("header.php");
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Sorting and filtering
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set species to "dog food"
$species = 'cat grooming'; // Only fetch products with species "dog food"

$where_clause = "WHERE pd.status = 0 AND tp.species = '$species'"; // Add species condition
if ($search) $where_clause .= " AND pd.name LIKE '%$search%'"; // Search by product name

// Default order clause
$order_clause = "ORDER BY pd.name ASC"; // Default to sorting by name ascending

// Update order clause based on sort selection
if ($sort == 'price_asc') {
    $order_clause = "ORDER BY MIN(pv.price) ASC"; // Sort by minimum price ascending
} elseif ($sort == 'price_desc') {
    $order_clause = "ORDER BY MIN(pv.price) DESC"; // Sort by minimum price descending
} elseif ($sort == 'name_asc') {
    $order_clause = "ORDER BY pd.name ASC"; // Sort by name ascending
} elseif ($sort == 'name_desc') {
    $order_clause = "ORDER BY pd.name DESC"; // Sort by name descending
}

// Fetch products
$query = "
    SELECT pd.*, c.name AS category_name, sc.name AS subcategory_name, 
           MIN(pv.price) AS min_price, tp.brand, tp.species
    FROM product_dog pd
    LEFT JOIN category c ON pd.cid = c.cid
    LEFT JOIN subcategory sc ON pd.subid = sc.subid
    LEFT JOIN product_variants pv ON pd.product_id = pv.product_id
    LEFT JOIN tblpro tp ON pd.product_id = tp.product_id
    $where_clause
    GROUP BY pd.product_id
    $order_clause
";

$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Fetch categories and subcategories for filter options
$categories_query = "SELECT * FROM category ORDER BY name";
$categories_result = mysqli_query($con, $categories_query);

$subcategories_query = "SELECT * FROM subcategory ORDER BY name";
$subcategories_result = mysqli_query($con, $subcategories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Food Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #131921;
            --secondary-color: #232f3e;
            --accent-color: #febd69;
            --text-color: #333;
            --light-bg: #f3f3f3;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3; /* Overall background color */
            color: #333;
        }

        h1 {
            text-align: center;
            margin: 20px 0; /* Margin for spacing */
            color: #131921;
            font-size: 2.5em; /* Larger font size */
            font-weight: bold; /* Bold text */
            text-transform: uppercase; /* Uppercase text */
            letter-spacing: 1px; /* Spacing between letters */
            background-color: white; /* White background for the title */
            padding: 10px; /* Padding around the title */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            border-radius: 4px; /* Rounded corners */
        }

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-container input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
        }

        .search-container button {
            background-color: var(--accent-color);
            border: none;
            color: var(--primary-color);
            padding: 10px 15px;
            cursor: pointer;
            font-size: 16px;
        }

        .search-container button:hover {
            background-color: #f3a847;
        }

        .search-container .fa-search {
            font-size: 18px;
        }

        .search-container .fa-microphone {
            font-size: 18px;
        }

        .filter-sort-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
         
            padding: 10px;
            border-radius: 4px;
        }

        .filter-container, .sort-container {
            display: flex;
            align-items: center;
         
            margin-left: 1320px;
        }

        .filter-container select, .sort-container select {
            padding: 8px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            background-color: white;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .product-card h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .product-card p {
            margin: 5px 0;
            color: #666;
        }

        .product-card .price {
            font-weight: bold;
            color: #B12704;
            font-size: 18px;
            margin: 10px 0;
        }

        .product-card .buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card a {
            display: inline-block;
            background-color: var(--accent-color);
            color: var(--primary-color);
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .product-card a:hover {
            background-color: #f3a847;
        }

        .wishlist-btn {
            background: none;
            border: none;
            color: #ff4d4d;
            cursor: pointer;
            font-size: 1.2em;
            transition: color 0.3s ease;
        }

        .wishlist-btn:hover {
            color: #ff0000;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .back-arrow {
            color: white;
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .voice-button {
            display: inline-block;
            background-color: #febd69; /* Accent color */
            color: #131921; /* Primary color */
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 18px; /* Adjust font size */
        }

        .voice-button:hover {
            background-color: #f3a847; /* Darker shade on hover */
        }

        .voice-button:focus {
            outline: none; /* Remove outline on focus */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cat grooming Products</h1> <!-- Title with white background -->

        <div class="search-container">
            <form action="" method="get" id="search-form">
                <input type="text" name="search" id="search-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <div class="filter-sort-container">
            <div class="sort-container">
                <form action="" method="get">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Sort by</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <img src="uploads/<?php echo $row['image1']; ?>" alt="<?php echo $row['name']; ?>">
                    <h4><?php echo $row['name']; ?></h4>
                    <p>Brand: <?php echo $row['brand']; ?></p>
                    <p>For: <?php echo $row['species']; ?></p>
                    <p class="price">Rs.<?php echo number_format($row['min_price'], 2); ?></p>
                    <div class="buttons">
                        <a href="viewpro.php?id=<?php echo $row['product_id']; ?>">View Details</a>
                        <button class="wishlist-btn" onclick="addToWishlist(<?php echo $row['product_id']; ?>)"><i class="fas fa-heart"></i></button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addToWishlist(productId) {
            console.log('Adding product to wishlist:', productId);
            $.ajax({
                url: 'add_to_wishlist.php',
                method: 'POST',
                data: {product_id: productId},
                dataType: 'json',
                success: function(response) {
                    console.log('Wishlist response:', response);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(response.message || 'Unknown error occurred');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while adding the item to the wishlist: ' + error,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        function goBack() {
            <?php include 'goback.php'; ?>
        }
    </script>
</body>
</html>

<?php
include("footer.php");
mysqli_close($con);
?>