<?php
require('connection.php');

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($con, $_POST['search']);
    
    $query = "SELECT p.*, c.name AS category_name, s.name AS subcategory_name, v.price 
              FROM product_dog p
              JOIN category c ON p.cid = c.cid
              JOIN subcategory s ON p.subid = s.subid
              JOIN product_variants v ON p.product_id = v.product_id
              WHERE p.status = 0 AND (
                  p.name LIKE '%$search%' OR 
                  s.name LIKE '%$search%' OR 
                  p.brand LIKE '%$search%' OR 
                  v.price LIKE '%$search%'
              )
              GROUP BY p.product_id";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($product = mysqli_fetch_assoc($result)) {
            echo "<div class='product-card'>";
            echo "<img src='uploads/{$product['image1']}' alt='{$product['name']}'>";
            echo "<h3>{$product['name']}</h3>";
            echo "<p>Category: {$product['category_name']}</p>";
            echo "<p>Subcategory: {$product['subcategory_name']}</p>";
            echo "<p>Brand: {$product['brand']}</p>";
            echo "<p>Price: $" . number_format($product['price'], 2) . "</p>";
            echo "<a href='product_details.php?pid={$product['product_id']}' class='view-details'>View Details</a>";
            echo "</div>";
        }
    } else {
        echo "<p>No products found.</p>";
    }
} else {
    echo "<p class='error'>Invalid search query.</p>";
}
?>