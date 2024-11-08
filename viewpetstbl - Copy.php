<?php
include('supplierindex.php');
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

// Handle activation/deactivation
if (isset($_POST['toggle_status']) && isset($_POST['product_id']) && isset($_POST['current_status'])) {
    $product_id = intval($_POST['product_id']);
    $new_status = ($_POST['current_status'] == 0) ? 1 : 0;
    $updateQuery = "UPDATE product_dog SET status = $new_status WHERE product_id = $product_id AND sid = $supplier_id";
    if (mysqli_query($con, $updateQuery)) {
        echo "<script>alert('Product status updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating product status: " . mysqli_error($con) . "');</script>";
    }
}

// Fetch products for this supplier
$productsQuery = "SELECT pd.*, c.name as category_name, sc.name as subcategory_name, tp.Gender, tp.Age
                  FROM productpet pd
                  JOIN category c ON pd.cid = c.cid
                  JOIN subcategory sc ON pd.subid = sc.subid
                  LEFT JOIN productpet tp ON pd.petid = tp.petid
                  WHERE pd.sid = $supplier_id
                  ORDER BY pd.petid DESC";
$productsResult = mysqli_query($con, $productsQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Supplier Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
        margin-left: 270px; /* Space for the sidebar */
        margin-right: auto; /* Centering */
        margin-top: 20px; /* Optional: Add some top margin */
        padding: 20px;
        max-width: 1200px; /* Set a max width */
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px; /* Optional: Add rounded corners */
    }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        img {
            max-width: 100px;
            height: auto;
        }
        .status-active {
            color: green;
        }
        .status-inactive {
            color: red;
        }
        .edit-btn, .toggle-btn {
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 5px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .view-variants-btn {
            padding: 5px 10px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .description-short {
            display: inline;
        }
        .description-full {
            display: none;
        }
        .view-more {
            color: #4CAF50;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <h1> Pets</h1>
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Species</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sno = 1;
                while ($product = mysqli_fetch_assoc($productsResult)): 
                    // Split the description into short and full parts
                    $shortDescription = substr($product['description'], 0, 50);
                    $fullDescription = substr($product['description'], 50);
                ?>
                    <tr>
                        <td><?php echo $sno++; ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><img src="uploads/<?php echo $product['image1']; ?>" alt="Product Image"></td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['subcategory_name']); ?></td>
                        <td>
                            <span class="description-short"><?php echo htmlspecialchars($shortDescription); ?></span>
                            <span class="description-full"><?php echo htmlspecialchars($fullDescription); ?></span>
                            <?php if (strlen($product['description']) > 50): ?>
                                <span class="view-more">View More</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['Age']); ?></td>
                        <td><?php echo htmlspecialchars($product['Gender']); ?></td>
                        <td class="<?php echo $product['status'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $product['status'] == 0 ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td>
                            
                            <!-- <a href="edit_product.php?pid=<?php echo $product['petid']; ?>" class="edit-btn">Edit</a> -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['petid']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $product['status']; ?>">
                                <button type="submit" name="toggle_status" class="toggle-btn">
                                    <?php echo $product['status'] == 0 ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="variantsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Product Variants</h2>
            <div id="variantsContent"></div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("variantsModal");
        var span = document.getElementsByClassName("close")[0];

        function viewVariants(productId) {
            fetch(`get_variants.php?product_id=${productId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("variantsContent").innerHTML = data;
                    modal.style.display = "block";
                });
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // View More functionality
        document.querySelectorAll('.view-more').forEach(button => {
            button.addEventListener('click', function() {
                const shortDesc = this.previousElementSibling.previousElementSibling;
                const fullDesc = this.previousElementSibling;
                
                if (fullDesc.style.display === 'none') {
                    fullDesc.style.display = 'inline';
                    shortDesc.style.display = 'none';
                    this.textContent = 'View Less';
                } else {
                    fullDesc.style.display = 'none';
                    shortDesc.style.display = 'inline';
                    this.textContent = 'View More';
                }
            });
        });
    </script>
</body>
</html>
