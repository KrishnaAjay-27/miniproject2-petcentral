<?php
    include("header.php");

    include 'message-box.php';
    require('connection.php');
    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $query="SELECT p.*, v.price, v.quantity, tp.brand, tp.species
                FROM product_dog p 
                LEFT JOIN product_variants v ON p.product_id = v.product_id 
                LEFT JOIN tblpro tp ON p.product_id = tp.product_id
                WHERE p.product_id='$id' 
                LIMIT 1";
        $re=mysqli_query($con,$query);
        $count=mysqli_num_rows($re);
        $row=mysqli_fetch_array($re);

        // Query to fetch product details along with supplier code
        $query1= "
            SELECT p.*, s.supplier_code, sub.name AS subcategory_name, tp.brand, tp.species
            FROM product_dog p
            INNER JOIN s_registration s ON p.sid = s.sid
            LEFT JOIN subcategory sub ON p.subid = sub.subid
            LEFT JOIN tblpro tp ON p.product_id = tp.product_id
            WHERE p.product_id='$id'
        ";
        $re1 = mysqli_query($con, $query1);
        $count1 = mysqli_num_rows($re1);
        $row1 = mysqli_fetch_array($re1);
    }

    // Fetch product details
    $product_id = $_GET['id']; // Get the product ID from the URL
    $query = "SELECT * FROM product_variants WHERE product_id = '$product_id'";
    $result = mysqli_query($con, $query);
    $variant = mysqli_fetch_assoc($result);

    // Get the original quantity of the selected variant
    $original_quantity = $variant['quantity'];
   
     // Adjust this based on your database structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/medium-zoom/1.0.6/medium-zoom.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .product-container {
            display: flex;
            gap: 40px;
        }

        .product-images {
            flex: 1;
        }

        .product-image-main {
            width: 100%;
            height: 400px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .product-image-main img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .product-image-thumbnails {
            display: flex;
            gap: 10px;
        }

        .product-image-thumbnail {
            width: 80px;
            height: 80px;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .product-image-thumbnail.active {
            border-color: #5344db;
        }

        .product-image-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details {
            flex: 1;
        }

        .product-title h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }

        .product-meta p {
            margin: 5px 0;
            color: #666;
        }

        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #5344db;
            margin: 20px 0;
        }

        .product-description {
            margin: 20px 0;
        }

        .product-description h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .product-options {
            margin: 20px 0;
        }

        .product-options label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        #select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .quantity-input label {
            margin-right: 10px;
        }

        .quantity-input input {
            width: 60px;
            padding: 5px;
            font-size: 16px;
            text-align: center;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .product-actions button {
            flex: 1;
            padding: 12px 20px;
            font-size: 16px;
            color: white;
            background-color: #5344db;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .product-actions button:hover {
            background-color: #3f32a4;
        }

        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }
        }

        .full-description {
            max-height: 3em; /* Limit height to 2 lines */
            overflow: hidden; /* Hide overflow */
            position: relative; /* Position for pseudo-element */
        }

        .full-description::after {
            content: '...'; /* Add ellipsis */
            position: absolute;
            bottom: 0;
            right: 0;
            background: white; /* Background to cover text */
            padding-left: 5px; /* Space before ellipsis */
        }

        .description {
            display: none; /* Initially hide the additional description */
        }

        .view-more {
            color: blue;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-container">
            <div class="product-images">
                <div class="product-image-main">
                    <img src="uploads/<?php echo $row['image1']; ?>" alt="Product Image 1" id="main-image" class="zoomable-image">
                </div>
                <div class="product-image-thumbnails">
                    <div class="product-image-thumbnail active" data-image="uploads/<?php echo $row['image1']; ?>">
                        <img src="uploads/<?php echo $row['image1']; ?>" alt="Product Thumbnail 1">
                    </div>
                    <?php if ($row['image2']): ?>
                    <div class="product-image-thumbnail" data-image="uploads/<?php echo $row['image2']; ?>">
                        <img src="uploads/<?php echo $row['image2']; ?>" alt="Product Thumbnail 2">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="product-details">
                <div class="product-title">
                    <h1><?php echo $row['name']; ?></h1>
                </div>
                <div class="product-meta">
                <p><strong><?php echo $row['species']; ?></strong></p>
                    <?php if ($row1 && isset($row1['subcategory_name'])): ?>
                        <p> <strong> <?php echo htmlspecialchars($row1['subcategory_name']); ?></strong></p>
                    <?php endif; ?>
                    <p>Brand: <?php echo $row['brand']; ?></p>
                    <?php if ($row1 && isset($row1['supplier_code'])): ?>
                        <p>Supplier Code: <?php echo htmlspecialchars($row1['supplier_code']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="product-price">
                    <span id="price"><i class='fa fa-rupee'></i><?php echo $row['price']; ?></span>
                </div>
                <div class="product-stock">
                    <?php if($row['quantity'] > 0): ?>
                        <p style="color:green;">In Stock</p>
                    <?php else: ?>
                        <p style="color:red;">Out of Stock</p>
                    <?php endif; ?>
                </div>
                <div class="product-description">
    <h3>Description</h3>
    <div class="full-description" id="description">
        <p><?php echo $row['description']; ?></p>
    </div>
    <span class="view-more" onclick="toggleDescription()">View More</span>
</div>
                <form method="post">
                    <div class="product-options">
                        <label for="select">Choose Size:</label>
                        <select id="select" name="size">
                            <?php
                            $que = "SELECT * FROM product_variants WHERE product_id='$id'";
                            $res = mysqli_query($con, $que);
                            while ($row1 = mysqli_fetch_array($res)) {
                                echo "<option value='".$row1['variant_id']."'>".$row1['size']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="quantity-input">
                        <label for="num">Quantity:</label>
                        <input type="number" id="num" name="num" min="1" max="<?php echo $original_quantity; ?>" value="1">
                    </div>
                    <div class="product-actions">
                        <button type="submit" id="cart" name="sub"><i class='fa fa-shopping-cart'></i> Add to cart</button>
                        <button type="submit" name="wishlist"><i class='fa fa-heart'></i> Add to wishlist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
if (isset($_POST['sub']) || isset($_POST['wishlist'])) {
    $pid = $_GET['id'];
    $we = isset($_POST['size']) ? $_POST['size'] : null;
    $qu = isset($_POST['num']) ? $_POST['num'] : 1;
    require('connection.php');
    
    if (isset($_SESSION['uid'])) {
        $userid = $_SESSION['uid'];
        
        if (isset($_POST['sub'])) {
            // Add to cart functionality (existing code)
            $query = "SELECT * FROM product_variants WHERE variant_id='$we'";
            $res = mysqli_query($con, $query);
            $row4 = mysqli_fetch_array($res);
            $price = $row4['price'];
            $weight = $row4['variant_id'];
        
// Query to check if the item already exists in the cart

// Function declaration check to avoid redeclaration errors
if (!function_exists('showAlert1')) {
    function showAlert1($type, $message, $icon, $color) {
        echo "<script>
            alert('$message'); // Simple alert for demonstration
            // You can customize this further if you are using a specific alert library
        </script>";
    }
}

// Query to check if the item already exists in the cart
$c = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tbl_cart WHERE product_id='$pid' AND lid='$userid'"));

if ($c > 0) {
    // Item already exists in the cart
    showAlert1('warning', 'Item already exists in the cart', 'fa-exclamation', '#FF9800');
} else {
    // Item does not exist, proceed to insert
    if ($we) {
        $sql = "INSERT INTO tbl_cart(lid, product_id, size, quantity, price) VALUES('$userid', '$pid', '$we', '$qu', '$price')";
    } else {
        $sql = "INSERT INTO tbl_cart(lid, product_id, size, quantity, price) VALUES('$userid', '$pid', '$weight', '$qu', '$price')";
    }
    $re = mysqli_query($con, $sql);

    // Check if the insert was successful
    if ($re) {
        showAlert1('check', 'Item added to cart', 'fa-check', '#4CAF50');
    } else {
        showAlert1('error', 'Failed to add item to cart', 'fa-exclamation-triangle', '#F44336');
    }
}
?>
<script>
    setTimeout(function(){
        window.location.href = 'shops.php';
    }, 5000);
</script>
<?php



        } elseif (isset($_POST['wishlist'])) {
            // Add to wishlist functionality
            $que = "SELECT * FROM product_variants WHERE variant_id='$we'";
            $res = mysqli_query($con, $que);
            $row4 = mysqli_fetch_array($res);
            $price = $row4['price'];

            // Check if the item is already in the wishlist
            $check_query = "SELECT * FROM tbl_wishlist WHERE product_id='$pid' AND lid='$userid'";
            $check_result = mysqli_query($con, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                showAlert2('warning', 'Item already in wishlist', 'exclamation-circle', '#FFFFCC');
            } else {
                $sql = "INSERT INTO tbl_wishlist(product_id, lid, price, PostedDate) VALUES('$pid', '$userid', '$price', NOW())";
                $re = mysqli_query($con, $sql);
                
                if ($re) {
                    showAlert1('check', 'Item added to Wishlist', 'fa-check', '#4CAF50');
                } else {
                    showAlert2('error', 'Failed to add item to wishlist', 'exclamation-circle', '#FF0000');
                }
            }
            ?>
            <script>
                setTimeout(function(){
                    window.location.href = 'shops.php';
                }, 5000);
            </script>
            <?php
        }
    } else {
        showAlert2('warning', 'Please log in to continue', 'exclamation-circle', '#FFFFCC');
        ?>
        <script>
            setTimeout(function(){
                window.location.href = 'login.php';
            }, 5000);
        </script>
        <?php
    }
}
?>
</body>
</html>
<?php
   include("footer.php");
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainImage = document.getElementById('main-image');
        const thumbnails = document.querySelectorAll('.product-image-thumbnail');
        
        const zoom = mediumZoom('.zoomable-image', {
            margin: 24,
            background: '#000000',
            scrollOffset: 0,
        });

        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const imageUrl = this.getAttribute('data-image');
                mainImage.src = imageUrl;
                
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                zoom.detach();
                zoom.attach('.zoomable-image');
            });
        });
    });
</script>
<script>
$(document).ready(function() {
    $("#select").on("change", function(){
        var variantId = $(this).val();
        $.ajax({
            type: "POST",
            url: "priceview.php",
            data: { sub: variantId },
            success: function(result){
                $('#price').html(result);
            }
        });
    });
});
</script>
<script>
    
    function toggleDescription() {
    const fullDescription = document.querySelector('.full-description');
    const viewMoreButton = document.querySelector('.view-more');

    // Check the current state of the description
    if (fullDescription.style.maxHeight === 'none') {
        // Collapse the description
        fullDescription.style.maxHeight = '3em'; // Limit height to 2 lines
        viewMoreButton.textContent = 'View More'; // Change button text
    } else {
        // Expand the description
        fullDescription.style.maxHeight = 'none'; // Remove height limit
        viewMoreButton.textContent = 'View Less'; // Change button text back
    }
}

</script>