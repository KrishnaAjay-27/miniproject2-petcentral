<?php
    include("header.php");
    include("goback.php");
    require('connection.php');
    if(isset($_SESSION['uid']))
                               {
                               $userid=$_SESSION['uid'];
                               $query="select * from registration where lid='$userid'";
                               $re=mysqli_query($con,$query);
                               $row=mysqli_fetch_array($re);
                               }
    else{
        
echo"<script>window.location.href='login.php';</script>";
    }
?>
<!DOCTYPE html>
  <head>
    <title>mycart</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
      .div{
        margin-top:0;
        height:170px;
        width:100%;
        margin-top:-140px;
      }
      /* .div img{
        height:180px;
        width:100%;
      } */
      .div h3{
        padding-top:100px;
        margin-left:100px;
        font-size:30px;
        font-weight:600;
      }
      .cart{
        min-height:800px;
        width:100%;
        height:auto;
        /* background-image:url("images/cartbg.jpg");
        background-size:cover;
        background-attachment:fixed; */
        display:grid;
        grid-template-columns: repeat(2,1fr);
        gap:3px;
      }
      
      .video{
        margin-left:500px;
        margin-top:10px;
        margin-bottom:30px;
        height:450px;
        width:450px;
      }
      .video img{
        height:450px;
        width:450px;
      }
      .empty{
        margin-left:150px;
        margin-top:-50px;
      }
      .card1{
        margin-left:50px;
        margin-top:50px;
        width:100%;
      }
      .card1 table{
        margin-left:10px;
        margin-top:10px;
        width:100%;
      }
      .card2{
        margin-left:100px;
        margin-top:60px;
        width:500px;
      }
      table {
  border-collapse: collapse;
  width: 100%;
  max-width: 800px;
  margin: auto;
  min-height:300px;
  height:auto;
}

td, th {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}
td{
  text-transform:capitalize;
}
tr:nth-child(even) {
  background-color: #f2f2f2;
}

tr:hover {
  background-color: #ddd;
}
td {
  border-top: none;
  border-right: none;
  border-left: none;
  border-bottom: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}
table img {
  height: 100px;
  width: 100px;
}

table th {
  background-color: #333;
  color: #fff;
}



button {
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 8px 16px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
}


.quantity {
  display: flex;
  align-items: center;
  background-color:#991823;
  width:100px;
  margin-left:15px;
  border-radius:10px;
  height:40px;
}

.quantity input {
  border:none;
  outline:none;
  padding: 8px;
  width: 30px;
  text-align: center;
  color:white;
  background-color:#991823;
  margin-top:2px;
}

.quantity button {
  width: 10px;
  height: 20px;
  font-size: 20px;
  line-height: 1;
  margin-left:0px;
  border-radius:10px;
  background-color:#991823;
  margin-top:-7px;
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}


.card {
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  margin-top: 20px;
  overflow: hidden;
  margin-left:60px;
  margin-right:40px;
  line-height:30px;
}

.card-header {
  background-color: #f5f5f5;
  font-weight: bold;
  padding: 8px;
}

.card-body {
  display: flex;
  flex-direction: column;
  padding: 8px;
}

.subtotal,
.shipping,
.total {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.subtotal-label,
.shipping-label,
.total-label {
  text-transform: uppercase;
}

.subtotal-value,
.shipping-value,
.total-value {
  font-weight: bold;
  text-align: right;
}

#span{
  text-transform:lowercase;
  padding-left:5px;
  color:black;
}

#back-shop{
  background: red;
  margin-left:10px;
  border-radius:5px;
  font-weight:600;
  height:45px;
  padding-left:30px;
  padding-right:30px;
  font-size:14px;
}
#back-shop:hover{
  background: #fc7c7c; 
}

#checkout{
  background: black;
  margin-left:230px;
  border-radius:5px;
  font-weight:600;
  height:45px;
  padding-left:30px;
  padding-right:30px;
  font-size:17px;
  color:white;
}
#checkout:hover{
  background: #fc7c7c;
}
    </style>
</head>
  <body>
  <div class="div">
        <h3>MYCART</h3>
    </div>
    <?php
  $sql="select tbl_cart.cart_id,tbl_cart.product_id,tbl_cart.quantity,tbl_cart.price,product_dog.name,product_dog.image1 from tbl_cart join product_dog on tbl_cart.product_id=product_dog.product_id where lid='$userid'";
  $res=mysqli_query($con,$sql);
  $c=mysqli_num_rows($res);
  $subtotal=0;
  $shipping=50;
  if($c==0)
  {
    ?>

    <div class="video">
    <img  src="cart.gif" alt="error"/>
                <h3 class="empty">Your Cart is Empty..!!</h3>
    </div>
   
      <?php
  }
  else{
  ?>
  <div class="cart">
      <div class="card1">
      <table>
  <tr>
    <th>Product Image</th>
    <th>Product Title</th>
    <th>Quantity</th>
    <th>Price</th>
    <th>Total</th>
    <th>Remove</th>
  </tr>
  <?php while($row=mysqli_fetch_array($res)) {
    $prototal = $row['price'] * $row['quantity'];
    $subtotal+=$prototal;
    $total=$subtotal;
  $proid=$row['product_id'];
  ?>
  <tr>
    <td><?php echo "<img src='uploads/".$row['image1']."' alt='not found'/>"; ?></td>
    <td><?php echo $row['name']; ?>
   

    </td>
    <td>
    <div class="quantity">
    <?php echo "<a  href='minusitem.php?id=",$row['cart_id'],"'>"?>
    <button class="minus-btn" type="button" name="button" value="-1">-</button>
  </a>

  <label for="quantity" hidden>Quantity:</label>
    <input type="number" name="quantity"  value="<?php echo $row['quantity']; ?>" min="1">
   
    <?php echo "<a  href='plusitem.php?id=",$row['cart_id'],"'>"?>
     <button class="plus-btn" type="button" name="button" value="1">+</button>
  </a>
    <input type="hidden" id="pro" name="product" value="<?php $row['product_id']; ?>"/>
  </div>
</td>
    <td><?php echo $row['price']; ?></td>
    <td><?php echo $prototal ?></td>
    <td><?php echo "<a href='deleteitem.php?id=".$row['cart_id']."'>"?><i class='fa fa-trash-o' style="color:black;font-size:20px;"></i></a></td>
  </tr>
  <?php } ?>
</table>
<br><br>
<a href="shops.php"><button type="button" id="back-shop">BACK TO SHOPPING</button></a>
      </div>
      <div class="card2">
        
        <h3 align="center">PRICE DETAILS</h3>
      <div class="subtotal-card">
      <div class="card">
  <div class="card-header">
    Subtotal
  </div>
  <div class="card-body">
    <div class="subtotal">
      <div class="subtotal-label">Subtotal<span id="span">(<?php echo $c." items"; ?>)</span>:</div>
      <div class="subtotal-value"><i class='fa fa-rupee' ></i> <?php echo $subtotal; ?></div>
    </div>

    <!------------------------------form starts ----------------------------------------- -->

    <form method="post" action="checkout.php">
    <?php
    if($subtotal<500)
    {
      ?>
      <div class="shipping">
      <div class="shipping-label">shipping fee:</div>
      <div class="shipping-value"><i class='fa fa-rupee' ></i><span id="sub"  style="color:black;"><?php echo $shipping; ?></span></div>
    </div>
    
    <?php
    
    $total+=$shipping;
    
    
    ?>
     <hr>
    <div class="total">
      <div class="total-label">Total:</div>
      <div class="total-value"><i class='fa fa-rupee' ></i> <?php echo $total; ?></div>
    </div>
    <?php
    }
   else{
     ?>
   
   <hr>
    <div class="total">
      <div class="total-label">Total:</div>
      <div class="total-value"><i class='fa fa-rupee' ></i> <?php echo $total; ?></div>

    </div>
    <?php
   }
   
    ?>
  </div>
  
</div>
<br><br><br>

<input type="hidden" name="total" value="<?php echo $total; ?>"/>
<button  type="submit" name='checkout' id='checkout'>Place Order</button>

</form>

      </div>
      </div>
    </div>
  <?php
  }
    
?>
     
<script>
// Quantity plus and minus buttons functionality
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
    input.value = value + 1;
  });
}
</script>


<?php
include("footer.php");

if(isset($_POST['checkout'])) {
  
    if(isset($_SESSION['uid'])) {
        $userid=$_SESSION['uid'];
        $cart_total=$_POST['total'];
        require('connection.php');
        $sql="insert into tbl_order(lid,total,date)values('$userid','$cart_total',NOW())";
        $res1=mysqli_query($con,$sql);
        if($res1) {
          $order_id = mysqli_insert_id($con);

        }
      }
      else{
        header("location:login.php");
        exit();
      }
}
?>
</body>
</html>
