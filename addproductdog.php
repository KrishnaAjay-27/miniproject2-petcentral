<?php
session_start();

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

// Fetch categories with status 0
$categoriesQuery = "SELECT cid, name FROM category WHERE status = 0";
$categoriesResult = mysqli_query($con, $categoriesQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $brand = mysqli_real_escape_string($con, $_POST['brand']);
    $species = mysqli_real_escape_string($con, $_POST['species']);
    $cid = intval($_POST['cid']);
    $subid = intval($_POST['subid']);

    // Handle image upload
    $image1 = '';
    if (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image1']['tmp_name'];
        $imageName = basename($_FILES['image1']['name']);
        $imageDestination = 'uploads/' . $imageName;
        move_uploaded_file($imageTmpName, $imageDestination);
        $image1 = $imageName;
    }
    
    $image2 = '';
    if (isset($_FILES['image2']) && $_FILES['image2']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image2']['tmp_name'];
        $imageName = basename($_FILES['image2']['name']);
        $imageDestination = 'uploads/' . $imageName;
        move_uploaded_file($imageTmpName, $imageDestination);
        $image2 = $imageName;
    }
    
    // Insert product
    $insertProductQuery = "
        INSERT INTO product_dog (name, description, image1, image2, brand, species, sid, cid, subid)
        VALUES ('$product_name', '$description', '$image1', '$image2', '$brand', '$species', $supplier_id, $cid, $subid)
    ";
    if (mysqli_query($con, $insertProductQuery)) {
        $product_id = mysqli_insert_id($con);

        // Insert product variants
        $sizes = $_POST['size'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];

        foreach ($sizes as $index => $size) {
            $quantity = intval($quantities[$index]);
            $price = floatval($prices[$index]);

            $insertVariantQuery = "
                INSERT INTO product_variants (product_id, size, quantity, price)
                VALUES ($product_id, '$size', $quantity, $price)
            ";
            mysqli_query($con, $insertVariantQuery);
        }

        echo "Product added successfully.";
    } else {
        echo "Error adding product: " . mysqli_error($con);
    }

    mysqli_close($con);
    exit(); // Ensure the script stops execution after form submission
}

// Handle AJAX request for subcategories
if (isset($_GET['cid'])) {
    $cid = intval($_GET['cid']);

    // Fetch subcategories for the selected category with status 0
    $subcategoriesQuery = "SELECT subid, name FROM subcategory WHERE cid = $cid AND status = 0";
    $subcategoriesResult = mysqli_query($con, $subcategoriesQuery);

    $subcategories = [];
    while ($row = mysqli_fetch_assoc($subcategoriesResult)) {
        $subcategories[] = $row;
    }

    echo json_encode($subcategories);
    mysqli_close($con);
    exit(); // Ensure the script stops execution after AJAX request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style> 
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            overflow: auto;
        }
        form {
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            max-height: 80vh;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .variant-section {
            margin-top: 20px;
        }
        .variant-section label {
            display: block;
            margin-bottom: 5px;
        }
        .variant-section input {
            margin-bottom: 10px;
        }
        .add-variant-button {
            margin-top: 10px;
        }
        /* Back Arrow Styling */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #4CAF50;
            font-size: 16px;
            font-weight: bold;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #45a049;
        }
        .back-link::before {
            content: '← ';
            font-size: 20px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="supplierindex.php" class="back-link">Back</a>
        <h1>Add Product</h1>
        <form method="POST" enctype="multipart/form-data">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="brand">Brand Name:</label>
            <input type="text" id="brand" name="brand" required>

            <label for="species">Species:</label>
            <select id="species" name="species" required>
                <option value="dog" selected>Dog</option>
            </select>

            <label for="cid">Category:</label>
            <select id="cid" name="cid" required onchange="fetchSubcategories()">
                <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                    <option value="<?php echo $category['cid']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="subid">Subcategory:</label>
            <select id="subid" name="subid" required>
                <!-- Subcategories will be populated based on selected category -->
            </select>

            <label for="image1">Product Image:</label>
            <input type="file" id="image1" name="image1" accept="image/*">
            <label for="image2">Product Image 2:</label>
            <input type="file" id="image2" name="image2" accept="image/*">

            <div class="variant-section">
                <h2>Product Variants</h2>
                <div id="variants-container">
                    <div class="variant-entry">
                        <label for="size[]">Size:</label>
                        <input type="text" name="size[]" required>

                        <label for="quantity[]">Quantity:</label>
                        <input type="number" name="quantity[]" required>

                        <label for="price[]">Price:</label>
                        <input type="number" name="price[]" step="0.01" required>
                    </div>
                </div>
                <button type="button" class="add-variant-button" onclick="addVariant()">Add Another Variant</button>
            </div>

            <button type="submit">Add Product</button>
        </form>
    </div>

    <script>
        function fetchSubcategories() {
            const cid = document.getElementById('cid').value;
            const subcategorySelect = document.getElementById('subid');

            fetch(`?cid=${cid}`)
                .then(response => response.json())
                .then(data => {
                    subcategorySelect.innerHTML = '';
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.subid;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                });
        }

        function addVariant() {
            const container = document.getElementById('variants-container');
            const newVariant = document.createElement('div');
            newVariant.classList.add('variant-entry');
            newVariant.innerHTML = `
                <label for="size[]">Size:</label>
                <input type="text" name="size[]" required>

                <label for="quantity[]">Quantity:</label>
                <input type="number" name="quantity[]" required>

                <label for="price[]">Price:</label>
                <input type="number" name="price[]" step="0.01" required>
            `;
            container.appendChild(newVariant);
        }
    </script>
</body>
</html>
