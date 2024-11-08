<?php
session_start();
require('connection.php');



// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

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

    // Check if product already exists for this supplier
    if (productExists($con, $product_name, $brand, $species, $supplier_id)) {
        echo json_encode(['success' => false, 'message' => 'A product with this name, brand, and species already exists for your account.']);
        mysqli_close($con);
        exit();
    }

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
    
    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Insert product
        $insertProductQuery = "
            INSERT INTO product_dog (name, description, image1, image2, sid, cid, subid)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = mysqli_prepare($con, $insertProductQuery);
        mysqli_stmt_bind_param($stmt, "sssssii", $product_name, $description, $image1, $image2, $supplier_id, $cid, $subid);
        mysqli_stmt_execute($stmt);
        $product_id = mysqli_insert_id($con);

        // Insert brand and species into tblpro
        $insertTblProQuery = "
            INSERT INTO tblpro (product_id, brand, species)
            VALUES (?, ?, ?)
        ";
        $stmt = mysqli_prepare($con, $insertTblProQuery);
        mysqli_stmt_bind_param($stmt, "iss", $product_id, $brand, $species);
        mysqli_stmt_execute($stmt);

        // Insert product variants
        $sizes = $_POST['size'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];

        foreach ($sizes as $index => $size) {
            $quantity = intval($quantities[$index]);
            $price = floatval($prices[$index]);

            $insertVariantQuery = "
                INSERT INTO product_variants (product_id, size, quantity, price)
                VALUES (?, ?, ?, ?)
            ";
            $stmt = mysqli_prepare($con, $insertVariantQuery);
            mysqli_stmt_bind_param($stmt, "isid", $product_id, $size, $quantity, $price);
            mysqli_stmt_execute($stmt);
        }

        // Commit transaction
        mysqli_commit($con);

        echo json_encode(['success' => true, 'message' => 'Product added successfully.']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Error adding product: ' . $e->getMessage()]);
    }

    mysqli_close($con);
    exit();
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
    exit();
}

// Add this function after the database connection and before the form submission handling
function productExists($con, $product_name, $brand, $species, $supplier_id) {
    $query = "SELECT COUNT(*) as count FROM product_dog pd
              JOIN tblpro tp ON pd.product_id = tp.product_id
              WHERE pd.name = ? AND tp.brand = ? AND tp.species = ? AND pd.sid = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sssi", $product_name, $brand, $species, $supplier_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
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
            display: flex;
        }
        .sidebar {
            background-color: #003366;
            color: white;
            width: 250px;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .container {
        margin-left: 570px; /* Space for the sidebar */
        margin-right: auto; /* Centering */
        margin-top: 20px; /* Optional: Add some top margin */
        padding: 100px;
        max-width: 900px; /* Set a max width */
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px; /* Optional: Add rounded corners */
    }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
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
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #cc0000;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #ff3333;
        }
    </style>
</head>
<body>
<div class="sidebar">
      
      <a href="supplierindex.php">Dashboard</a>
      <a href="logout.php" class="logout-btn">Logout</a>
  </div>
    <div class="container">
     
        <h1>Add Product</h1>
        <form method="POST" enctype="multipart/form-data" id="addProductForm">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>
            <p id="product_name_error" class="error-message">Only alphabets and spaces are allowed</p>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="brand">Brand Name:</label>
            <input type="text" id="brand" name="brand" required>
            <p id="brand_error" class="error-message">Only alphabets and numbers are allowed</p>

            <label for="species">Species:</label>
            <select id="species" name="species" required>
                <option value="dog food" selected>Dog Food</option>
                <option value="cat food">Cat food</option>
                <option value="dog Accessories" selected>Dog Accessories</option>
                <option value="cat Accessories">Cat Accessories</option>
                <option value="dog grooming" selected>Dog grooming</option>
                <option value="cat grooming">Cat grooming</option>

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

            <label for="image1">Product Image1:</label>
            <input type="file" id="image1" name="image1" accept="image/*">
            <label for="image2">Product Image 2:</label>
            <input type="file" id="image2" name="image2" accept="image/*">

            <div class="variant-section">
                <h2>Product Variants</h2>
                <div id="variants-container">
                    <div class="variant-entry">
                        <label for="size[]">Size:</label>
                        <input type="text" name="size[]" required class="size-input">
                        <p class="error-message size-error">Special characters are not allowed</p>

                        <label for="quantity[]">Quantity:</label>
                        <input type="number" name="quantity[]" required class="quantity-input">
                        <p class="error-message quantity-error">Only numbers are allowed</p>

                        <label for="price[]">Price:</label>
                        <input type="number" name="price[]" step="0.01" required class="price-input">
                        <p class="error-message price-error">Only numbers are allowed</p>
                    </div>
                </div>
                <button type="button" class="add-variant-button" onclick="addVariant()">Add Another Variant</button>
            </div>

            <button type="submit">Add Product</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <input type="text" name="size[]" required class="size-input">
                <p class="error-message size-error">Special characters are not allowed</p>

                <label for="quantity[]">Quantity:</label>
                <input type="number" name="quantity[]" required class="quantity-input">
                <p class="error-message quantity-error">Only numbers are allowed</p>

                <label for="price[]">Price:</label>
                <input type="number" name="price[]" step="0.01" required class="price-input">
                <p class="error-message price-error">Only numbers are allowed</p>
            `;
            container.appendChild(newVariant);
            addValidationListeners(newVariant);
        }

        function addValidationListeners(element) {
            const sizeInputs = element.querySelectorAll('.size-input');
            const quantityInputs = element.querySelectorAll('.quantity-input');
            const priceInputs = element.querySelectorAll('.price-input');

            sizeInputs.forEach(input => input.addEventListener('input', validateSize));
            quantityInputs.forEach(input => input.addEventListener('input', validateQuantity));
            priceInputs.forEach(input => input.addEventListener('input', validatePrice));
        }

        function validateProductName() {
            const input = document.getElementById('product_name');
            const error = document.getElementById('product_name_error');
            const regex = /^[A-Za-z\s]+/;

            if (!regex.test(input.value)) {
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }
        }

        function validateBrand() {
            const input = document.getElementById('brand');
            const error = document.getElementById('brand_error');
            const regex = /^[A-Za-z0-9]+$/;

            if (!regex.test(input.value)) {
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }
        }

        function validateSize(event) {
            const input = event.target;
            const error = input.nextElementSibling;
            const regex = /^[A-Za-z0-9\s]+/;

            if (!regex.test(input.value)) {
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }
        }

        function validateQuantity(event) {
            const input = event.target;
            const error = input.nextElementSibling;
            const regex = /^\d+$/;

            if (!regex.test(input.value)) {
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }
        }

        function validatePrice(event) {
            const input = event.target;
            const error = input.nextElementSibling;
            const regex = /^\d+(\.\d{1,2})?$/;

            if (!regex.test(input.value)) {
                error.style.display = 'block';
            } else {
                error.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const productNameInput = document.getElementById('product_name');
            const brandInput = document.getElementById('brand');

            if (productNameInput) {
                productNameInput.addEventListener('input', validateProductName);
            }
            if (brandInput) {
                brandInput.addEventListener('input', validateBrand);
            }

            addValidationListeners(document);
        });

        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Perform client-side validation here
            // ...

            // If validation passes, submit the form
            fetch('addproductdog.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'supplierindex.php'; // Redirect to supplier index page
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product.');
            });
        });
    </script>
</body>
</html>