<?php
    include("header.php");
    include("goback.php");
    include 'message-box.php';
    require('connection.php');
    if(isset($_GET['id']))
    {
    $id=$_GET['id'];
    $query="select * from product_dog where product_id='$id'";
    $re=mysqli_query($con,$query);
    $count=mysqli_num_rows($re);
    $row=mysqli_fetch_array($re);
    }
    if(isset($_GET['id']))
    {
        $ssid=$_GET['id'];

        // Query to fetch product details along with supplier code
        $query1= "
            SELECT p.*, s.supplier_code 
            FROM product_dog p
            INNER JOIN s_registration s ON p.sid = s.sid
            WHERE p.product_id='$ssid'
        ";
        $re1 = mysqli_query($con, $query1);
        $count1 = mysqli_num_rows($re1);
        $row1 = mysqli_fetch_array($re1);
    }

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single E-commerce Product Page using HTML, CSS - Codingscape</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
		     $(document).ready(function() {
            $("#select").on("change",function(){
                    var na = $("#select").val();
                      $.ajax({
                      type:"POST",
                      url: "priceview.php",
                      data: { sub: na },
                      success: function(result){
                        $('#price').html(result);
                     }
                    });
                });
            });
		  </script>
    <style>
        *{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

:root{
    --primary-color: #5344db;
    --accent-color: #5344db;
    --grey:#484848;
    --bg-grey: #efefef;
    --shadow: #949494;
}

.container{
    margin-top:-40px;
    display: flex;
    position: relative;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    height:auto;
    margin-left:70px;
    margin-bottom:50px;
}

.row{
    display: flex;
    gap: 20px;
}

.single-product{
    width: 1080px;
    position: relative;
}
/** product image **/

.single-product .product-image{
    width: 100%;
    position:relative;
}



.product-image .product-image-main{
    position: relative;
    display: block;
    height: 480px;
    background: var(--bg-grey);
    padding: 10px;
    margin-right:60px;
}

.product-image-main img{
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}


.product-image .product-image-main1{
    position: relative;
    display: block;
    height: 480px;
    background: var(--bg-grey);
    padding: 10px;
    margin-right:80px;
}

.product-image-main1 img{
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-image-main1{
    margin-top:270px;
    width: 470px;
    position: absolute;
    transform: translate(-50%,-50%);
    top: 50%;
    left: 50%;
    overflow: hidden;
    border: 10px solid #ffffff;
    border-radius: 8px;
    box-shadow: -1px 5px 15px black;
}


.wrappers{
    width: 470px;
    
    height:100%;
    display: flex;
    animation: slide 16s infinite;
}
.wrappers img{
    width: 470px;
    height:100%;
}

@keyframes slide{
    0%{
        transform: translateX(0);
    }
    20%{
        transform: translateX(0);
    }
    35%{
        transform: translateX(-96%);
    }
    45%{
        transform: translateX(-96%);
    }
    55%{
        transform: translateX(0);
    }
    75%{
        transform: translateX(0);
    }
    80%{
        transform: translateX(-96%);
    }
    80%{
        transform: translateX(-96%);
    }
    100%{
        transform: translateX(0);
    }
}
/** product title **/

.product-title{
    margin-top: 20px;

}
.product-title h2{
    font-size: 32px;
    line-height: 2.4rem;
    font-weight: 700;
    letter-spacing: -0.02rem;
}


/** Product price **/
.product-price{
    display: flex;
    position: relative;
    margin: 10px 0;
    align-items: center;
}

.product-price .offer-price{
    font-size: 48px;
    font-weight: 700;
}
/** Product Details **/
.product-details{
    margin: 10px 0;
    margin-top:-25px;
}
.product-details h3{
    font-size: 18px;
    font-weight: 500;
}
.product-details p{
    margin: 5px 0;
    font-size: 14px;
    line-height: 1.2rem;
}


/** product Button Group **/
.quantity{
    width:120px;
    height:35px;
}
.quantity input{
    width:80px;
    height:35px;
    margin-left:10px;
    padding-left:10px;
    font-size:20px;
}
.product-btn-group{
    display: flex;
    gap: 10px;
    height:60px;
}
#cart-button button{
    margin-top:5px;
    background:black;
    padding-top:-5px;     
    width:180px;
    height:45px;
    color:white;
    background:black;
    border:none;
    outline:none;
    text-transform:uppercase;
    font-size:16px;
}
#cart-button button:hover{
    color:black;
    background:#a2212c;
    font-size:17px;
}

#wishlist-button button{
    margin-top:5px;
    background:black;
    padding-top:-5px;
    width:200px;
    height:45px;
    color:white;
    background:black;
    border:none;
    outline:none;
    text-transform:uppercase;
    font-size:16px;
}
#wishlist-button button:hover{
    color:black;
    background:#a2212c;
    font-size:17px;
}
#select{
  width:300px;
  height:35px;
  margin-left:20px;
}


    </style>
</head>
<body>

    <div class="container">
        <div class="single-product">
            <div class="row">
                <div class="col-6">
                    <div class="product-image">
                        <?php
                         if($row['image1']&&$row['image2'])
                         {
                           ?>
                           <div class="product-image-main1">
                            <div class="wrappers">
                                
                        <?php echo "<img src='uploads/".$row['image1']."' alt='not found' id='product-main-image'>";?>
                        <?php echo "<img src='uploads/".$row['image2']."'  alt='not found' id=product-secondary-image'>";?>
                         </div>
                            </div>
                            <?php
                         }
                         else
                         {
                           ?>
                              <div class="product-image-main">
                        <?php echo "<img src='uploads/".$row['image1']."' alt='not found' id='product-main-image'>";?>
                        </div>
                           <?php

                         }
                        ?>
                        
                    </div>
                </div>
                <div class="col-6">
                    <div class="product">
                        <form method="post">
                        <div class="product-title">
                            <h2><?php echo $row['name']; ?></h2>
                            <h2>Species:-<?php echo $row['species']; ?></h2>
                        </div>
                        <!-- Display supplier code here -->
                        <div class="supplier-code">
                        <div class="supplier-code">
    <?php if ($row1 && isset($row1['supplier_code'])): ?>
        <h3 style="color: red;">Supplier Code: <?php echo htmlspecialchars($row1['supplier_code']); ?></h3>
    <?php else: ?>
        <h3 style="color: red;">Supplier Code: Not Available</h3>
    <?php endif; ?>
</div>

</div>
                           <?php
                            $que="select * from product_variants where product_id='$id'";
                            $res=mysqli_query($con,$que);    
                             $row2=mysqli_fetch_array($res);
                            ?>
                        <div class="product-price">
                        <span class="offer-price" id="price" name="price"><i class='fa fa-rupee' ></i><?php echo $row2['price']; ?>

                        
                        <?php
                           if($row2['quantity']!==0)
                           {
                              ?>
                                <h3 style="color:green;font-size:20px;padding-top:20px;margin-left:5px;">IN STOCK</h3>
                                <?php
                           }
                           else{
                            ?>
                                <h3 style="color:red;font-size:20px;padding-top:20px;margin-left:5px;">NOT IN STOCK</h3>
                                <?php
                           }
                           echo "<br>";
                        ?></span>
                        </div>
                        
                        <div class="product-details">
                            <h3>Description</h3>
                            <textarea  disabled style="background:transparent;min-height:50px;height:auto;margin-left:0px;outline:none;border:none;font-size:18px;width:600px;" cols="100" rows="10"><?php echo $row['description']; ?></textarea><br>
                         
                        </div><br><br>
                                <div class="weight">
                                    <label>Size</label>
                                   <select id="select" name="size">
                                    <?php
                                    $que="select * from product_variants where product_id='$id'";
                                    $res=mysqli_query($con,$que);    
                                    $row1=mysqli_fetch_array($res);  
                                    echo"<option align='center' value='".$row1['variant_id']."'>".$row1['size']."</option>";     
                                   while($row1=mysqli_fetch_array($res))
                                   {
                                  echo"<option value='".$row1['variant_id']."'>".$row1['size']."</option>";
                                  }  
                                 ?>
                                   </select>
                                   </div><br><br>
                        <hr style="color:black;height:3px;">
                        <br><br>
                        
                        <div class="product-btn-group">
                        <div class="quantity">
                            <input type="number" min="1" max="100" step="1" value="1" name="num" style="width:100px;height:45px;margin-top:5px;padding-left:45px;">
                         </div>
                            <div id="cart-button" ><button  type="submit" id="cart" name="sub"><i class='fa fa-shopping-cart' ></i> Add to cart</button></div>
                            <div id="wishlist-button" ><button  type="submit" name="wishlist"><i class='fa fa-heart' ></i> Add to wishlist</button></div>
                        
                        </div>
                                </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php

if (isset($_POST['sub'])) {
    $pid = $_GET['id'];
    $we = isset($_POST['size']) ? $_POST['size'] : null;
    $qu = $_POST['num'];
    require('connection.php');
    
    if (isset($_SESSION['uid'])) {
        $userid = $_SESSION['uid'];
        $query = "SELECT * FROM tbl_cart WHERE product_id='$pid' AND lid='$userid'";
        $result = mysqli_query($con, $query);
        $c = mysqli_num_rows($result);
        $que = "SELECT * FROM product_variants WHERE variant_id='$we'";
        $res = mysqli_query($con, $que);
        $row4 = mysqli_fetch_array($res);
        $price = $row4['price'];            
        $weight = $row4['variant_id'];
        
        if ($c > 0) {
            if ($we) {
                $sql = "UPDATE tbl_cart SET lid='$userid', size='$we', quantity='$qu', price='$price' WHERE lid='$userid' AND product_id='$pid'";
            } else {
                $sql = "UPDATE tbl_cart SET lid='$userid', size='$weight', quantity='$qu', price='$price' WHERE lid='$userid' AND product_id='$pid'";
            }
            $re = mysqli_query($con, $sql);
        } else {
            if (isset($_SESSION['uid'])) {
                $userid = $_SESSION['uid'];
                if ($we) {
                    $sql = "INSERT INTO tbl_cart(lid, product_id, size, quantity, price) VALUES('$userid', '$pid', '$we', '$qu', '$price')";
                } else {
                    $sql = "INSERT INTO tbl_cart(lid, product_id, size, quantity, price) VALUES('$userid', '$pid', '$weight', '$qu', '$price')";
                }
                $re = mysqli_query($con, $sql);
                showAlert1('check', 'Item added to cart', 'fa-check', '#4CAF50');
                ?>
                <script>
                    setTimeout(function(){
                        window.location.href = 'shops.php';
                    }, 5000);
               
             </script>
            <?php
        }
    }
}
    else{
        if(isset($_SESSION['uid']))
        {

            $userid = $_SESSION['uid'];
              $sql = "INSERT INTO tbl_wishlist(product_id, lid,price,PostedDate) VALUES('$pid','$userid','$price',NOW())";
              $re = mysqli_query($con,$sql);
              
              showAlert1('check', 'item added to Wishlist', 'fa-check', '#4CAF50');
            ?>
             <script>
                setTimeout(function(){
   window.location.href = 'shops.php';
}, 5000)
             </script>
            <?php
           
        }
        else{
            showAlert2('warning', 'Please log in to continue', 'exclamation-circle', '#FFFFCC');
            ?>
             <script>
                setTimeout(function(){
   window.location.href = 'login.php';
}, 5000)
             </script>
            <?php
        }
    }   
}
?>
</body>
</html>
<?php
   include("footer.php");
?>

















