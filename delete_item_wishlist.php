<?php
include 'message-box.php'; 
require('connection.php');
  session_start();
  if(isset($_SESSION['uid']))
  {
      $userid=$_SESSION['uid'];
  if(isset($_GET['id']))
  {
    $wid=$_GET['id'];
    $sql="delete from tbl_wishlist where wishlist_id='$wid' and lid='$userid'";
    $res=mysqli_query($con,$sql);
    if($res)
    {
      showAlert1('check', 'item Removed from wishlist', 'fa-check', '#4CAF50');
            ?>
             <script>
                setTimeout(function(){
   window.location.href = 'mywishlist.php';
}, 2000)
             </script>
            <?php

    }
  }
}
?>