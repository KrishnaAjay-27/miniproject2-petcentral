<?php
    require('connection.php');
    session_start();
    $notification_count = 0;
if (isset($_SESSION['uid'])) {
    $user_id = $_SESSION['uid'];
    $count_query = "SELECT COUNT(*) as count FROM notifications WHERE lid = ? AND is_read = 0";
    $stmt = $con->prepare($count_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $notification_count = $row['count'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

.wrapper .bottom_nav ul li a {
    color: black;
    text-decoration: none !important;
    letter-spacing: 2px;
    text-transform: uppercase;
    width: 80px;
    font-size: 12px;
    position: relative;
    padding: 8px 15px;
    transition: all 0.3s ease;
}

.wrapper .bottom_nav ul li a:hover {
    color: #333;
    background-color: rgba(255, 255, 255, 0.2);
    text-decoration: none !important;
}

.parent-menu {
  position: relative;
   padding: 8px 7px; 
 
}


.parent-menu > a {
  position: relative;
  padding: 10px 15px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
}

.parent-menu > a::after {
  content: '\f107';
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  margin-left: 5px;
  transition: transform 0.3s ease;
}

.parent-menu:hover > a::after {
  transform: rotate(180deg);
}

.submenu {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  min-width: 200px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  transition: all 0.3s ease;
  z-index: 1000;
  padding: 8px 0;
}

.parent-menu:hover .submenu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.submenu a {
  display: flex;
  padding: 12px 20px;
  color: #333;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 14px;
  align-items: center;
}

.submenu a::before {
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  margin-right: 10px;
  font-size: 14px;
}

.submenu a[href*="food"]::before {
  content: '\f2e7';
  color: #ff6b6b;
}

.submenu a[href*="accessories"]::before {
  content: '\f1b0';
  color: #4ecdc4;
}

.submenu a[href*="grooming"]::before {
  content: '\f6fc';
  color: #45b7d1;
}

.submenu a:hover {
  background: #f8f9fa;
  padding-left: 25px;
}

.submenu a:not(:last-child) {
  border-bottom: 1px solid #f1f1f1;
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
.navbar {
        display: flex;
        list-style: none;
    }

    .navbar li {
        position: relative;
        margin: 0 15px;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        padding: 10px;
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

.dropdown {
        display: none;
        position: absolute;
        background-color: #444;
        min-width: 160px;
        z-index: 1;
    }

    .navbar li:hover .dropdown {
        display: block;
    }

    .dropdown a {
        padding: 10px;
        display: block;
        color: white;
    }
    
    .dropdown a:hover {
        background-color: #555;
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
/* Add this to your styles.css */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #f8f8f8; /* Header background color */
}

nav ul {
    list-style: none;
    display: flex;
    gap: 15px; /* Space between menu items */
}

nav a {
    text-decoration: none;
    color: #333; /* Link color */
}

.notification-icon {
    position: relative; /* Position relative for the count */
}

.notification-icon a {
    text-decoration: none;
    color: #333; /* Icon color */
    font-size: 24px; /* Icon size */
}

.notification-count {
    position: absolute;
    top: -5px; /* Adjust position */
    right: -10px; /* Adjust position */
    background-color: red; /* Background color for count */
    color: white; /* Text color for count */
    border-radius: 50%; /* Make it circular */
    padding: 2px 6px; /* Padding for the count */
    font-size: 12px; /* Font size for the count */
}
.search_bar {
  margin-left: 400px;
  position: relative;
}

.search_bar input[type="text"] {
  height: 40px;
  padding: 20px;
  border: 1px solid #d9d9d9;
  width: 400px;
  margin-top: 5px;
  margin-left: 30px;
  background: #f9f9f9;
  font-size: 15px;
}

.search_bar input[type="text"]:focus {
  width: 250px;
}

.search_bar #delivery-status {
  display: block;
  margin-top: 5px;
  color: black; /* Light grey color for the placeholder text */
  font-size: 14px;
}

.bottom_nav ul li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    background: #333;
    left: 0;
    bottom: -2px;
    transition: width 0.3s ease;
    opacity: 0;
}

.bottom_nav ul li a:hover::after {
    width: 100%;
    opacity: 1;
}

  </style>
</head>
<body>
<div class="wrapper">
    <div class="top_nav">
        <div class="left">
          <div class="logo"><span class="span1">Pet</span><span class="span2">Central</span></div>
          <div class="search_bar">
  <input type="text" id="pincode" placeholder="Enter Pincode" oninput="checkPincode()">
  <span id="delivery-status">Enter the pincode to check whether it is deliverable or not</span>
</div>

          <!-- <div class="search_bar">
          <form action="products.php" method="GET">
  <input type="text" name="search" placeholder="Search products">
  <button type="submit">Search</button>
</form>
          </div> -->
      </div> 
      <div class="right">
        <ul>
        <div class="notification-icon">
                <a href="notifications.php" onclick="markAsRead()">
                    <i class="fas fa-bell"></i> <!-- Notification Icon -->
                    <span class="notification-count"><?php echo $notification_count; ?></span> <!-- Notification count -->
                </a>
            </div>
            <script>
        function markAsRead() {
            // Make an AJAX call to mark notifications as read
            fetch('mark_notifications_read.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Notifications marked as read.");
                    } else {
                        console.error("Failed to mark notifications as read.");
                    }
                });
        }
        
        function checkPincode() {
  const pincode = document.getElementById('pincode').value;
  const deliveryStatus = document.getElementById('delivery-status');

  if (pincode.trim() === "") {
    deliveryStatus.textContent = "Enter the pincode to check whether it is deliverable or not";
    deliveryStatus.style.color = "#999"; // Light grey color
  } else if (/^68\d{4}$/.test(pincode)) {
    deliveryStatus.textContent = "Product Delivery Available to Your Place";
    deliveryStatus.style.color = "green";
  } else {
    deliveryStatus.textContent = "Product Delivery Not Available to Your Place";
    deliveryStatus.style.color = "red";
  }
}

    </script>
    <li>
                    <a href="trackorder.php" title="My Orders">
                    <i class="fas fa-truck"></i> <!-- My Orders Icon -->
                    </a>
                </li>
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
        <a href="userdashboard.php"><b><input type="submit" value="Dashboard" id="button"/></b></a>
        
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
        <li><a href="userindex.php">Home</a></li>
        <li><a href="book_appointments.php">Appointments</a></li>
        <li><a href="image.php">Image Processing</a></li>
        <li><a href="view_video_by_user.php">Videos</a></li>
        <li><a href="uploads.php">Disease</a></li>
        <li><a href="doctorview.php">Chat with doctor</a></li>
        <li class="parent-menu">
          <a href="shops.php"> All Products </a>
        </li>

        <li class="parent-menu">
    <a href="#">Dog</a>
    <div class="submenu">
        <a href="dogfood.php"><b>Dog Food</b></a>
        <a href="dogaccessories.php"><b>Dog Accessories</b></a>
        <a href="doggrooming.php"><b>Dog Grooming</b></a>
    </div>
</li>

<li class="parent-menu">
    <a href="#">Cat</a>
    <div class="submenu">
        <a href="catfood.php"><b>Cat Food</b></a>
        <a href="cataccessories.php"><b>Cat Accessories</b></a>
        <a href="catgrooming.php"><b>Cat Grooming</b></a>
    </div>
</li>
<li class="parent-menu">
    <a href="displaydog.php">Pets</a>
    <!-- <div class="submenu"> <!-- Submenu for Dog -->
        <!-- <a href="displaydog.php"><b>Dog</b></a>
        <a href="displaycat.php"><b>Cat</b></a> --> 
      
    </div>
</li>

    </ul>
    </div>
</div>
</body>
</html>
