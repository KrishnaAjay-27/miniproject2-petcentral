<?php
include("header.php");
include("goback.php");
require('connection.php');

// Redirect to login if user is not logged in
if (!isset($_SESSION['uid'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$userid = $_SESSION['uid'];
$query = "SELECT * FROM registration WHERE lid='$userid'";
$re = mysqli_query($con, $query);
$row = mysqli_fetch_array($re);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#quantity").keyup(function(){
                var pid = $(this).data('pid'); // Ensure this is correctly set
                var qua = $("#quantity").val();
                $.ajax({
                    method: "POST",
                    url: "updatecart.php",
                    data: { qua: qua, pid: pid },
                    dataType: 'html',
                    success: function(html){
                        $('#sub').html(html);
                    }
                });
            });
        });
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #f8f9fa;
            padding: 10px 20px;
            text-align: center;
        }
        .div {
            height: 170px;
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding-top: 50px;
        }
        .div h3 {
            font-size: 30px;
            font-weight: 600;
        }
        .wishlist {
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
            margin: auto;
            background-color: #fff;
        }
        td, th {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        table img {
            height: 100px;
            width: 100px;
        }
        table th {
            background-color: #333;
            color: #fff;
        }
        #back-shop {
            background: #243A6E;
            border-radius: 5px;
            color: #fff;
            font-weight: 600;
            height: 30px; /* Adjusted height for a smaller button */
            padding: 0 15px; /* Adjusted padding for a smaller button */
            font-size: 14px; /* Adjusted font size for a smaller button */
            margin: 20px auto;
            display: inline-block; /* Changed to inline-block to fit button size */
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        #back-shop:hover {
            background: #fc7c7c;
        }
        .empty {
            text-align: center;
            margin: 50px 0;
        }
        .empty img {
            height: 450px;
            width: 450px;
        }
        .empty h3 {
            font-size: 24px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Header content if any -->
    </div>
    <div class="div">
        <h3>My Wishlist</h3>
    </div>
    <div class="content">
        <div class="wishlist">
            <a href="shops.php" id="back-shop">Back to Shopping</a>
        </div>
        <?php
        $sql = "SELECT tbl_wishlist.wishlist_id, tbl_wishlist.product_id, tbl_wishlist.price, product_dog.name, product_dog.image1 
                FROM tbl_wishlist 
                JOIN product_dog ON tbl_wishlist.product_id = product_dog.product_id 
                WHERE lid = '$userid'";
        $res = mysqli_query($con, $sql);
        if (mysqli_num_rows($res) > 0) {
        ?>
        <div class="wishlist">
            <table>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Unit Price</th>
                    <th>Remove</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($res)) { ?>
                <tr>
                    <td><img src="uploads/<?php echo $row['image1']; ?>" alt="Product Image"></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><a href="delete_item_wishlist.php?id=<?php echo $row['wishlist_id']; ?>"><i class="fa fa-trash-o" style="color:black;"></i></a></td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <?php
        } else {
        ?>
        <div class="empty">
            <img src="images/cart.gif" alt="Empty Wishlist">
            <h3>Your Wishlist is Empty..!!</h3>
        </div>
        <?php } ?>
    </div>
    <?php include("footer.php"); ?>
</body>
</html>
