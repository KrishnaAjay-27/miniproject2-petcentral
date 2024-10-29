<?php
session_start();
require('connection.php');
$userid=$_SESSION['uid'];

$add = $_POST['address'];
$city = $_POST['landmark'];
$r = $_POST['roadname'];
$dis = $_POST['district'];
$pin = $_POST['pin'];

// Check if address already exists
$query = "SELECT * FROM registration WHERE address='$add' AND landmark='$city' AND roadname='$r' AND district='$dis' AND pincode='$pin' AND lid='$userid'";
$res = mysqli_query($con,$query);
$c = mysqli_num_rows($res);

if($c > 0) {
    // Address already exists, so update it
    $sql = "UPDATE registration SET address='$add', landmark='$city',roadname='$r', district='$dis', pincode='$pin' WHERE lid='$userid'";
    $re = mysqli_query($con,$sql);

    
} else {
    // Address does not exist, so insert it
    $sql = "INSERT INTO registration (lid, address, landmark,roadname, district, pincode) VALUES ('$userid', '$add', '$city','$r', '$dis', '$pin')";
    $re = mysqli_query($con,$sql);
}
header('Location: ' . $_SERVER['PHP_SELF']);
exit;
?>
