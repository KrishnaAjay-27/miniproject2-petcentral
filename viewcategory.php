<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid']) || $_SESSION['u_type'] != 0) {
    header('Location: login.php');
    exit();
}

// Establish database connection

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
$categoriesQuery = "SELECT cid, name, status FROM category";
    $categoriesResult = mysqli_query($con, $categoriesQuery);
    
    // Handle activation/deactivation
    if (isset($_GET['action']) && isset($_GET['cid'])) {
        $action = $_GET['action'];
        $cid = intval($_GET['cid']);
        
        if ($action == 'activate') {
            $updateCategoryQuery = "UPDATE category SET status = 0 WHERE cid = $cid";
            $updateSubcategoryQuery = "UPDATE subcategory SET status = 0 WHERE cid = $cid";
        } elseif ($action == 'deactivate') {
            $updateCategoryQuery = "UPDATE category SET status = 1 WHERE cid = $cid";
            $updateSubcategoryQuery = "UPDATE subcategory SET status = 1 WHERE cid = $cid";
        } else {
            die("Invalid action.");
        }
        
        // Execute the queries
        if (mysqli_query($con, $updateCategoryQuery) && mysqli_query($con, $updateSubcategoryQuery)) {
            header("Location: viewcategory.php");
            exit();
        } else {
            die("Error updating category or subcategory: " . mysqli_error($con));
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <title>View Categories</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: white;
            border: 1px solid #2c3e50;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-links a {
            margin-right: 10px;
            color: black;
            text-decoration: none;
            font-size: 18px;
        }
        .action-links a:hover {
            color:black;
        }
        .action-links .edit-icon {
            color: #3498db; /* Blue for edit */
        }
        .status-button {
            border: none;
            padding: 5px 10px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .activate {
            background-color: #4CAF50;
            transition: background-color 0.3s ease;
        }
        .deactivate {
            background-color: #e74c3c;
            transition: background-color 0.3s ease;
        }
        .status-button:hover {
            opacity: 0.85;
        }
        .back-link {
            font-size: 18px;
            color: #4CAF50;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            position: relative;
            padding-left: 30px;
        }
        .back-link::before {
            content: '\2190';
            font-size: 24px;
            position: absolute;
            left: 0;
            top: 0;
        }
    </style>
</head>
<body>

    <h1>All Categories</h1>
    
    <table>
        <thead>
            <tr>
                <th>Category No.</th>
                <th>Category Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1; // Initialize counter
            while ($row = mysqli_fetch_assoc($categoriesResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($counter); ?></td> <!-- Display counter instead of cid -->
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <?php if ($row['status'] == 0): ?>
                            <span>Active</span>
                        <?php else: ?>
                            <span>Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="action-links">
                        <!-- Edit button with icon -->
                        <a href="editcategory.php?cid=<?php echo htmlspecialchars($row['cid']); ?>">
                            <i class="fas fa-edit edit-icon"></i> <!-- Font Awesome Edit Icon -->
                        </a>
                       
                        <!-- Activate/Deactivate buttons -->
                        <?php if ($row['status'] == 0): ?>
                            <a href="viewcategory.php?action=deactivate&cid=<?php echo htmlspecialchars($row['cid']); ?>" class="status-button deactivate">Deactivate</a>
                        <?php else: ?>
                            <a href="viewcategory.php?action=activate&cid=<?php echo htmlspecialchars($row['cid']); ?>" class="status-button activate">Activate</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php $counter++; // Increment counter ?>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>