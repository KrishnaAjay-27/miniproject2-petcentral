<?php
 require('connection.php');
  if(isset($_GET['id']))
  {
    $cid=$_GET['id'];
    
    $que="select quantity from tbl_cart where cart_id='$cid'";
    $re=mysqli_query($con,$que);
    if($re)
    {
      $row=mysqli_fetch_array($re);
      $qu=$row['quantity'];
      if($qu<=1)
      {
          $sql="delete from tbl_cart where cart_id='$cid'";
          $res=mysqli_query($con,$sql);
          if($res)
          {
              header("location:mycart.php");
              exit();
          }
  
      }
      else{
          $sql="update tbl_cart set quantity=quantity-1 where cart_id='$cid'";
          $res=mysqli_query($con,$sql);
          if($res)
          {
              header("location:mycart.php");
              exit();
          }
      }
      
    }
   
  }
?>
