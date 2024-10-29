<?php
    require('connection.php');
    session_start();
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
   /* @import url("https://fonts.googleapis.com/css?family=Josefin+Sans|Mountains+of+Christmas&display=swap"); */
   

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  list-style: none;
  text-decoration: none;
  font-family: "Josefin Sans", sans-serif;
}

.wrapper{
  position:relative;
}

.wrapper .top_nav{
  margin-top:0;
  width: 100%;
  height: 65px;
  background: #fff;
  padding: 0 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wrapper .top_nav .left{
  display: flex;
  align-items: center;
}

/* .wrapper .top_nav .left .logo p{
  font-size: 24px;
  font-weight: bold;
  color: #494949;
  font-family: "Mountains of Christmas", cursive;
  margin-right: 25px;
} */
.logo{
  margin-left:-35px;
  display:flex;
}
.logo img{
  height:60px;
  width:auto;
}
.logo .span1{
  color:black;
  padding-top:25px;
  left:90px;
  font-size:18px;
}
.logo .span2{
  color:Black;
  padding-top:25px;
  left:90px;
  font-size:18px;
}
.wrapper .top_nav .left .logo p span{
  color: #37a000;
  font-family: "Mountains of Christmas", cursive;
}

.wrapper .top_nav .left .search_bar{
  margin-left:400px;
  position:relative;
}
.wrapper .top_nav .left .search_bar input[type="text"]{
  height:40px;
        padding:20px;
        border:1px solid #d9d9d9;
        width:400px;
        margin-top:5px;
        margin-left:30px;
        background:#f9f9f9;
        font-size:15px;
}

.wrapper .top_nav .left .search_bar input[type="text"]:focus{
  width: 250px;
}

.wrapper .top_nav .right ul{
  display: flex;
}

.wrapper .top_nav .right ul li{
  margin: 0 12px;
}

.wrapper .top_nav .right ul li:last-child{
  /* background:  #37a000; */
  margin-right: 0;
  border-radius: 2px;
  text-transform: uppercase;
  letter-spacing: 3px;
}

/* .wrapper .top_nav .right ul li:hover:last-child{
  background: #494949;
} */

.wrapper .top_nav .right ul li a{
  display: block;
  padding: 8px 10px;
  color: #666666;
}

.wrapper .top_nav .right ul li:last-child a{
   color: white;
}

.wrapper .bottom_nav{
  width: 100%;
  background: #f9c74f;
  height: 45px;
  padding-left:270px;
}

.wrapper .bottom_nav ul{
  width: 80%;
  height: 45px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wrapper .bottom_nav ul li a{
  color:black;
  text:bold;
  letter-spacing: 2px;
  text-transform: uppercase;
  width:80px;
  font-size: 12px;
}


.parent-menu {
  display: inline-block;
  position: relative;
}

.parent-menu .submenu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 120px;
  z-index: 1;
  height:160px;
  background:white;
}

.parent-menu:hover .submenu {
  display: block;
}

.submenu a {
  display: block;
  text-decoration: none;
  color: #494949;
  height:40px;
}

.submenu a:hover {
  background-color: #f2f2f2;
}
#button {
  display: block;
  width: 100%;
  height:16px;
  border: none;
  border-radius: 4px;
  background-color: transparent;
  color: black;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}



.submenu2{
  display:none;
  position: absolute;
  top: 90%;
  left: 1000px;
  width: 200px;
  z-index: 1;
  height:120px;
  background:white;
}
.customised:hover .submenu2 {
  display: block;
}


.submenu2 #buttons:hover {
  background-color: yellowgreen;
  width:200px;
}
#buttons {
  display: block;
  width: 100%;
  height:60px;
  border: none;
  border-radius: 4px;
  background-color: transparent;
  color: black;
  font-size: 16px;
  cursor: pointer;
  padding-top:10px;
  transition: background-color 0.3s ease;
}
  </style>
</head>
<body>
<div class="wrapper">
    <div class="top_nav">
        <div class="left">
          <div class="logo"><span class="span1">Pet</span><span class="span2">Central</span></div>
          <!-- <div class="search_bar">
          <form action="products.php" method="GET">
  <input type="text" name="search" placeholder="Search products">
  <button type="submit">Search</button>
</form>
          </div> -->
      </div> 
      <div class="right">
        <ul>
          <!-- <li><a href="login.php#login">LogIn</a></li>
          <li><a href="login.php#register">SignUp</a></li> -->

          <?php
                               if(isset($_SESSION['uid']))
                               {
                               $userid=$_SESSION['uid'];
                               $query="select * from registration where lid='$userid'";
                               $re=mysqli_query($con,$query);
                               $row=mysqli_fetch_array($re);
                               ?>
                              <li class="parent-menu" style='margin-top:7px;font-size: 16px;'>
    <i class='fa fa-user' style='color: #494949;padding-right:5px;'></i>
    <?php echo $row['name'] ; ?>
    <div class="submenu">
        <a href="userdashboard.php"><b><input type="submit" value="Profile" id="button"/></b></a>
        <a href="editprofile.php"><b><input type="submit" value="Edit Profile" id="button"/></a></b>
        <a href="userpass.php"><b><input type="submit" value="Change Password" id="button"/></a></b>
        <a href="logout.php"><b><input type="submit" value="Logout" id="button"/></a></b>
    </div>
</li>
<?php
                              }
                              else{   
                              ?>
                                  <li><a href="login.php">LogIn</a></li>
                              <?php
                              }?>
          <li><a href="mywishlist.php" title="My Wishlist"><i class="fa fa-heart"></i></a></li>
            
            <li><a href="mycart.php"><i class="fa fa-shopping-cart"></i></a></li>
            <li style="margin-left:-20px;"></li>
            <?php
            
            
              ?>
              <!-- <li><a href="mycart.php"><i style="color:grey;" class="fa fa-shopping-cart"></i></a></li> -->
              <?php
            
            ?>
        </ul>
      </div>
    </div>
    <div class="bottom_nav">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About us</a></li>
        <li><a href="contact.php">contact</a></li>
        <li><a href="shops.php">Products</a></li>
        <!-- <li class="customised">customised -->
        <!-- <div class="submenu2">
        <a href="cake_order.php"><b><input type="submit" value="Customised cakes" id="buttons"/></b></a>
        <a href="custom_gifts.php"><b><input type="submit" value="customised gifts" id="buttons"/></b></a>
    </div> -->
        </li>
        
      </ul>
      
  </div>
  
</div>
</body>
</html>