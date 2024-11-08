<?php
include("header.php");
include 'message-box.php';
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Sorting and filtering
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';
$species = isset($_GET['species']) ? $_GET['species'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "WHERE pd.status = 0";
if ($category) $where_clause .= " AND c.cid = '$category'";
if ($subcategory) $where_clause .= " AND sc.subid = '$subcategory'";
if ($species) $where_clause .= " AND tp.species = '$species'";
if ($search) $where_clause .= " AND (pd.name LIKE '%$search%' OR c.name LIKE '%$search%' OR sc.name LIKE '%$search%')";

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
    <title>Our Products</title>
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
            background-color: #f3f3f3;
            color: #333;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            color: #131921;
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
        <h1>Our Products</h1>

        <div class="search-container">
            <form action="" method="get" id="search-form">
                <input type="text" name="search" id="search-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
                <button type="button" id="voice-search" class="voice-button">
                    <i class="fas fa-microphone"></i>
                </button>
            </form>
        </div>

        <div class="filter-sort-container">
            <div class="filter-container">
                <form action="" method="get">
                    <select name="subcategory" onchange="this.form.submit()">
                        <option value="">All Subcategories</option>
                        <?php while ($subcat = mysqli_fetch_assoc($subcategories_result)): ?>
                            <option value="<?php echo $subcat['subid']; ?>" <?php echo $subcategory == $subcat['subid'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subcat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="species" onchange="this.form.submit()">
                        <option value="">All Species</option>
                        <option value="dog food" <?php echo $species == 'dog food' ? 'selected' : ''; ?>>Dog food</option>
                        <option value="cat food" <?php echo $species == 'cat food' ? 'selected' : ''; ?>>Cat food</option>
                        <option value="dog Accessories" <?php echo $species == 'dog Accessories' ? 'selected' : ''; ?>>Dog Accessories</option>
                        <option value="cat Accessories" <?php echo $species == 'cat Accessories' ? 'selected' : ''; ?>>Cat Accessories</option>
                    </select>
                </form>
            </div>
            <div class="sort-container">
                <form action="" method="get">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Sort by</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="product-grid">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="product-card">
            <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
            <p>Brand: <?php echo htmlspecialchars($row['brand']); ?></p>
            <p>For: <?php echo htmlspecialchars($row['species']); ?></p>
            <p class="price">Rs.<?php echo number_format($row['min_price'], 2); ?></p>
            <div class="buttons">
                <a href="viewpro.php?id=<?php echo urlencode($row['product_id']); ?>">View Details</a>
                <button class="wishlist-btn" onclick="addToWishlist(<?php echo $row['product_id']; ?>)"><i class="fas fa-heart"></i></button>
            </div>
        </div>
    <?php endwhile; ?>
</div>
    </div>

    <script>
        document.getElementById('voice-search').onclick = function() {
            if (!('webkitSpeechRecognition' in window)) {
                alert('Your browser does not support voice search.');
                return;
            }
            var recognition = new webkitSpeechRecognition();
            recognition.lang = 'en-US';
            recognition.interimResults = false;

            recognition.onresult = function(event) {
                var transcript = event.results[0][0].transcript;
                document.getElementById('search-input').value = transcript;
                document.getElementById('search-form').submit();
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error detected: ' + event.error);
            };

            recognition.start();
        };

        function addToWishlist(productId) {
            // Add to wishlist logic here
            Swal.fire({
                icon: 'success',
                title: 'Added to Wishlist',
                text: 'Product has been added to your wishlist!',
                showConfirmButton: false,
                timer: 1500
            });
        }
    </script>
</body>
</html>
