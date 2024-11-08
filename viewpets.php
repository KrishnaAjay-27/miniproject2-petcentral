<?php
    include("header.php");

    include 'message-box.php';
    require('connection.php');
    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $query = "
        SELECT p.product_name, p.description, p.Age, p.Gender, p.image1, p.image2, p.video, 
               p.price, p.quantity, vac.vaccname, vac.image3, s.supplier_code, p.weight
        FROM productpet p 
        LEFT JOIN petvacc vac ON p.petid = vac.petid  -- Join with petvacc table
        LEFT JOIN s_registration s ON p.sid = s.sid  -- Join with s_registration table
        WHERE p.petid = ? 
        LIMIT 1
    ";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "Pet not found.";
        exit();
    }

    mysqli_stmt_close($stmt);

        // Query to fetch product details along with supplier code
        $query1 = "
    SELECT p.*, s.supplier_code, sub.name AS subcategory_name, tp.Gender, tp.Age, 
           vac.vaccname, vac.image3
    FROM productpet p
    INNER JOIN s_registration s ON p.sid = s.sid
    LEFT JOIN subcategory sub ON p.subid = sub.subid
    LEFT JOIN productpet tp ON p.petid = tp.petid
    LEFT JOIN petvacc vac ON tp.petid = vac.petid  -- Join with petvacc table
    WHERE p.petid =$id
";
        $re1 = mysqli_query($con, $query1);
        $count1 = mysqli_num_rows($re1);
        $row1 = mysqli_fetch_array($re1);
    }

    // Fetch product details
    $petid = $_GET['id']; // Get the product ID from the URL
    $query = "SELECT * FROM productpet WHERE petid = '$petid'";
    $result = mysqli_query($con, $query);
    $variant = mysqli_fetch_assoc($result);

    // Get the original quantity of the selected variant
    $original_quantity = $variant['quantity']; // Adjust this based on your database structure
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['product_name']); ?> - Product Details</title>
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

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-container">
            <div class="product-images">
                <div class="product-image-main">
                    <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="Product Image 1" id="main-image" class="zoomable-image">
                </div>
                <div class="product-image-thumbnails">
                    <div class="product-image-thumbnail active" data-image="uploads/<?php echo htmlspecialchars($row['image1']); ?>">
                        <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="Product Thumbnail 1">
                    </div>
                    <?php if ($row['image2']): ?>
                    <div class="product-image-thumbnail" data-image="uploads/<?php echo htmlspecialchars($row['image2']); ?>">
                        <img src="uploads/<?php echo htmlspecialchars($row['image2']); ?>" alt="Product Thumbnail 2">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="product-details">
                <div class="product-title">
                    <h1><?php echo htmlspecialchars($row['product_name']); ?></h1>
                </div>
                <div class="product-meta">
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($row['Age']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['Gender']); ?></p>
                    <p><strong>Supplier Code:</strong> <?php echo htmlspecialchars($row['supplier_code']); ?></p>
                    <p><strong>Vaccination:</strong> <?php echo htmlspecialchars($row['vaccname']); ?></p>
                </div>
                <div class="product-price">
                    <span id="price"><i class='fa fa-rupee'></i><?php echo htmlspecialchars($row['price']); ?></span>
                </div>
                <div class="product-stock">
                    <?php if ($row['quantity'] > 0): ?>
                        <p style="color:green;">In Stock</p>
                    <?php else: ?>
                        <p style="color:red;">Out of Stock</p>
                    <?php endif; ?>
                </div>
                <div class="product-description">
                    <h3>Description</h3>
                    <div class="full-description" id="description" style="max-height: 3em; overflow: hidden;">
                        <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    </div>
                    <span class="view-more" onclick="toggleDescription()">View More</span>
                    <h3>Vaccination Certificate</h3>
                    <span class="view-certificate" onclick="openModal('certificateModal')">View Certificate</span>
                </div>
                <h3>Video</h3>
                <span class="view-video" onclick="openModal('videoModal', '<?php echo htmlspecialchars($row['video']); ?>')">Watch Video</span>
                <form method="post">
                    <div class="quantity-input">
                        <label for="num">Quantity:</label>
                        <input type="number" id="num" name="num" min="1" max="<?php echo $row['quantity']; ?>" value="1">
                    </div>
                    <div class="product-actions">
                        <button type="submit" id="cart" name="sub"><i class='fa fa-shopping-cart'></i> Add to cart</button>
                        <button type="submit" name="wishlist"><i class='fa fa-heart'></i> Add to wishlist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Vaccination Certificate -->
    <div id="certificateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('certificateModal')">&times;</span>
            <h2>Vaccination Certificate</h2>
            <img src="uploads/<?php echo htmlspecialchars($row['image3']); ?>" alt="Vaccination Certificate" style="max-width: 100%; height: auto;">
        </div>
    </div>

    <!-- Modal for Video -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('videoModal')">&times;</span>
            <h2>Watch Video</h2>
            <iframe id="videoFrame" width="100%" height="315" src="" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

<?php
if (isset($_POST['sub']) || isset($_POST['wishlist'])) {
    $pid = $_GET['id'];
    $qu = isset($_POST['num']) ? $_POST['num'] : 1;
    require('connection.php');

    if (isset($_SESSION['uid'])) {
        $userid = $_SESSION['uid'];

        if (isset($_POST['sub'])) {
            // Add to cart functionality
            $price = $row['price'];
            $weight = $row['weight']; // Get the weight from the productpet table

            // Check if the item is already in the cart
            $c = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tbl_cart WHERE petid='$pid' AND lid='$userid'"));

            if ($c > 0) {
                // Item already in cart, do not add again
                showAlert2('warning', 'Item already in cart, please update quantity in My Cart', 'exclamation-circle', '#FFFFCC');
            } else {
                // Insert a new cart item
                $sql = "INSERT INTO tbl_cart(lid, petid, quantity, price, size) VALUES('$userid', '$pid', '$qu', '$price', '$weight')";
                $re = mysqli_query($con, $sql);
                showAlert1('check', 'Item added to cart', 'fa-check', '#4CAF50');
                ?><script>
                    setTimeout(function() {
                        window.location.href = 'shops.php';
                    }, 5000);
                </script><?php
            }
        } elseif (isset($_POST['wishlist'])) {
            // Add to wishlist functionality
            $price = $row['price'];

            // Check if the item is already in the wishlist
            $check_query = "SELECT * FROM tbl_wishlist WHERE petid='$pid' AND lid='$userid'";
            $check_result = mysqli_query($con, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                showAlert2('warning', 'Item already in wishlist', 'exclamation-circle', '#FFFFCC');
            } else {
                $sql = "INSERT INTO tbl_wishlist(petid, lid, price, PostedDate) VALUES('$pid', '$userid', '$price', NOW())";
                $re = mysqli_query($con, $sql);

                if ($re) {
                    showAlert1('check', 'Item added to Wishlist', 'fa-check', '#4CAF50');
                } else {
                    showAlert2('error', 'Failed to add item to wishlist', 'exclamation-circle', '#FF0000');
                }
            }
            ?><script>
                setTimeout(function() {
                    window.location.href = 'displaydogs.php';
                }, 5000);
            </script><?php
        }
    } else {
        showAlert2('warning', 'Please log in to continue', 'exclamation-circle', '#FFFFCC');
        ?><script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 5000);
        </script><?php
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

    function toggleDescription() {
        const description = document.getElementById('description');
        const viewMoreButton = document.querySelector('.view-more');

        if (description.style.maxHeight === 'none') {
            description.style.maxHeight = '3em'; // Limit height to 2 lines
            viewMoreButton.textContent = 'View More'; // Change button text
        } else {
            description.style.maxHeight = 'none'; // Show full description
            viewMoreButton.textContent = 'View Less'; // Change button text
        }
    }

    function openModal(modalId, videoUrl = '') {
        document.getElementById(modalId).style.display = "block";
        if (modalId === 'videoModal') {
            document.getElementById('videoFrame').src = videoUrl; // Set video URL
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
        if (modalId === 'videoModal') {
            document.getElementById('videoFrame').src = ''; // Clear video URL to stop playback
        }
    }

    // Close modals when clicking outside of them
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal('certificateModal');
            closeModal('videoModal');
        }
    }
</script>
