<?php

include("header.php");
require('connection.php');

// Fetch subcategories for filter options
$subcategories_query = "SELECT * FROM subcategory ORDER BY name";
$subcategories_result = mysqli_query($con, $subcategories_query);

// Initialize variables for filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Set the subcategory ID for "dog"
$dogSubcategoryId = 13; // Replace with the actual ID for the dog subcategory

// Build the WHERE clause
$where_clause = "WHERE pd.status = 0";

if ($gender) {
    $where_clause .= " AND tp.Gender = '$gender'";
}
if ($search) {
    $where_clause .= " AND pd.product_name LIKE '%$search%'";
}

// Default order clause
$order_clause = "ORDER BY pd.product_name ASC"; // Default to sorting by name ascending

// Update order clause based on sort selection
if ($sort == 'price_asc') {
    $order_clause = "ORDER BY MIN(pv.price) ASC"; // Sort by minimum price ascending
} elseif ($sort == 'price_desc') {
    $order_clause = "ORDER BY MIN(pv.price) DESC"; // Sort by minimum price descending
} elseif ($sort == 'name_asc') {
    $order_clause = "ORDER BY pd.product_name ASC"; // Sort by name ascending
} elseif ($sort == 'name_desc') {
    $order_clause = "ORDER BY pd.product_name DESC"; // Sort by name descending
}

// Fetch products
$query = "
    SELECT pd.*, c.name AS category_name, sc.name AS subcategory_name, 
           MIN(pv.price) AS min_price, tp.Age, tp.Gender
    FROM productpet pd
    LEFT JOIN category c ON pd.cid = c.cid
    LEFT JOIN subcategory sc ON pd.subid = sc.subid
    LEFT JOIN productpet pv ON pd.petid = pv.petid
    LEFT JOIN productpet tp ON pd.petid = tp.petid

  
    $where_clause
    GROUP BY pd.petid
    $order_clause
";

$result = mysqli_query($con, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Dog Products</title>
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
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            color: var(--primary-color);
            font-size: 2.5em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: white;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
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
            border-radius: 0 4px 4px 0;
        }

        .search-container button:hover {
            background-color: #f3a847;
        }

        .filter-sort-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: var(--secondary-color);
            padding: 10px;
            border-radius: 4px;
        }

        .filter-container, .sort-container {
            display: flex;
            align-items: center;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Pet</h1>
        <div class="search-container">
            <form action="" method="get" id="search-form">
                <input type="text" name="search" id="search-input" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <div class="filter-sort-container">
            <div class="filter-container">
                <form action="" method="get">
                    <select name="gender" onchange="this.form.submit()">
                        <option value="">All Genders</option>
                        <option value="Male" <?php echo $gender == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $gender == 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </form>
            </div>
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
                    <img src="uploads/<?php echo $row['image1']; ?>" alt="<?php echo $row['product_name']; ?>">
                    <h4><?php echo $row['product_name']; ?></h4>
                    <p>Age: <?php echo $row['Age']; ?></p>
                    <p>Gender: <?php echo $row['Gender']; ?></p>
                    <p class="price">Rs.<?php echo number_format($row['min_price'], 2); ?></p>
                    <div class="buttons">
                        <a href="viewpets.php?id=<?php echo $row['petid']; ?>">View Details</a>
                        <button class="wishlist-btn" onclick="addToWishlist(<?php echo $row['petid']; ?>)"><i class="fas fa-heart"></i></button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       function addToWishlist(petId) {
    console.log('Adding product to wishlist:','Pet ID:', petId);
    $.ajax({
        url: 'add_to_wishlist.php',
        method: 'POST',
        data: {  petid: petId }, // Send both product_id and pet_id
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
    </script>
</body>
</html>

<?php
include("footer.php");
mysqli_close($con);
?>