<?php
require('connection.php');
// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the product ID from the request
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details including brand and species
$productQuery = "
    SELECT 
        pd.product_id,
        pd.name,
        pd.description,
    tp.brand,
        tp.species,
        pd.image1,
        sr.supplier_code
    FROM 
        product_dog pd
    JOIN 
        s_registration sr ON pd.sid = sr.sid
    JOIN 
        tblpro tp ON pd.product_id = tp.product_id
    WHERE 
        pd.product_id = $product_id
";

$productResult = mysqli_query($con, $productQuery);

if ($productResult) {
    $product = mysqli_fetch_assoc($productResult);
    
    // Fetch all variants for the product
    $variantsQuery = "
        SELECT 
            pv.variant_id,
            pv.size,
            pv.quantity,
            pv.price
        FROM 
            product_variants pv
        WHERE 
            pv.product_id = $product_id
    ";
    
    $variantsResult = mysqli_query($con, $variantsQuery);
    $variants = [];
    
    while ($variant = mysqli_fetch_assoc($variantsResult)) {
        $variants[] = $variant; // Store each variant in an array
    }
    
    $product['variants'] = $variants; // Add variants to the product array
    echo json_encode($product); // Return product details as JSON
} else {
    echo json_encode(['error' => 'Product not found.']);
}

// Close the database connection
mysqli_close($con);
?>