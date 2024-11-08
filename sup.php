<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the supplier's name from the 's_registration' table
$uid = $_SESSION['uid'];
$query = "SELECT name FROM s_registration WHERE lid='$uid'";
$result = mysqli_query($con, $query);

// Check if the query was successful
if ($result) {
    $supp = mysqli_fetch_assoc($result);
    $suppliers = $supp ? $supp['name'] : 'Supplier'; // Default name if no record found
} else {
    echo "Error: " . mysqli_error($con); // Output the error for debugging
    $suppliers = 'Supplier'; // Default name in case of query failure
}

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>queens dental clinic</title>
<meta charset="utf-8">
<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/grid.css" type="text/css" media="screen">
<script src="js/jquery-1.6.3.min.js" type="text/javascript"></script>
<script src="js/tabs.js" type="text/javascript"></script>
<style>
body {
    background-color: #f9f9fa
}

.padding {
    padding-top: 99px;
}

.user-card-full {
    overflow: hidden;
}

.card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 20px 0 rgba(69,90,100,0.08);
    box-shadow: 0 1px 20px 0 rgba(69,90,100,0.08);
    border: none;
    margin-bottom: 30px;
}

.m-r-0 {
    margin-right: 0px;
}

.m-l-0 {
    margin-left: 0px;
}

.user-card-full .user-profile {
    border-radius: 5px 0 0 5px;
}

.bg-c-lite-green {
        background: -webkit-gradient(linear, left top, right top, from(#f29263), to(#ee5a6f));
    background: linear-gradient(to right, #ee5a6f, #f29263);
}

.user-profile {
    padding: 20px 0;
}

.card-block {
    padding: 1.25rem;
}

.m-b-25 {
    margin-bottom: 25px;
}

.img-radius {
    border-radius: 5px;
}


 
h6 {
    font-size: 14px;
}

.card .card-block p {
    line-height: 25px;
}

@media only screen and (min-width: 1400px){
p {
    font-size: 14px;
}
}

.card-block {
    padding: 1.25rem;
}

.b-b-default {
    border-bottom: 1px solid #e0e0e0;
}

.m-b-20 {
    margin-bottom: 20px;
}

.p-b-5 {
    padding-bottom: 5px !important;
}

.card .card-block p {
    line-height: 25px;
}

.m-b-10 {
    margin-bottom: 20px;
}

.text-muted {
    color: #919aa3 !important;
}

.b-b-default {
    border-bottom: 1px solid #e0e0e0;
}

.f-w-600 {
    font-weight: 600;
}

.m-b-20 {
    margin-bottom: 20px;
}

.m-t-40 {
    margin-top: 20px;
}

.p-b-5 {
    padding-bottom: 5px !important;
}

.m-b-10 {
    margin-bottom: 10px;
}

.m-t-40 {
    margin-top: 20px;
}

.user-card-full .social-link li {
    display: inline-block;
}

.user-card-full .social-link li a {
    font-size: 20px;
    margin: 0 10px 0 0;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}


/* Googlefont Poppins CDN Link */

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');

*{

  margin: 0;

  padding: 0;

  box-sizing: border-box;

  font-family: 'Poppins', sans-serif;

}

* {

  box-sizing: border-box;

}

 

input[type=text], select, textarea {

  width: 100%;

  padding: 12px;

  border: 1px solid #ccc;

  border-radius: 4px;

  resize: vertical;

}

 

label {

  padding: 12px 12px 12px 0;

  display: inline-block;

}

 

input[type=submit] {

  background-color: #04AA6D;

  color: white;

  padding: 12px 20px;

  border: none;

  border-radius: 4px;

  cursor: pointer;

  float: right;

}

 

input[type=submit]:hover {

  background-color: #45a049;

}

 

.container {

    width: 1000px;

    margin-top:600px;

    margin-right:100px;

  border-radius: 25px;

  background-color: #f2f2f2;

  padding: 20px;

}

 

.col-25 {

  float: left;

  width: 25%;

  margin-top: 6px;

}

 

.col-75 {

  float: left;

  width: 75%;

  margin-top: 6px;

}

 

/* Clear floats after the columns */

.row:after {

  content: "";

  display: table;

  clear: both;

}

 

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */

@media screen and (max-width: 600px) {

  .col-25, .col-75, input[type=submit] {

    width: 100%;

    margin-top: 0;

  }

}

.sidebar{

  position: fixed;

  height: 100%;

  width: 240px;

  background: #60adde ;

  transition: all 0.5s ease;

}

.sidebar.active{

  width: 60px;

}

.sidebar .logo-details{

  height: 80px;

  display: flex;

  align-items: center;

}

.sidebar .logo-details i{

  font-size: 28px;

  font-weight: 500;

  color: #fff;

  min-width: 60px;

  text-align: center

}

.logodetails ibase_add_user{

  font-size: 28px;

  font-weight: 500;

  color: #fff;

  min-width: 60px;

  text-align: center

}

.sidebar .logo-details .logo_name{

  color: #fff;

  font-size: 24px;

  font-weight: 500;

}

 

.sidebar .nav-links{

  margin-top: 10px;

}

.sidebar .nav-links li{

  position: relative;

  list-style: none;

  height: 50px;

}

.sidebar .nav-links li a{

  height: 100%;

  width: 100%;

  display: flex;

  align-items: center;

  text-decoration: none;

  transition: all 0.4s ease;

}

.sidebar .nav-links li a.active{

  background:blue;

}

.sidebar .nav-links li a:hover{

  background: blue;

}

.sidebar .nav-links li i{

  min-width: 60px;

  text-align: center;

  font-size: 18px;

  color: #fff;

}

.sidebar .nav-links li a .links_name{

  color: #fff;

  font-size: 15px;

  font-weight: 400;

  white-space: nowrap;

}

.sidebar .nav-links .log_out{

  position: absolute;

  bottom: 0;

  width: 100%;

}

.home-section{

  position: relative;

  background: #f5f5f5;

  padding-bottom:50px;

  min-height: 100vh;

  width: calc(100% - 240px);

  left: 240px;

  transition: all 0.5s ease;

}

.sidebar.active ~ .home-section{

  width: calc(100% - 60px);

  left: 60px;

}

.home-section nav{

  display: flex;

  justify-content: space-between;

  height: 80px;

  background: #fff;

  display: flex;

  align-items: center;

  position: fixed;

  width: calc(100% - 240px);

  left: 240px;

  z-index: 100;

  padding: 0 20px;

  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);

  transition: all 0.5s ease;

}

.sidebar.active ~ .home-section nav{

  left: 60px;

  width: calc(100% - 60px);

}

.home-section nav .sidebar-button{

  display: flex;

  align-items: center;

  font-size: 24px;

  font-weight: 500;

}

nav .sidebar-button i{

  font-size: 35px;

  margin-right: 10px;

}

.home-section nav .search-box{

  position: relative;

  height: 50px;

  max-width: 550px;

  width: 100%;

  margin: 0 20px;

}

nav .search-box input{

  height: 100%;

  width: 100%;

  outline: none;

  background: #F5F6FA;

  border: 2px solid #EFEEF1;

  border-radius: 6px;

  font-size: 18px;

  padding: 0 15px;

}

nav .search-box .bx-search{

  position: absolute;

  height: 40px;

  width: 40px;

  background: #2697FF;

  right: 5px;

  top: 50%;

  transform: translateY(-50%);

  border-radius: 4px;

  line-height: 40px;

  text-align: center;

  color: #fff;

  font-size: 22px;

  transition: all 0.4 ease;

}

.home-section nav .profile-details{

  display: flex;

  align-items: center;

  background: #F5F6FA;

  border: 2px solid #EFEEF1;

  border-radius: 6px;

  height: 50px;

  min-width: 190px;

  padding: 0 15px 0 2px;

}

nav .profile-details img{

  height: 40px;

  width: 40px;

  border-radius: 6px;

  object-fit: cover;

}

nav .profile-details .admin_name{

  font-size: 15px;

  font-weight: 500;

  color: #333;

  margin: 0 10px;

  white-space: nowrap;

}

nav .profile-details i{

  font-size: 25px;

  color: #333;

}

.home-section .home-content{

  position: relative;

  padding-top: 104px;

}

.home-content .overview-boxes{

  display: flex;

  align-items: center;

  justify-content: space-between;

  flex-wrap: wrap;

  padding: 0 20px;

  margin-bottom: 26px;

}

.overview-boxes .box{

  display: flex;

  align-items: center;

  justify-content: center;

  width: calc(100% / 4 - 15px);

  background: #fff;

  padding: 15px 14px;

  border-radius: 12px;

  box-shadow: 0 5px 10px rgba(0,0,0,0.1);

}

.overview-boxes .box-topic{

  font-size: 20px;

  font-weight: 500;

}

.home-content .box .number{

  display: inline-block;

  font-size: 35px;

  margin-top: -6px;

  font-weight: 500;

}

.home-content .box .indicator{

  display: flex;

  align-items: center;

}

.home-content .box .indicator i{

  height: 20px;

  width: 20px;

  background: #8FDACB;

  line-height: 20px;

  text-align: center;

  border-radius: 50%;

  color: #fff;

  font-size: 20px;

  margin-right: 5px;

}

.box .indicator i.down{

  background: #e87d88;

}

.home-content .box .indicator .text{

  font-size: 12px;

}

.home-content .box .cart{

  display: inline-block;

  font-size: 32px;

  height: 50px;

  width: 50px;

  background: #cce5ff;

  line-height: 50px;

  text-align: center;

  color: #66b0ff;

  border-radius: 12px;

  margin: -15px 0 0 6px;

}

.home-content .box .cart.two{

   color: #2BD47D;

   background: #C0F2D8;

 }

.home-content .box .cart.three{

   color: #ffc233;

   background: #ffe8b3;

 }

.home-content .box .cart.four{

   color: #e05260;

   background: #f7d4d7;

 }

.home-content .total-order{

  font-size: 20px;

  font-weight: 500;

}

.home-content .sales-boxes{

  display: flex;

  justify-content: space-between;

  /* padding: 0 20px; */

}

 

/* left box */

.home-content .sales-boxes .recent-sales{

  width: 65%;

  background: #fff;

  padding: 20px 30px;

  margin: 10px 20px;

  border-radius: 12px;

  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);

}

.home-content .sales-boxes .sales-details{

  display: flex;

  align-items: center;

  justify-content: space-between;

}

.sales-boxes .box .title{

  font-size: 24px;

  font-weight: 500;

  /* margin-bottom: 10px; */

}

.sales-boxes .sales-details li.topic{

  font-size: 20px;

  font-weight: 500;

}

.sales-boxes .sales-details li{

  list-style: none;

  margin: 8px 0;

}

.sales-boxes .sales-details li a{

  font-size: 18px;

  color: #333;

  font-size: 400;

  text-decoration: none;

}

.sales-boxes .box .button{

  width: 100%;

  display: flex;

  justify-content: flex-end;

}

.sales-boxes .box .button a{

  color: #fff;

  background: #0A2558;

  padding: 4px 12px;

  font-size: 15px;

  font-weight: 400;

  border-radius: 4px;

  text-decoration: none;

  transition: all 0.3s ease;

}

.sales-boxes .box .button a:hover{

  background:  #0d3073;

}

 

/* Right box */

.home-content .sales-boxes .top-sales{

  width: 35%;

  background: #fff;

  padding: 20px 30px;

  margin: 0 20px 0 0;

  border-radius: 12px;

  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);

}

.sales-boxes .top-sales li{

  display: flex;

  align-items: center;

  justify-content: space-between;

  margin: 10px 0;

}

.sales-boxes .top-sales li a img{

  height: 40px;

  width: 40px;

  object-fit: cover;

  border-radius: 12px;

  margin-right: 10px;

  background: #333;

}

.sales-boxes .top-sales li a{

  display: flex;

  align-items: center;

  text-decoration: none;

}

.sales-boxes .top-sales li .product,

.price{

  font-size: 17px;

  font-weight: 400;

  color: #333;

}

/* Responsive Media Query */

@media (max-width: 1240px) {

  .sidebar{

    width: 60px;

  }

  .sidebar.active{

    width: 220px;

  }

  .home-section{

    width: calc(100% - 60px);

    left: 60px;

  }

  .sidebar.active ~ .home-section{

    /* width: calc(100% - 220px); */

    overflow: hidden;

    left: 220px;

  }

  .home-section nav{

    width: calc(100% - 60px);

    left: 60px;

  }

  .sidebar.active ~ .home-section nav{

    width: calc(100% - 220px);

    left: 220px;

  }

}

@media (max-width: 1150px) {

  .home-content .sales-boxes{

    flex-direction: column;

  }

  .home-content .sales-boxes .box{

    width: 100%;

    overflow-x: scroll;

    margin-bottom: 30px;

  }

  .home-content .sales-boxes .top-sales{

    margin: 0;

  }

}

@media (max-width: 1000px) {

  .overview-boxes .box{

    width: calc(100% / 2 - 15px);

    margin-bottom: 15px;

  }

}

@media (max-width: 700px) {

  nav .sidebar-button .dashboard,

  nav .profile-details .admin_name,

  nav .profile-details i{

    display: none;

  }

  .home-section nav .profile-details{

    height: 50px;

    min-width: 40px;

  }

  .home-content .sales-boxes .sales-details{

    width: 560px;

  }

}

@media (max-width: 550px) {

  .overview-boxes .box{

    width: 100%;

    margin-bottom: 15px;

  }

  .sidebar.active ~ .home-section nav .profile-details{

    display: none;

  }

}

  @media (max-width: 400px) {

  .sidebar{

    width: 0;

  }

  .sidebar.active{

    width: 60px;

  }

  .home-section{

    width: 100%;

    left: 0;

  }

  .sidebar.active ~ .home-section{

    left: 60px;

    width: calc(100% - 60px);

  }

  .home-section nav{

    width: 100%;

    left: 0;

  }

  .sidebar.active ~ .home-section nav{

    left: 60px;

    width: calc(100% - 60px);

  }

  .logo_name1{

    margin-right:800px;

    margin-bottom:20px;

  }

}

 

/* add animation effects */

@-webkit-keyframes animatetop {

    from {top:-300px; opacity:0}

    to {top:0; opacity:1}

}

 

@keyframes animatetop {

    from {top:-300px; opacity:0}

    to {top:0; opacity:1}

}
    
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

    <!-- <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'> -->

<!--[if lt IE 9]>
<script type="text/javascript" src="js/html5.js"></script>
<link rel="stylesheet" href="css/ie.css" type="text/css" media="screen">
<![endif]-->
</head>
<body id="page1">
<div class="sidebar">

<div class="logo-details">


</div>

<ul class="nav-links">

  <li>
  <a href="supplierindex.php">Dashboard</a>
        <a href="addproductdog.php">Manage Products </a>
        
        <a href="addproductpets.php">Manage Pets</a>
        <a href="viewproduct.php">View Products</a>
        <a href="viewpetstbl.php">View Pets</a>
        <a href="view_orders.php">View Orders</a>
	
	<!-- <?php
				mysqli_close($con);
		?>
	   -->
      
<script>

let sidebar = document.querySelector(".sidebar");

let sidebarBtn = document.querySelector(".sidebarBtn");

sidebarBtn.onclick = function() {

sidebar.classList.toggle("active");

if(sidebar.classList.contains("active")){

sidebarBtn.classList.replace("bx-menu" ,"bx-menu-alt-right");

}else

sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");

}
 </script>
	</html>