<?php
include('supplierindex.php');
require('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the supplier ID based on user ID
$uid = $_SESSION['uid'];
$sidQuery = "SELECT sid FROM s_registration WHERE lid='$uid'";
$sidResult = mysqli_query($con, $sidQuery);
if ($sidResult && mysqli_num_rows($sidResult) > 0) {
    $sidRow = mysqli_fetch_assoc($sidResult);
    $supplier_id = intval($sidRow['sid']);
} else {
    die("Supplier ID (sid) not found for the logged-in user.");
}

// Get the product ID from the URL
$product_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

// Fetch the product details
$productQuery = "SELECT pd.*, tp.brand, tp.species 
                 FROM product_dog pd 
                 LEFT JOIN tblpro tp ON pd.product_id = tp.product_id 
                 WHERE pd.product_id = $product_id AND pd.sid = $supplier_id";
$productResult = mysqli_query($con, $productQuery);
$product = mysqli_fetch_assoc($productResult);

if (!$product) {
    die("Product not found or you don't have permission to edit it.");
}

// Fetch categories with status 0
$categoriesQuery = "SELECT cid, name FROM category WHERE status = 0";
$categoriesResult = mysqli_query($con, $categoriesQuery);

// Fetch subcategories for the product's category
$subcategoriesQuery = "SELECT subid, name FROM subcategory WHERE cid = {$product['cid']} AND status = 0";
$subcategoriesResult = mysqli_query($con, $subcategoriesQuery);

// Fetch product variants
$variantsQuery = "SELECT * FROM product_variants WHERE product_id = $product_id";
$variantsResult = mysqli_query($con, $variantsQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $brand = mysqli_real_escape_string($con, $_POST['brand']);
    $species = mysqli_real_escape_string($con, $_POST['species']);
    $cid = intval($_POST['cid']);
    $subid = intval($_POST['subid']);
    
    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Update product_dog table without updating images
        $updateProductQuery = "
            UPDATE product_dog SET 
            name = '$product_name', 
            description = '$description', 
            cid = $cid, 
            subid = $subid
            WHERE product_id = $product_id AND sid = $supplier_id
        ";
        
        if (!mysqli_query($con, $updateProductQuery)) {
            throw new Exception("Error updating product_dog: " . mysqli_error($con));
        }

        // Check if a record exists in tblpro
        $checkTblproQuery = "SELECT tblid FROM tblpro WHERE product_id = $product_id";
        $checkTblproResult = mysqli_query($con, $checkTblproQuery);

        if (mysqli_num_rows($checkTblproResult) > 0) {
            // Update existing record in tblpro
            $updateTblproQuery = "
                UPDATE tblpro SET 
                brand = '$brand', 
                species = '$species'
                WHERE product_id = $product_id
            ";
            if (!mysqli_query($con, $updateTblproQuery)) {
                throw new Exception("Error updating tblpro: " . mysqli_error($con));
            }
        } else {
            // Insert new record into tblpro
            $insertTblproQuery = "
                INSERT INTO tblpro (product_id, brand, species) 
                VALUES ($product_id, '$brand', '$species')
            ";
            if (!mysqli_query($con, $insertTblproQuery)) {
                throw new Exception("Error inserting into tblpro: " . mysqli_error($con));
            }
        }

        // Commit transaction
        mysqli_commit($con);
        echo "<script>alert('Product updated successfully.'); window.location.href = 'viewproduct.php';</script>";
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        echo "Error updating product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
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
            align-items: center;
        }
        label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        input[type="text"], textarea {
            width: 100%;
            height: 40px;
            margin-left: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }
        textarea {
            height: 100px;
        }
        input[type="file"] {
            margin-left: 10px;
        }
        .variant {
            margin-bottom: 20px;
        }
        .variant label {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .variant input[type="text"] {
            width: 100px;
            margin-left: 10px;
        }
        .variant input[type="number"] {
            width: 100px;
            margin-left: 10px;
        }
        button[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        button[type="submit"]:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST">
            <label>
                Product Name:
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </label>
            <label>
                Description:
                <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </label>
            <label>
                Brand:
                <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
            </label>
            <label>
                Species:
                <input type="text" name="species" value="<?php echo htmlspecialchars($product['species']); ?>" required>
            </label>
            <label>
                Category:
                <select name="cid" required>
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                        <option value="<?php echo $category['cid']; ?>" <?php echo ($category['cid'] == $product['cid']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </label>
            <label>
                Subcategory:
                <select name="subid" required>
                    <?php while ($subcategory = mysqli_fetch_assoc($subcategoriesResult)): ?>
                        <option value="<?php echo $subcategory['subid']; ?>" <?php echo ($subcategory['subid'] == $product['subid']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subcategory['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </label>

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>

<?php
mysqli_close($con);
?>
