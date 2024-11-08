<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid']) || $_SESSION['u_type'] != 0) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $admin = mysqli_fetch_assoc($result);
    $adminEmail = $admin ? $admin['email'] : 'Admin';
} else {
    $adminEmail = 'Admin'; // Default email in case of query failure
}

// Handle activate/deactivate requests
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['cid'])) {
    die("Category ID is required.");
}

$cid = intval($_GET['cid']);

// Fetch category details
$categoryQuery = "SELECT name FROM category WHERE cid = $cid";
$categoryResult = mysqli_query($con, $categoryQuery);
$category = mysqli_fetch_assoc($categoryResult);

// Handle form submission for updating category
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    
    $updateQuery = "UPDATE category SET name = '$name' WHERE cid = $cid";
    if (mysqli_query($con, $updateQuery)) {
        header("Location: viewcategory.php");
        exit();
    } else {
        die("Error updating category: " . mysqli_error($con));
    }
}

// Establish database connection


// Fetch all categories

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>category</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn.activate {
            background-color: #2ecc71; /* Green for activate */
            color: white;
        }
        .btn.deactivate {
            background-color: #e74c3c; /* Red for deactivate */
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .logout-btn {
            background-color: #e74c3c; /* Red for logout */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
            display: inline-block;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-info">
            <p>Welcome, <?php echo htmlspecialchars($adminEmail); ?></p>
        </div>
        <a href="admindashboard.php">Dashboard</a>
        <a href="manageuseradmin.php">Manage Users</a>
        <a href="addcategory.php">Manage Categories </a>
        <a href="addsubcategory.php">Manage Subcategory</a>
        <a href="viewcategory.php">View Categories</a>
        <a href="viewsubcategory.php">View Sub categories</a>
        <a href="addsuppliers.php">Add Suppliers</a>
        <a href="managesupplieadmin.php">Manage Suppliers</a>
        <a href="fetch_products.php">View Products</a>
    </div>
    <div class="main-content">
        <div class="header">
            <a href="adminindex.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
    
    

    



    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <style>
        /* Internal styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 100px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Edit Category</h1>
        <form method="post">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            <button type="submit">Update Category</button>
        </form>
    </div>
</body>
</html>
