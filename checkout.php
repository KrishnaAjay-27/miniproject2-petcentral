<?php
    include("header.php");
    require('connection.php');
    if(isset($_SESSION['uid']))
                               {
                               $userid=$_SESSION['uid'];
                               $query="select * from registration where lid='$userid'";
                               $re=mysqli_query($con,$query);
                               $row=mysqli_fetch_array($re);
                               }
    else{
        header("location:login.php");
        exit();
    }
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>checkout</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
  $('#new').click(function() {
    $('.newform').toggle();
  });
});
  </script>
 
      <script>  
		     $(document).ready(function() {
          // if($("#next2").prop('disabled',true)){
          //   $("#pass_err").html("Please fill the form correctly.").show()
          // }
    

                $("#add").keyup(function(){
                    check_address();
                })
                $("#district").on("change",function(){
                    check_district();
                })
                $("#city").keyup(function(){
                    check_city();
                })
                $("#pin").keyup(function(){
                    check_pin();
                })
                
                var add_error=false;
                var district_error=false;
                var city_error=false;
                var pin_error=false;

                
                function check_address()
                {
                    var pattern = /^[a-zA-Z\s]*$/;
                    var add = $("#add").val();
                    if (pattern.test(add)==true && add !="") {
                      $("#error_add").hide();
                      $("#add").css("border","1px solid green");
                      $("#next2").prop('disabled',false);
                    } else {
                      $("#error_add").html("Can contain only characters.").show();
                      $("#add").css("border","1px solid red");
                      $("#next2").prop('disabled',true);
                     add_error = true;
                    }
                }
                function check_city()
                {
                    var pattern = /^[a-zA-Z0-9\s]*$/;
                    var city = $("#city").val();
                    if (pattern.test(city)==true && city !="") {
                      $("#error_city").hide();
                      $("#city").css("border","1px solid green");
                      $("#next2").prop('disabled',false);
                    } else {
                      $("#error_city").html("Only characters,numbers and space are allowed.").show();
                      $("#city").css("border","1px solid red");
                      $("#next2").prop('disabled',true);
                      city_error = true;
                    }
                }

                function check_district()
                {
                    var pattern =  /^[a-zA-Z\s]*$/;
                    var dis = $("#district").val();
                    if (dis!="") {
                      $("#error_district").hide();
                      $("#district").css("border","1px solid green");
                      $("#next2").prop('disabled',false);
                    } else {
                      $("#error_district").html("Select an option.").show();
                      $("#district").css("border","1px solid red");
                      $("#next2").prop('disabled',true);
                      district_error = true;
                    }
                }

                function check_pin()
                {
                    var pattern = /^\s*6\d{5}\s*$/;
                    var pin = $("#pin").val();
                    if (pattern.test(pin)==true && pin !="") {
                      $("#error_pin").hide();
                      $("#pin").css("border","1px solid green");
                      $("#next2").prop('disabled',false);
                    } else {
                      $("#error_pin").html("Should start with 6 and contain only 6 digits.").show();
                      $("#pin").css("border","1px solid red");
                      $("#next2").prop('disabled',true);
                     pin_error = true;
                    }
                }

        $("#form").submit(function(event) {
            add_error=false;
            district_error=false;
            city_error=false;
            pin_error=false;

            check_address();
            check_city();
            check_district();
            check_pin();

            if (add_error===false && district_error===false && city_error===false && pin_error===false) {
                $("#pass_err").hide();
    event.preventDefault(); // Prevent default form submission behavior

    // Collect form data
    var formData = $(this).serialize();

    // Send AJAX request to server
    $.ajax({
      url: 'ajax.php', // Insertion script URL
      method: 'POST',
      data: formData, // Form data
      success: function(response) {
        
      }
    });
                return false;
            } else {
                $("#pass_err").html("Please fill the form correctly.").show()
               return false;
            }
         });
       
			  });
		   </script>
 


 <script>  
$(document).ready(function() {
  $("#placeorder").submit(function(event) {
    event.preventDefault(); // Prevent default form submission behavior
    // Collect form data
    var formData = $(this).serialize();
    // Send AJAX request to server
    $.ajax({
      url: 'process-form.php', // Insertion script URL
      method: 'POST',
      data: formData, // Form data
      success: function(response) {
        if (response === 'razorpay') {
          window.location.href = 'payment.php';
        }
      }
    });
    return false;
  });
});
		   </script>
           <script>
 $("#next1").click(function() {
        $(".content1").hide();
        $(".content2").show();
        $(".content3, .content4").hide();
        $("#step1, #step2").addClass("active");
      });

      $("#next2").click(function() {
        $(".content1, .content2, .content4").hide();
        $(".content3").show();
        $("#step1, #step2, #step3").addClass("active");
      });
  
  </script>








  <script>
  $(document).ready(function() {
    $(".content1").show();
    $(".content2, .content3, .content4").hide();

    $("#next1").click(function() {
      $(".content1").hide();
      $(".content2").show();
      $(".content3, .content4").hide();
      $("#step1").addClass("active");
      $("#step2").addClass("active");
    });
   
    $("#next2").click(function() {
      $(".content1, .content2, .content4").hide();
      $("#content3").show();
      $("#step1").addClass("active");
      $("#step2").addClass("active");
      $("#step3").addClass("active");
    });

    // $("#next3").click(function() {
      // $(".content1, .content2, .content3").hide();
      // $(".content4").show();
      // $("#step1").addClass("active");
      // $("#step2").addClass("active");
      // $("#step3").addClass("active");
      // $("#step4").addClass("active");
    // });

    // $("#prev2").click(function() {
    //   $(".content1").show();
    //   $(".content2").hide();
    //   $(".content3, .content4").hide();
    //   $("#step2,#step3,#step4").removeClass("active");
      
    //   $("#step1").addClass("active");
    // });

    // $("#prev3").click(function() {
    //   $(".content1, .content3").hide();
    //   $(".content2").show();
    //   $(".content4").hide();
    //   $("#step2,#step3,#step4").removeClass("active");
    //   $("#step1,").addClass("active");
    // });

  });
</script>


  <style>
   body{
    background-image:linear-gradient(rgba(0,0,0,0.35),rgba(0,0,0,0.35)),url("images/candies.jpg");
                background-size:cover;
            }

   .checkout {
  margin: 100px 200px 0 200px;
  /* border:1px solid black; */
  border-radius:50px;
  min-height:580px;
  background-color: #f9c74f;
}

/* progress-bar styles */
.progress-bar {
  display: flex;
  justify-content: space-between;
  list-style: none;
  padding-top:25px;
  text-align: center;
}

.progress-bar li {
  flex: 1;
  position: relative;
}

.progress-bar li span {
  display: inline-block;
  width: 30px;
  height: 30px;
  line-height: 30px;
  border-radius: 50%;
  background-color: #ddd;
  color: black;
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 10px;
}

.progress-bar li.active span {
  background: linear-gradient(to right, #FF1046 20%, #E01660 40%, #E01660 60%, #FF1046 80%);
    background-size: 200% auto;
    animation: effect 1s linear infinite;
    color:green;
}

@keyframes effect {
    to {
        background-position: -200% center;
    }
}

.progress-line{
  content: "";
  position: absolute;
  top: 15px;
  right: -50%;
  left: 57%;
  width: 95%;
  height: 5px;
  background-color: #ddd;
  color:#ddd;
}
.progress-bar li.active .progress-line {
  background: linear-gradient(to right, #ccc, #ccc 50%, #FF1046 50%, #FF1046);
  background-size: 200% 100%;
  animation: progress-bar-animation 1s ease-out forwards;
}

@keyframes progress-bar-animation {
  to {
    background-position: -100% 0;
  }
}

.checkout-content {
  width: 93%;
  min-height: 700px;
  height:auto;
  display: flex;
  overflow: hidden; 
  padding-bottom:40px;
  margin:50px 30px 10px 30px;
}


.content1,.content2,.content3,.content4 { 
  width: 100%;
  height: 100%;
  position: relative;

  /* background:transparent; */
 
  background-color: #f8f8f8;
}

.content1 {
  display: flex;
  flex-direction: column;
  align-items: left;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin: 20px;
}

.content1 .buttons{
  display:flex;
}
.content1 button {
  margin-left:50px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content1 button:hover {
  background-color: #ff1046;
}


.content2 {
  display: flex;
  flex-direction: column;
  align-items: left;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin: 20px;
}
.content2 label {
  font-weight: bold;
  margin-right: 10px;
  margin-left:40px;
  margin-bottom:10px;
}

.content2 input[type="text"] {
  width: 450px;
  padding: 13px;
  margin-bottom: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  margin-left:40px;
}
.name{
  display:flex;
}
.content2 .name input[type="text"] {
  width: 205px;
  padding: 13px;
  margin-bottom: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  margin-left:40px;
}


.content2 .error {
  color: red;
  font-size: 12px;
  margin-left:40px;
}
.content2 .buttons{
  display:flex;
}
.content2 button {
  margin-left:50px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content2 input[type="submit"]:hover {
  background-color: #ff1046;
}

.content2 input[type="submit"] {
  margin-left:50px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content2 button:hover {
  background-color: #ff1046;
}
.image{
  padding-top:150px;
  margin-left:550px;
}
.image img{
  height:250p;
  width:250px;
}



.content3 {
  display: flex;
  flex-direction: column;
  align-items: left;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin: 20px;
}

.content3 .buttons{
  display:flex;
}
.content3 button {
  margin-left:50px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content3 button:hover {
  background-color: #ff1046;
}
.content3 input[type="submit"]  {
  margin-left:50px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content3 input[type="submit"] :hover {
  background-color: #ff1046;
}


.content4 {
  display: flex;
  flex-direction: column;
  align-items: left;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin: 20px;
}
.content4 button {
  margin-left:670px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px 20px;
  margin-top: 10px;
  cursor: pointer;
}

.content4 button:hover {
  background-color: #ff1046;
}

.step {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  animation-duration: 0.5s;
  animation-name: slide-in;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

td{
  padding: 20px; 
  border-bottom: 1px solid #ddd; 
  display: flex; 
  align-items: center;
}
tr{
  border: none;
}

.order{
  flex: 1; 
  display: flex; 
  align-items: center;
  
}
.p1{
  font-weight: bold; 
  font-size: 1.2rem; 
  margin: 0;
  margin-left:20px;

}
.p2{
  color: #666; 
  font-size: 1rem; 
  margin: 0;  
  padding-top:10px;
  margin-left:20px;
}
.p3{
  color: #666; 
  font-size: 0.9rem; 
  margin: 0;
  margin-left:20px;
  padding-top:10px;
}
.p4{
  color: #666; 
  font-size: 0.9rem; 
  margin: 0;
  margin-left:20px;
  padding-top:10px;
}
.prototal{
  flex: 1; 
  text-align: right;
}
.prototal p{
  color: #333; 
  font-weight: bold; 
  font-size: 1.2rem; 
  margin: 0;
}

.total {
    margin-top:5px;
    padding: 10px;
    text-align: right;
  }
  


  /* -------------------------payment---------------------------------- */

  h2 {
  font-size: 24px;
  margin-bottom: 10px;
}

input[type="radio"] {
  width: 20px;
  height: 20px;
  border-radius: 50%; 
  accent-color: #fe3a66; 
  vertical-align: middle;
}



input[type="radio"]:checked::before {
  content: "";
  display: block;
  width: 12px;
  height: 12px;
  margin: 3px auto; 
  border-radius: 50%;
  accent-color: #fe3a66; 
}

input[type="radio"]:checked + label {
  color:black; 
  font-size:18px;
}


.label {
  display: inline-block;
  width: 200px;
  margin-left: 50px;
  margin-top:-18px;
  font-size:17px;
}


input[type="text"]{
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 10px;
  font-size: 16px;
  margin-bottom: 10px;
  width: 100%;
  box-sizing: border-box;
}
select{
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 10px;
  font-size: 16px;
  margin-bottom: 10px;
  margin-left: 40px;
  width:56%;
  box-sizing: border-box;
}
.expiry-cvv {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.expiry-cvv #card-expiry label {
  margin-bottom: 0;
  margin-left: 20px;
}

.label1 {
  margin-bottom: 0;
  margin-left: 40px;
}

.expiry-cvv input[type="text"] {
  flex: 1;
  margin-bottom: 0;
  margin-left: 20px;
}

button {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

button:hover {
  background-color: #3e8e41;
}

#card-details {
  display: none;
}




  </style>
</head>
<body>
<div class="checkout">

  <div class="progress-container">
    <ul class="progress-bar">
      <li id="step1" class="active"><span>1</span><br>Confirmation<div class="progress-line"></div></li>
      <li id="step2"><span>2</span><br>Address<div class="progress-line"></div></li>
      <li id="step3"><span>3</span><br>Payment</li>
    </ul>
    <div class="checkout-content">
      
    <!-- --------------------------------------content1 ---------------------------------------->
    <div class="content1" >
      
    <table>
      <?php
       $query="select tbl_cart.cart_id,tbl_cart.size,tbl_cart.product_id,tbl_cart.quantity,tbl_cart.price,product_dog.name,product_dog.description,product_dog.image1 from tbl_cart join product_dog on tbl_cart.product_id=product_dog.product_id where lid='$userid'";
       $result=mysqli_query($con,$query);
       
       $subtotal=0;
       $c=mysqli_num_rows($result);
       if($c>0)
       {
         while($row1=mysqli_fetch_array($result))
       {
        $we=$row1['size'];
        $que = "SELECT * FROM product_variants where variant_id='$we'";
       $res = mysqli_query($con,$que);
        $row4 = mysqli_fetch_array($res);
        $prototal = $row1['price'] * $row1['quantity'];
        $subtotal+=$prototal;
        $total=$subtotal;
        $shipping=0;
        ?>
         <tr>
         <td>
  <div class="order">
    <img src='uploads/<?php echo $row1['image1']; ?>' alt='not found' style="max-width: 100px; max-height: 100px; margin-right: 10px;">
    <div>
      <p class="p1"><?php echo $row1['name']; ?></p>
      <p class="p2"><?php echo 'Net Quantity :'.$row4['size']; ?></p>
      <p class="p3"><?php echo 'Price :$'.$row1['price']; ?></p>
      <p class="p4"><?php echo 'Quantity: '.$row1['quantity']; ?></p>
    </div>
  </div>
  <div class="prototal">
    <p ><?php echo '$'.$prototal; ?></p>
  </div>
</td>
         </tr>
         
        
        <?php
       }
       ?>
      </table>
      <hr style="margin-top:5px;border-color:black;"><br><br>
      <?php
      if($subtotal<500)
    {
    $total+=50;
    
    
    ?>
     
     <div class="total">
      <p>Subtotal: $<?php echo $subtotal; ?></p><br>
      <p>Shipping: $<?php echo $shipping; ?></p><br>
      <hr style="width:30%;margin-left:530px;border-color:black;"><br>
      <input type="hidden" name="total" value="<?php echo $total; ?>">
      <h4>Total  Amount : $<?php echo $total; ?></h4>
      </div>
    <?php
    }
   else{
     ?>
   <div class="total">
      <p>Subtotal: $<?php echo $subtotal; ?></p><br>
      <p>Shipping: $<?php echo $shipping; ?></p><br>
      <br>
      <hr style="width:30%;margin-left:530px;border-color:black;"><br>
      <input type="hidden" name="total" value="<?php echo $total; ?>">
      <h2>Total Amount : $<?php echo $total; ?></h2>
  </div>
    <?php
   }
   ?>
    <br><br>
    
      <div class="buttons" style="margin-left:270px;">
        
      <button id="next1">Proceed to Checkout</button><br><br>
      </div>
    </div>
         <?php
       }

       else{
?>
    <div class="total">
       <p>Subtotal: $<?php echo $subtotal; ?></p><br>
       <p>Shipping: $0</p><br>
       <br>
       <hr style="width:30%;margin-left:530px;border-color:black;"><br>
       <h2>Total Amount : $ 0</h2>
   </div>
     <br><br>
     
       <div class="buttons" style="margin-left:270px;">
         
       <button id="next1">Proceed to Checkout</button><br><br>
       </div>
     </div>
     <?php
       }
       ?>

    
    <!-- --------------------------------------content2 ---------------------------------------->
    <div class="content2" >
    <input type="submit" id="new" name="new" value="add new address" style="margin-left:350px;
  background-color:#fe3a66;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px ;
  margin-top: 10px;
  width:150px;
  cursor: pointer;"><br><br>
  
   <!--image div ---------------------------------------->
  <div class="image">
    <img src="delivery-boy.gif" alt="not found">
  </div>


   <div class="newform" style="display: none;">
   <div class="buttons">
    <button id="prev2">Previous</button>
    <button id="next2">Next</button>
</div>
</form>

<div class="newform" style="display: none;">
    <form method="post" id="form">
        <div class="address">
            <label>Address</label><br>
            <input class="two" type="text" name="address" id="add"/><br>
            <span id="error_add" class="error"></span><br>
        </div>

        <div class="city">
            <label>City</label><br>
            <input class="two" type="text" name="landmark" id="city" /><br>
            <span id="error_city" class="error"></span><br>
        </div>

        <div class="district">
            <label for="district">District</label>
            <select id="district" name="district" required>
                <option value="">Select District</option>
                <option value="Thiruvananthapuram">Thiruvananthapuram</option>
                <option value="Kollam">Kollam</option>
                <option value="Pathanamthitta">Pathanamthitta</option>
                <option value="Alappuzha">Alappuzha</option>
                <option value="Kottayam">Kottayam</option>
                <option value="Idukki">Idukki</option>
                <option value="Ernakulam">Ernakulam</option>
                <option value="Thrissur">Thrissur</option>
                <option value="Palakkad">Palakkad</option>
                <option value="Malappuram">Malappuram</option>
                <option value="Kozhikode">Kozhikode</option>
                <option value="Wayanad">Wayanad</option>
                <option value="Kannur">Kannur</option>
                <option value="Kasaragod">Kasaragod</option>
            </select>
            <br>
            <span id="error_district" class="error"></span><br>
        </div>

        <div class="pin">
            <label>Pincode</label><br>
            <input class="two" type="text" name="pincode" id="pin" /><br>
            <span id="error_pin" class="error"></span><br>
        </div>
        <div class="button">
            <input type="submit" id="sub" name="sub" value="Add"/>
            <br><span id="pass_err" class="error"></span>  
        </div>
    </form> 
</div>

   </div> 

   <form method="post" id="placeorder">
    <?php
   $firstRow = true;
   $userid = $_SESSION['uid']; // Make sure this is set and valid
$selectedAddressId = '';
$query = "SELECT * FROM registration WHERE lid = '$userid'";
$result = mysqli_query($con, $query);

while ($row1 = mysqli_fetch_assoc($result)) {
    // Ensure that the 'add_id' key exists in the array
    $addId = isset($row1['add_id']) ? $row1['add_id'] : '';

    ?>
    <div class="details-radio" style="margin-top: -50px; color:black;">
        <input type="radio" name="details" value="<?php echo htmlspecialchars($addId); ?>" <?php if ($firstRow) { echo "checked"; $selectedAddressId = $addId; } else { if ($addId == $selectedAddressId) { echo "checked"; } } ?>>
        <label for="details-radio">
            <?php echo htmlspecialchars($row1['name']) . ' ' . htmlspecialchars($row1['address']) . '<br>  ' .
                 htmlspecialchars($row1['landmark']) . '<br>  ' .
                 htmlspecialchars($row1['roadname']) . '<br>  ' .
                 htmlspecialchars($row1['district']) . '<br>  ' .
                 htmlspecialchars($row1['pincode']);
            ?>
        </label>
        <br><br><br><br>
    </div>
    <span id="error_msg" class="error"></span><br>
    <?php
    $firstRow = false;
}
?>
<div class="buttons">
    <button id="prev2">Previous</button>
    <button id="next2">Next</button>
</div>
</form>
      
   </div>
    </div>

    <!-- --------------------------------------content3 ---------------------------------------->
    <div class="content3" id="content3">
    
  <h2>Select payment Method:</h2><br><br>
  <input type="radio" name="pay-cash" value="razorpay" class="radiobutton" id="upi-option">
  <label for="upi-option" class="label">Razorpay</label><br><br>

  <br><br>
   
    <br><br>
      <div class="buttons">
        
      <button id="prev3">Previous</button>
      <input type="hidden" name="total" value="<?php echo $total; ?>"/>
      <button  type="submit" name='submit' id='checkout' value="Pay Now">done</button>
      
      </div>
    </div>

    </form>
    
    <!-- --------------------------------------content2 ---------------------------------------->
    <div class="content4" id="content4" >
     <?php
          include('content4.php');
     ?>
    </div>
  </div>
  </div>
</div>





</body>
</html>