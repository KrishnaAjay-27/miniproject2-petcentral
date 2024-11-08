<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}


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
    $suppliers = 'Supplier'; // Default name in case of query failure
}

// Close the database connection
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
        }
        .sidebar {
            background-color: #003366;
            color: white;
            width: 250px;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar h1 {
            margin: 0;
            padding-bottom: 10px;
            font-size: 24px;
            color: white; /* Keep "Welcome" in white */
        }
        .sidebar h2 {
            font-size: 18px;
            font-weight: normal;
            margin: 5px 0 20px;
            color: #cce0ff;
        }
        .nav-links {
            display: flex;
            flex-direction: column;
            width: 100%;
            align-items: center;
        }
        .nav-links a {
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            width: 100%;
            text-align: center;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        .nav-links a:hover {
            background-color: #004080;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .profile-dropdown {
            position: relative;
            display: inline-block;
            margin-top: 20px;
        }
        .profile-btn {
            background-color: transparent;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            padding: 10px;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            background-color: #004080;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 4px;
        }
        .profile-dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .profile-dropdown-content a:hover {
            background-color: #0059b3;
        }
        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #cc0000;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #ff3333;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h1>Welcome Supplier</h1>
        <h2><?php echo htmlspecialchars($suppliers); ?></h2>
        <div class="nav-links">
        <a href="supplierindex.php">Dashboard</a>
        <div class="profile-dropdown">
            <button class="profile-btn">Profile <i class="fa fa-caret-down"></i></button>
            <div class="profile-dropdown-content">
                
                <a href="edit_profilesupplier.php">Edit Profile</a>
                <a href="supplierpassword.php">Change Password</a>
            </div>
        </div>
 
            <a href="addproductdog.php">Manage Products</a>
            <a href="addproductpets.php">Manage Pets</a>
            <a href="viewproduct.php">View Products</a>
            <a href="viewpetstbl.php">View Pets</a>
            <a href="view_orders.php">Order history</a>
            <a href="payment_orders.php">Payment history</a>
        </div>

        <!-- Profile Dropdown -->
        

        <!-- Logout Button -->
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

</body>
</html>
