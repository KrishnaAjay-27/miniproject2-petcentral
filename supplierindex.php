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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.3);
            transition: width 0.3s;
        }
        .sidebar .admin-info {
            margin-bottom: 30px;
        }
        .sidebar .admin-info p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            margin: 5px 0;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
        }
        .sidebar a:hover {
            background-color: #34495e;
            color: #ecf0f1;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
            min-height: 100vh;
            background-color: #fff;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        .header .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .header .logout-btn:hover {
            background-color: #c0392b;
        }
       
        .user-count-box {
            background-color: #f1c40f;
            color: black;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            position: absolute;
            left: 20px;
            top: 200px;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-info">
            <p>Welcome Supplier, <?php echo htmlspecialchars($suppliers); ?></p>
        </div>
        <a href="admindashboard.php">Dashboard</a>
        <a href="addproductdog.php">Manage Products </a>
        
        <a href="addproducts.php">Manage Pets</a>
        <a href="viewproduct.php">View Products</a>
        <a href="manage_products.php">View Pets</a>
        <a href="view_orders.php">View Orders</a>
    </div>
    <div class="main-content">
        <div class="header">
            <a href="dashboard.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Dashboard Content</h1>
        <!-- Add your dashboard content here -->
    </div>
</body>
</html>
