<?php
  require('connection.php');
  if(isset($_GET['id']))
  {
    $cid=$_GET['id'];
    $sql="update tbl_cart set quantity=quantity+1 where cart_id='$cid'";
    $res=mysqli_query($con,$sql);
    if($res)
    {
        header("location:mycart.php");
        exit();
    }
  }
?>