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
$subcategoriesQuery = "
    SELECT sc.subid, sc.name AS subcategory_name, c.name AS category_name, sc.status
    FROM subcategory sc
    JOIN category c ON sc.cid = c.cid
";
$subcategoriesResult = mysqli_query($con, $subcategoriesQuery);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>View Subcategories</title>
    
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
        btn {
    padding: 8px 16px;
    font-size: 14px;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-block;
    transition: background-color 0.3s ease;
    color: white; /* Button text color */
}

.activate {
    background-color: #28a745; /* Green for Activate */
}

.deactivate {
    background-color: #dc3545; /* Red for Deactivate */
}

.btn:hover {
    opacity: 0.8;
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
    <h1>Subcategories</h1>
    
    <!-- Back link -->
    
    
    <!-- Table to display subcategories -->
    <table>
        <thead>
            <tr>
            <th>SI No.</th>
                <th>Subcategory Name</th>
                <th>Category Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
       
            <?php 
            
            $counter = 1;
            while ($row = mysqli_fetch_assoc($subcategoriesResult)):
             ?>
                
                <tr>
                <td><?php echo htmlspecialchars($counter); ?></td> 
                    <td><?php echo htmlspecialchars($row['subcategory_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td class="status <?php echo $row['status'] ? 'inactive' : 'active'; ?>">
                        <?php echo $row['status'] ? 'Inactive' : 'Active'; ?>
                    </td>
                    <td class="action-links">
                        <a href="editsubcategory.php?subid=<?php echo $row['subid']; ?>">
                             <i class="fas fa-edit edit-icon"></i>
                        </a>
                        <a href="toggle_subcategory_status.php?subid=<?php echo $row['subid']; ?>&action=<?php echo $row['status'] ? 'activate' : 'deactivate'; ?>" 
   class="btn status-btn <?php echo $row['status'] ? 'activate' : 'deactivate'; ?>">
   <?php echo $row['status'] ? 'Activate' : 'Deactivate'; ?>
</a>
                    </td>
                </tr>
            
            <?php $counter++; // Increment counter ?>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
