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
$sidRow = mysqli_fetch_assoc($sidResult);
$supplier_id = intval($sidRow['sid']);

// Fetch categories with status 0
$categoriesQuery = "SELECT cid, name FROM category WHERE status = 0";
$categoriesResult = mysqli_query($con, $categoriesQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $Age = mysqli_real_escape_string($con, $_POST['Age']);
    $Gender = mysqli_real_escape_string($con, $_POST['Gender']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
    $color = mysqli_real_escape_string($con, $_POST['color']);
    $weight = mysqli_real_escape_string($con, $_POST['weight']);
    $height = mysqli_real_escape_string($con, $_POST['height']);
    $vaccname = mysqli_real_escape_string($con, $_POST['vaccname']);
    $cid = intval($_POST['cid']);
    $subid = intval($_POST['subid']);

    // Handle image upload
    $image1 = uploadFile('image1');
    $image2 = uploadFile('image2');
    $video = uploadFile('video');
    $image3 = uploadFile('image3');

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Insert product into productpet
        $insertProductQuery = "
            INSERT INTO productpet (product_name, description, Age, Gender, image1, image2, video, price, quantity, color, weight, height, sid, cid, subid)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = mysqli_prepare($con, $insertProductQuery);
        mysqli_stmt_bind_param($stmt, "ssssssssdiidiii", $product_name, $description, $Age, $Gender, $image1, $image2, $video, $price, $quantity, $color, $weight, $height, $supplier_id, $cid, $subid);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('SQL Error: ' . mysqli_error($con));
        }

        $petid = mysqli_insert_id($con);

        // Insert into petvacc
        $insertVaccineQuery = "
            INSERT INTO petvacc (petid, vaccname, image3)
            VALUES (?, ?, ?)
        ";
        $stmt = mysqli_prepare($con, $insertVaccineQuery);
        mysqli_stmt_bind_param($stmt, "iss", $petid, $vaccname, $image3);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('SQL Error: ' . mysqli_error($con));
        }

        // Commit transaction
        mysqli_commit($con);
        echo json_encode(['success' => true, 'message' => 'Product added successfully.', 'redirect' => 'supplier_index.php']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }

    mysqli_close($con);
    exit();
}

// Handle AJAX request for subcategories
if (isset($_GET['cid'])) {
    $cid = intval($_GET['cid']);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
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
            padding: 20px;
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
        <h1>Add Pets</h1>
        <form method="POST" enctype="multipart/form-data" id="addProductForm">
            <label for="product_name">Pet Breed:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="Age">Age:</label>
            <input type="text" id="Age" name="Age" required>

            <label for="Gender">Gender:</label>
            <select id="Gender" name="Gender" required>
                <option value="Female">Female</option>
                <option value="Male">Male</option>
            </select>

            <label for="color">Color:</label>
            <input type="text" id="color" name="color" required>

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

            <label for="image1">Product Image 1:</label>
            <input type="file" id="image1" name="image1" accept="image/*">
            <label for="image2">Product Image 2:</label>
            <input type="file" id="image2" name="image2" accept="image/*">
            <label for="video">Product Video:</label>
            <input type="file" id="video" name="video" accept="video/*">
            <label for="image3">Vaccination Certificate:</label>
            <input type="file" id="image3" name="image3" accept="image/*">

            <label for="vaccname">Vaccine Name:</label>
            <input type="text" id="vaccname" name="vaccname" required>

            <label for="price">Price:</label>
            <input type="text" id="price" name="price" required>

            <label for="quantity">Quantity:</label>
            <input type="text" id="quantity" name="quantity" required>

            <label for="weight">Weight:</label>
            <input type="text" id="weight" name="weight" required>

            <label for="height">Height:</label>
            <input type="text" id="height" name="height" required>

            <button type="submit">Add Pet</button>
        </form>
    </div>

    <script>
        function fetchSubcategories() {
            const cid = document.getElementById('cid').value;
            const subidSelect = document.getElementById('subid');

            fetch(`addproductpets.php?cid=${cid}`)
                .then(response => response.json())
                .then(data => {
                    subidSelect.innerHTML = ''; // Clear existing options
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.subid;
                        option.textContent = subcategory.name;
                        subidSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching subcategories:', error));
        }

        document.getElementById('addProductForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('addproductpets.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    swal({
                        title: "Success!",
                        text: result.message,
                        type: "success"
                    }, function() {
                        window.location.href = result.redirect;
                    });
                } else {
                    swal("Error", result.message, "error");
                }
            })
            .catch(error => {
                swal("Error", "Something went wrong!", "error");
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>