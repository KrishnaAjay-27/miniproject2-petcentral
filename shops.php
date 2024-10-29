<?php
include("header.php");
include 'message-box.php';

include("goback.php");
//include 'message-box.php';
require('connection.php');
        // $query="select * from tbl_product ";
        // $result=mysqli_query($con,$query);
        if (!isset($_SESSION['uid'])) {
            header('Location: login.php');
            exit();
        }
        

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
     <script>
       $(document).ready(function() {
    $('.category-items li#cat').click(function() {
        $(this).find('.subcategory-items').toggle();
    });

    $('.subcategory-items li#subcat').click(function() {
        $(this).find('.sub-subcategory-items').toggle();
    });
});
     </script>
    <title>shop</title>
    <style>
        
*{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}
body{
    font-family: 'Quicksand', sans-serif;
}

    .div{
        background-image:url("images/ad.jpg");
        background-size:cover;
        background-attachment:fixed;
        height:200px;
        max-width:100%;
        margin-left:0;
        margin-right:0;
        margin-top:-140px;
    }
    .div img{
        height:350px;
        margin-left:850px;
        margin-top:-110px;
    }
    .div h3{
        margin-left:110px;
        padding-top:100px;
        font-weight:600;
        color:black;
        font-size:40px;
    }
    .content{
        margin-top:0px;
        background-color:#ffffff;
       /* background-image:url("images/admin1.jpg");
        background-size:cover;
        background-attachment:fixed; */
        display:flex;
        
        margin-left:10px;
    }
  .product-details{
    
    margin-left:40px;
        margin-top:60px;
        width:400px;
        padding-left:-1000px;
        display:grid;
        grid-template-columns: repeat(3,1fr);
  }
     :root{
    --white-light: rgba(255, 255, 255, 0.5);
    --alice-blue: #f8f9fa;
    --carribean-green: #40c9a2;
    --gray: #ededed;
}
/* Utility stylings */
img{
    width: 100%;
    display: block;
}
.container{
    max-width:100%;
    width: 25vw;
    margin: 0 auto;
}

/* product section */
.product{
    margin: 1rem;
    position: relative;
    width:230px;
    border:1px solid #cacaca; 
    background: #f5f3f0;
}
.product-content{
    padding: 3rem 0 0 0;
    cursor: pointer;
    height:260px;
    width:230px;
}
.product-img{
    background: white;
    box-shadow: 0 0 10px 10px var(--white-light);
    width: 170px;
    height: 170px;
    margin: 1% auto;
    border-radius: 50%;
    transition: background 0.5s ease;
    margin-top:-10px;
}
.product-btns{
    display: flex;
    justify-content: center;
    margin-top: 1.4rem;
    opacity: 0;
    transition: opacity 0.6s ease;
}
.btn-cart, .btn-view, .btn-wish{
    margin-top:-10px;
    background: transparent;
    border: 1px solid black;
    padding: 0.8rem 0;
    width: 125px;
    font-family: inherit;
    text-transform: uppercase;
    cursor: pointer;
    border: none;
    transition: all 0.6s ease;
}
.btn-cart, .btn-view, .btn-wish{
    background: white;
}
.btn-cart:hover{
    background:#ba2332;
}

.btn-view:hover{
    background: #ba2332;
}

.btn-wish:hover{
    background: #ba2332;
    color: #fff;
}
.product-info{
    background: white;
    padding: 0.5rem;   
    min-height:100px;
    height:auto;
    width:228px;
   
}
.product-name{
    color: black;
    display: block;
    text-decoration: none;
    font-size: 1rem;
    text-transform: uppercase;
    font-weight: bold;
    margin:1% auto;
    padding-top:10px;
}
.product-price{
    padding-top:-5px;
    padding-right: 0.2rem;
    display: inline-block;
}
.product-img img{
    width: 170px;
    height: 170px;
    margin: 1% auto;
    border-radius: 50%;
    transition: transform 0.6s ease;
}
.product:hover .product-img img{
    transform: scale(1.1);
}
.product:hover .product-btns{
    opacity: 1;
 }


.content1{
  margin-left:30px;
  margin-top:20px;
  border:none;
  outline:none;
  min-height:500px;
  background: white;
}
.wrap1 {
	border-radius: 5px;
	background-color: #e7e7e7;
	padding: 050px ;
	background: white;
}



/*category sidebar*/
.category{
    background:#f6f6f6;
    min-height:500px;
    height:fit-content;
    margin-bottom:50px;
    width:300px;
    margin-top:70px;
    margin-left:10px;
    position:relative
}
.category h3{
    text-align:center;
    margin-top:30px;
    text-transform:uppercase;
}
.category-items{
    margin-left:20px;
    margin-top:30px;
    text-transform:capitalize;
    line-height:50px;
}

.category-items hr{
    width:90%;
}
.subcategory-items{
    background:#f6f6f6;
    height:fit-content;
    padding-bottom:10px;
    width:fit-content;
    margin-top:20px;
    margin-left:7px;
    position:relative;
    color:purple;
}

.sub-subcategory-items{
    background:#f6f6f6;
    height:fit-content;
    padding-bottom:10px;
    width:fit-content;
    margin-top:20px;
    margin-left:10px;
    position:relative;
    color:red;
}


#disabled {
    pointer-events: none;
    opacity: 0.5;
  }


  .pagination {
    margin-left:800px;
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination a,
.pagination .current-page {
  display: inline-block;
  margin: 0 5px;
  padding: 5px 10px;
  border: 1px solid #ccc;
  border-radius: 50%;
  text-decoration: none;
  color: #333;
}


.pagination .current-page {
  background-color: #fc7c7c;
  color: #fff;
  border-color: #333;
}


    </style>
</head>
<body>
    <div class="div">
        <h3>PRODUCTS</h3>
    </div>
    <div class="content">
        
    <!-- category start -->  
    <div class="category">
    <h3>Categories</h3><br><br><hr style="width:80%;margin-left:20px;">
    <div class="category-items">
    <ul>
        <?php
        $query1 = "SELECT * FROM category";
        $result1 = mysqli_query($con, $query1);
        while($rows1 = mysqli_fetch_array($result1)) {
            echo "<li id='cat' value='".$rows1['cid']."'>".$rows1['name'];
            echo "<i class='fa fa-plus' style='padding-left:120px;padding-right:30px;font-size:10px;'></i>";
            ?>
            <ul class="subcategory-items" style="display:none;">
                <?php
                $query2 = "SELECT * FROM subcategory WHERE cid='".$rows1['cid']."'";
                $result2 = mysqli_query($con,$query2);
                while($rows2 = mysqli_fetch_array($result2)) {
                    echo "<li id='subcat' value='".$rows2['subid']."'>".$rows2['name'];
                    echo "<i class='' style='padding-left:90px;padding-right:30px;font-size:10px; color:purple;'></i>";
                    ?>
                   
                    <?php
                    echo "</li>";
                }
                ?>
            </ul>
            <?php
            echo "</li><hr>";
        }
        ?>
    </ul>
</div>
</div>

 <!-- category ends -->
 


<!-- product section -->
<div class="product-details">
    <?php
    $records_per_page =9; // change this to the desired number of records per page

    $total_records_query = "SELECT COUNT(*) as count FROM product_dog";
    $total_records_result = mysqli_query($con, $total_records_query);
    $total_records = mysqli_fetch_assoc($total_records_result)['count'];

    $total_pages = ceil($total_records / $records_per_page);

    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $starting_record = ($current_page - 1) * $records_per_page;
    $ending_record = $starting_record + $records_per_page - 1;

    if (isset($_GET['subid'])) {
        $sid = $_GET['subid'];
        $query = "SELECT * FROM product_dog WHERE subid='$sid' LIMIT $starting_record, $records_per_page";
    } elseif (isset($_GET['search'])) {
        $search_query = $_GET['search'];
        $query = "SELECT * FROM product_dog WHERE name LIKE '%$search_query%' LIMIT $starting_record, $records_per_page";
    } else {
        $query = "SELECT * FROM product_dog LIMIT $starting_record, $records_per_page";
    }

    $result1 = mysqli_query($con, $query);

    while ($row = mysqli_fetch_array($result1)) {
        $pid = $row['product_id'];
        if ($row['status'] == '0') {
    ?>
            <div class="products">
                <div class="container">
                    <div class="product-items">
                        <div class="product">
                            <div class="product-content">
                                <div class="product-img">
                                    <form method="post">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                            <?php echo "<img src='uploads/".$row['image1']."' alt='not found' width=230px height=230px>";?>
                            </div>
                            <div class = "product-btns">
                            <!-- <button type = "button" class = "btn-cart"><?php /*echo "<a href='cart.php?id=".$row['pid']."'>  <span><i class = 'fas fa-shopping-cart'></i></span></a>"*/?>
                                  
                                  </button> -->

                                  <button type = "submit" name="wishlist" value="<?php echo $pid; ?>" class = "btn-wish"> <span><i class = 'fa fa-heart'></i>  WISHLIST</span>
                                  
                                  </button>
    
                                  <?php echo "<a href='viewpro.php?id=".$row['product_id']."'> " ?><button type = "button" id="view" class = "btn-view"> <span><i class = 'fa fa-eye'></i>  VIEW</span>
                                  
                                </button></a>
                            </div>
                        </div>

                        <div class = "product-info">
                            
                            <a href = "#" class = "product-name"><?php  echo $row['name']; ?></a>
                             <?php
                              $que="select * from product_variants where product_id='$pid'";
                              $res=mysqli_query($con,$que);
                              $count1=mysqli_num_rows($res);
                              $row1=mysqli_fetch_array($res);
                              $price=$row1['price'];
                             
                            ?>
                            
                            <p class = "product-price" style="font-size:20px;"><i class="fa fa-rupee"></i>    <?php  echo $row1['price']; ?>
                        
                            <?php
                           if($row1['quantity']!==0)
                           {
                              ?>
                                <h3 style="color:green;font-size:13px;padding-top:20px;margin-left:5px;">IN STOCK</h3>
                                <?php
                           }
                           else{
                            ?>
                                <h3 style="color:red;font-size:13px;padding-top:20px;margin-left:5px;">NOT IN STOCK</h3>
                                <?php
                           }
                        ?>
                        </p>
                            <input type="hidden" name="price" value="<?php echo $price; ?>">
                          </div>
                    </div>
                    </form>

    
                    </div>
            </div>
    </div>
    <?php
    }
   elseif($row['status']=='1'){
    ?>
    
    <div class = "products" id="disabled">
    <div class = "container">
        <div class = "product-items">
                <div class = "product">
                    <div class = "product-content">
                        <div class = "product-img">
                            <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                        <?php echo "<img src='uploads/".$row['image1']."' width=230px height=230px>";?>
                        </div>
                        <div class = "product-btns">
                        <!-- <button type = "button" class = "btn-cart"><?php /*echo "<a href='cart.php?id=".$row['pid']."'>  <span><i class = 'fas fa-shopping-cart'></i></span></a>"*/?>
                              
                              </button> -->

                              <button type = "submit" name="wishlist" value="<?php echo $pid; ?>" class = "btn-wish"> <span><i class = 'fa fa-heart'></i>  WISHLIST</span>
                              
                              </button>

                              <?php echo "<a href='viewpro.php?id=".$row['product_id']."'> " ?><button type = "button" id="view" class = "btn-view"> <span><i class = 'fa fa-eye'></i>  VIEW</span>
                              
                            </button></a>
                        </div>
                    </div>

                    <div class = "product-info">
                        
                        <a href = "#" class = "product-name"><?php  echo $row['name']; ?></a>
                         <?php
                          $que="select * from product_variants where product_id='$pid'";
                          $res=mysqli_query($con,$que);
                          $count1=mysqli_num_rows($res);
                          $row1=mysqli_fetch_array($res);
                          $price=$row1['price'];
                         
                        ?>
                        
                        <p class = "product-price" style="font-size:20px;"><i class="fa fa-rupee"></i>    <?php  echo $row1['price']; ?>
                    
                        <?php
                       if($row1['quantity']!==0)
                       {
                          ?>
                            <h3 style="color:green;font-size:13px;padding-top:20px;margin-left:5px;">IN STOCK</h3>
                            <?php
                       }
                       else{
                        ?>
                            <h3 style="color:red;font-size:13px;padding-top:20px;margin-left:5px;">NOT IN STOCK</h3>
                            <?php
                       }
                    ?>
                    </p>
                        <input type="hidden" name="price" value="<?php echo $price; ?>">
                      </div>
                </div>
                </form>


                </div>
        </div>
</div>
<?php
    
   }

        ?>
         
  <?php
     }
     
    ?>

    </div>

    
   
</div>
<div class="pagination">
    <?php if ($current_page > 1) : ?>
        <a style="border:none;outline:none;"   href="?page=<?php echo $current_page - 1; ?>" class="pagination-link">Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
        <?php if ($i == $current_page) : ?>
            <span class="current-page"><?php echo $i; ?></span>
        <?php else : ?>
            <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($current_page < $total_pages) : ?>
        <a style="border:none;outline:none;"  href="?page=<?php echo $current_page + 1; ?>" class="pagination-link">Next</a>
    <?php endif; ?>
</div><br><br><br>
<?php
if(isset($_POST['wishlist']))
{
    $pid = $_POST['product_id'];
    
    $price = $_POST['price'];  
    $con = mysqli_connect("localhost","root","","project");

    $query = "SELECT * FROM tbl_wishlist WHERE product_id='$pid'";
    $result = mysqli_query($con,$query);
    $c = mysqli_num_rows($result);
    if($c > 0)
    {
        if(isset($_SESSION['uid']))
        {
            $userid = $_SESSION['uid'];
            $sql = "UPDATE tbl_wishlist SET lid='$userid',price='$price' ,PostedDate=NOW() WHERE lid='$userid' AND product_id='$pid'";
            $re = mysqli_query($con,$sql);
        }
        else{
            //echo '<script>showMessage("Hello, World!");</script>';
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



<?php
   include("footer.php");
?>

</body>
</html>


<?php
include("footer.php");
?>
</body>
</html>