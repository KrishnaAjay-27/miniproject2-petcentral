<?php
   require('connection.php');
   $id = $_POST["sub"];
   $qu="SELECT * FROM product_variants where variant_id = '$id'";
   $result = mysqli_query($con,$qu);
   $row=mysqli_fetch_array($result);
   echo "<i class='fa fa-rupee' ></i>".$row['price'];
   
   // if($row['quantity']!==0)
   // {
      ?>
        <!-- <h3 style="color:green;font-size:20px;margin-top:20px;margin-bottom:10px;">IN STOCK</h3> -->
        <?php
   // }
   // else{
    ?>
        <!-- <h3 style="color:red;font-size:20px;margin-top:20px;margin-bottom:10px;">NOT IN STOCK</h3> -->
        <?php
   // }
   
   echo "<br>";
?>
