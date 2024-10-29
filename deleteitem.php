<?php
include 'message-box.php'; 
require('connection.php');
  session_start();
  if(isset($_SESSION['uid']))
  {
      $userid=$_SESSION['uid'];
  if(isset($_GET['id']))
  {
    $cid=$_GET['id'];
    $sql="delete from tbl_cart where cart_id='$cid' and lid='$userid'";
    $res=mysqli_query($con,$sql);
    if($res)
    {
       showAlert1('check', 'item removed from cart', 'fa-check', '#4CAF50');
            ?>
             <script>
                setTimeout(function(){
   window.location.href = 'mycart.php';
}, 2000)
             </script>
            <?php

    }
  }
}
?>