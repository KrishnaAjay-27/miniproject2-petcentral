<?php
include("header.php");
require('connection.php');

if(isset($_SESSION['uid'])) {
    $userid = $_SESSION['uid'];
} else {
    echo "<script>window.location.href='login.php';</script>";
}

// Query for dogs
$sql_dogs = "
    SELECT 
        tbl_cart.cart_id, 
        tbl_cart.product_id, 
        tbl_cart.quantity, 
        tbl_cart.price, 
        product_dog.name AS product_name, 
        product_dog.image1 AS product_image, 
        product_dog.description AS product_description, 
        'dog' AS type 
    FROM 
        tbl_cart 
    JOIN 
        product_dog ON tbl_cart.product_id = product_dog.product_id 
    WHERE 
        tbl_cart.lid = '$userid'
";
$res_dogs = mysqli_query($con, $sql_dogs);

// Query for pets
$sql_pets = "
    SELECT 
        tbl_cart.cart_id, 
        tbl_cart.petid AS product_id, 
        tbl_cart.quantity, 
        tbl_cart.price, 
        productpet.product_name AS product_name, 
        productpet.image1 AS product_image, 
        productpet.description AS product_description, 
        'pet' AS type 
    FROM 
        tbl_cart 
    JOIN 
        productpet ON tbl_cart.petid = productpet.petid 
    WHERE 
        tbl_cart.lid = '$userid'
";
$res_pets = mysqli_query($con, $sql_pets);

if (!$res_dogs || !$res_pets) {
    die("Query failed: " . mysqli_error($con));
}

$subtotal_dogs = 0;
$subtotal_pets = 0;
$shipping = 50;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
}

.div {
    margin-top: 0;
    height: 170px;
    width: 100%;
    background-color: #f0f0f0;
}

.div h3 {
    padding-top: 100px;
    margin-left: 100px;
    font-size: 30px;
    font-weight: 600;
}

.cart {
    display: grid;
    grid-template-columns: 2fr 1fr; /* Main content on the left, price details on the right */
    gap: 20px;
    padding: 20px;
}

.video {
    margin-left: 500px;
    margin-top: 10px;
    margin-bottom: 30px;
    height: 450px;
    width: 450px;
}

.video img {
    height: 450px;
    width: 450px;
}

.empty {
    margin-left: 150px;
    margin-top: -50px;
    font-size: 24px;
}

.card1 {
    margin: 0 auto;
    width: 100%;
    margin-bottom: 50px;
}

.card1 table {
    width: 100%;
    margin: 10px 0;
    border-collapse: collapse;
}

/* Main content table */
table {
    width: 100%;
    margin-top: 50px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

table th {
    background-color: #333;
    color: #fff;
    padding: 10px;
    text-transform: uppercase;
}

table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

table img {
    height: 100px;
    width: 100px;
    border-radius: 8px;
}

.quantity {
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity input {
    border: none;
    padding: 8px;
    width: 40px;
    text-align: center;
    background-color: #f0f0f0;
}

.quantity button {
    width: 30px;
    height: 30px;
    border: none;
    background-color: #4CAF50;
    color: white;
    font-size: 16px;
    border-radius: 50%;
    cursor: pointer;
}

button {
    padding: 8px 16px;
    background-color: #333;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background-color: #555;
}

h3 {
    font-size: 24px;
    text-align: center;
    padding: 20px;
    background-color: #fff;
    border-bottom: 2px solid #ddd;
    text-transform: uppercase;
    letter-spacing: 1px;
}

#back-shop, #checkout {
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 5px;
    cursor: pointer;
}

#back-shop {
    background-color: red;
    color: white;
}

#checkout {
    background-color: black;
    color: white;
}

#back-shop:hover, #checkout:hover {
    background-color: #fc7c7c;
}

.card {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 20px;
    background-color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Sidebar for price details */
.price-details {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 20px;
    background-color: #fff;
    margin-top: 50px; /* Aligns with the table */
}

.price-details .card-header {
    background-color: #f5f5f5;
    padding: 10px;
    font-weight: bold;
    text-align: center;
}

.price-details .card-body {
    padding: 10px;
}

.subtotal, .shipping, .total {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.subtotal-label, .shipping-label, .total-label {
    font-weight: bold;
}

.subtotal-value, .shipping-value, .total-value {
    font-weight: bold;
    color: #333;
}

.total {
    border-top: 2px solid #ddd;
    padding-top: 15px;
    
}

#checkout {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #333;
    color: #fff;
    border-radius: 5px;
    text-align: center;
    text-transform: uppercase;
}

#checkout:hover {
    background-color: #555;
}

.save-icon {
    font-size: 10px;
    color: red;
    cursor: pointer;
    margin-left: 10px;
    transition: color 0.3s;
}

.save-icon:hover {
    color: #333;
}
    </style>
</head>
<body>
    <div class="div">
        <h3>MY CART</h3>
    </div>

    <?php
    if (mysqli_num_rows($res_dogs) == 0 && mysqli_num_rows($res_pets) == 0) {
    ?>
    <div class="video">
        <img src="cart.gif" alt="error"/>
        <h3 class="empty">Your Cart is Empty..!!</h3>
    </div>
    <?php
    } else {
    ?>
    <div class="cart">
        <!-- Table for Dogs -->
        <div class="card1">
            <h3>Products</h3>
            <table>
                <tr>
                    <th>Product Image</th>
                    <th>Product Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Remove</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($res_dogs)) {
                    $prototal = $row['price'] * $row['quantity'];
                    $subtotal_dogs += $prototal;
                ?>
                <tr>
                    <td><img src='uploads/<?php echo $row['product_image']; ?>' alt='not found'/></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td>
                    <div class="quantity">
        <a href='minusitem.php?id=<?php echo $row['cart_id']; ?>'><button class="minus-btn" type="button">-</button></a>
        <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" max="<?php echo $row['quantity']; ?>" data-original-quantity="<?php echo $row['quantity']; ?>">
        <a href='plusitem.php?id=<?php echo $row['cart_id']; ?>'><button class="plus-btn" type="button">+</button></a>
    </div>
                    </td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $prototal; ?></td>
                    <td>
    <a href='removeitem.php?id=<?php echo $row['cart_id']; ?>'><button>Remove</button></a>
    <a href='#' onclick="saveForLater(<?php echo $row['cart_id']; ?>, '<?php echo $row['type']; ?>', <?php echo $row['product_id']; ?>, <?php echo $row['price']; ?>, <?php echo $row['quantity']; ?>)">
        <i class="fas fa-bookmark save-icon" title="Save for Later">Saveforlater</i>
    </a>
</td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="4"><strong>Total Amount:</strong></td>
                    <td><strong><?php echo $subtotal_dogs; ?></strong></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- Table for Pets -->
        <div class="card1">
            <h3>Pets</h3>
            <table>
                <tr>
                    <th>Product Image</th>
                    <th>Product Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Remove</th>
                  
                </tr>
                <?php while ($row = mysqli_fetch_array($res_pets)) {
                    $prototal = $row['price'] * $row['quantity'];
                    $subtotal_pets += $prototal;
                ?>
                <tr>
                    <td><img src='uploads/<?php echo $row['product_image']; ?>' alt='not found'/></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td>
                    <div class="quantity">
            <a href='minusitem.php?id=<?php echo $row['cart_id']; ?>'><button class="minus-btn" type="button">-</button></a>
            <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" max="<?php echo $row['quantity']; ?>" data-original-quantity="<?php echo $row['quantity']; ?>">
            <a href='plusitem.php?id=<?php echo $row['cart_id']; ?>'><button class="plus-btn" type="button">+</button></a>
        </div>
                    </td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $prototal; ?></td>
                    <td>
    <a href='removeitem.php?id=<?php echo $row['cart_id']; ?>'><button>Remove</button></a>
    <a href='#' onclick="saveForLater(<?php echo $row['cart_id']; ?>, '<?php echo $row['type']; ?>', <?php echo $row['product_id']; ?>, <?php echo $row['price']; ?>, <?php echo $row['quantity']; ?>)">
        <i class="fas fa-+bookmark save-icon" title="Save for Later">Saveforlater</i>
    </a>
</td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="4"><strong>Total Amount:</strong></td>
                    <td><strong><?php echo $subtotal_pets; ?></strong></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- Price Details -->
        <div class="price-details card">
        <div class="card-header">
            <h3 align="center">PRICE DETAILS</h3>
            </div>
          
                    
                    <div class="card-body">
                        <div class="subtotal">
                            <div class="subtotal-label">Subtotal (products):</div>
                            <div class="subtotal-value"><i class='fa fa-rupee'></i> <?php echo $subtotal_dogs; ?></div>
                        </div>
                        <div class="subtotal">
                            <div class="subtotal-label">Subtotal (Pets):</div>
                            <div class="subtotal-value"><i class='fa fa-rupee'></i> <?php echo $subtotal_pets; ?></div>
                        </div>

                        <?php
                        $total = $subtotal_dogs + $subtotal_pets;
                        if ($total < 500) {
                        ?>
                        <div class="shipping">
                            <div class="shipping-label">Shipping Fee:</div>
                            <div class="shipping-value"><i class='fa fa-rupee'></i> <?php echo $shipping; ?></div>
                        </div>
                        <?php
                            $total += $shipping;
                        }
                        ?>

                        <hr>
                        <div class="total">
                            <div class="total-label">Total:</div>
                            <div class="total-value"><i class='fa fa-rupee'></i> <?php echo $total; ?></div>
                        </div>
                    </div>
                </div>
                <br><br><br>
                <form method="post" action="place_order.php">
                    <input type="hidden" name="total" value="<?php echo $total; ?>"/>
                    <?php
                    $cart_ids = [];
                    mysqli_data_seek($res_dogs, 0); // Reset pointer for dogs
                    while ($row = mysqli_fetch_array($res_dogs)) {
                        $cart_ids[] = $row['cart_id'];
                    }
                    mysqli_data_seek($res_pets, 0); // Reset pointer for pets
                    while ($row = mysqli_fetch_array($res_pets)) {
                        $cart_ids[] = $row['cart_id'];
                    }
                    echo '<input type="hidden" name="cart_ids" value="' . implode(',', $cart_ids) . '"/>';
                    ?>
                    <button type="submit" name='checkout' id='checkout'>Place Order</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    }
    ?>

    <script>
        // Quantity plus and minus buttons functionality
        <script>
    //Quantity plus and minus buttons functionality
    var minusButton = document.querySelectorAll('.minus-btn');
    var plusButton = document.querySelectorAll('.plus-btn');
    var quantityInput = document.querySelectorAll('.quantity input');

    for (var i = 0; i < minusButton.length; i++) {
        minusButton[i].addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            var value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    }

    for (var i = 0; i < plusButton.length; i++) {
        plusButton[i].addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            var value = parseInt(input.value);
            var maxQuantity = parseInt(input.getAttribute('data-original-quantity')); // Get the original quantity from the data attribute

            if (value < maxQuantity) {
                input.value = value + 1;
            } else {
                alert("You cannot exceed the available quantity of " + maxQuantity + ".");
            }
        });
    }
</script>
<script>
function saveForLater(cartId, type, productId, price, quantity) {
    $.ajax({
        url: 'save_for_later.php',
        type: 'POST',
        data: {
            cart_id: cartId,
            type: type,
            product_id: productId,
            price: price,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            console.log(response); // For debugging
            if(response && response.success) {
                alert('Item saved for later');
                location.reload();
            } else {
                alert(response.message || 'Failed to save item');
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText); // For debugging
            alert('Error occurred while saving item');
        }
    });
}
</script>
    </script>
</body>
</html>
