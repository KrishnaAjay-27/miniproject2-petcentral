<?php
include("connection.php");
include("header.php");
// session_start();

if (!isset($_SESSION['uid'])) {
    echo "<script>
        Swal.fire({
            title: 'Access Denied',
            text: 'Please log in to view your wishlist.',
            icon: 'warning',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href='login.php';
            }
        });
    </script>";
    exit();
}

$userid = $_SESSION['uid'];

if (isset($_GET['delete'])) {
    $wishlist_id = $_GET['delete'];
    $delete_query = "DELETE FROM tbl_wishlist WHERE wishlist_id = '$wishlist_id' AND lid = '$userid'";
    if (mysqli_query($con, $delete_query)) {
        $success_message = "Poof! The item has vanished from your wishlist. ✨";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: white;
            color: black;
            padding: 20px 0;
            position: relative;
        }
        .header h1 {
            margin: 0;
            text-align: center;
            
        }
        .back-arrow {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 24px;
            text-decoration: none;
        }
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .wishlist-item {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .wishlist-item-info {
            padding: 15px;
        }
        .wishlist-item-name {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .wishlist-item-price {
            color: #243A6E;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .wishlist-item-actions {
            display: flex;
            justify-content: space-between;
        }
        .view-more, .remove-item {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .view-more {
            background-color: #243A6E;
        }
        .remove-item {
            background-color: #dc3545;
        }
        .empty-wishlist {
            text-align: center;
            margin-top: 50px;
        }
        .empty-wishlist img {
            max-width: 300px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="header">
       
        <h1>My Wishlist</h1>
    </div>
    <div class="container">
        <?php if (isset($success_message)): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $success_message; ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
        <?php endif; ?>

        <?php
        // Fetch wishlist items from both product_dog and productpet tables
        $sql = "
            SELECT 
                tbl_wishlist.wishlist_id, 
                tbl_wishlist.product_id, 
                tbl_wishlist.price, 
                product_dog.name AS product_name, 
                product_dog.image1, 
                'dog' AS type 
            FROM tbl_wishlist 
            JOIN product_dog ON tbl_wishlist.product_id = product_dog.product_id 
            WHERE lid = '$userid'
            
            UNION ALL
            
            SELECT 
                tbl_wishlist.wishlist_id, 
                tbl_wishlist.petid AS product_id, 
                tbl_wishlist.price, 
                productpet.product_name, 
                productpet.image1, 
                'pet' AS type 
            FROM tbl_wishlist 
            JOIN productpet ON tbl_wishlist.petid = productpet.petid 
            WHERE lid = '$userid'
        ";

        $res = mysqli_query($con, $sql);
        if (mysqli_num_rows($res) > 0):
        ?>
        <div class="wishlist-grid">
            <?php while ($row = mysqli_fetch_array($res)): ?>
            <div class="wishlist-item">
                <img src="uploads/<?php echo $row['image1']; ?>" alt="<?php echo $row['product_name']; ?>">
                <div class="wishlist-item-info">
                    <div class="wishlist-item-name"><?php echo $row['product_name']; ?></div>
                    <div class="wishlist-item-price">₹<?php echo number_format($row['price'], 2); ?></div>
                    <div class="wishlist-item-actions">
                        <?php if ($row['type'] === 'dog'): ?>
                            <a href="viewpro.php?id=<?php echo $row['product_id']; ?>" class="view-more">View More</a>
                        <?php else: ?>
                            <a href="viewpets.php?id=<?php echo $row['product_id']; ?>" class="view-more">View More</a>
                        <?php endif; ?>
                        <a href="#" class="remove-item" onclick="confirmRemove(<?php echo $row['wishlist_id']; ?>); return false;">Remove</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php else: ?>
        <div class="empty-wishlist">
            <img src="images/cart.gif" alt="Empty Wishlist">
            <h3>Your Wishlist is Empty!</h3>
            <p>Add items to your wishlist to save them for later.</p>
        </div>
        <?php endif; ?>
    </div>
    <script>
        function confirmRemove(wishlistId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove this item from your wishlist?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete=' + wishlistId;
                }
            });
        }
    </script>
</body>
</html><?php
   include("footer.php");
?>