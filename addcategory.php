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
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'activate') {
        $status = 0; // Set status to active
    } elseif ($action == 'deactivate') {
        $status = 1; // Set status to inactive
    } else {
        die("Invalid action");
    }

    // Prepare and execute the update query
    $updateQuery = "UPDATE login SET status=? WHERE lid=?";
    $stmt = mysqli_prepare($con, $updateQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $status, $userId);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: manageuseradmin.php'); // Redirect to reflect changes
            exit();
        } else {
            echo "Update failed: " . mysqli_stmt_error($stmt); // Debugging output
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare failed: " . mysqli_error($con); // Debugging output
    }
}

// Fetch user details from the 'registration' table
$userQuery = "SELECT r.name, r.email, r.phone, r.address, l.status, l.lid
              FROM registration r
              JOIN login l ON r.lid = l.lid";
$userResult = mysqli_query($con, $userQuery);

if (!$userResult) {
    die("Query failed: " . mysqli_error($con));
}

// Close the database connection
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
        <a href="adddoctors.php">Add Doctors</a>
        <a href="managesupplieadmin.php">Manage Suppliers</a>
        <a href="fetch_products.php">View Products</a>
    </div>
    <div class="main-content">
        <div class="header">
            <a href="adminindex.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <?php
        if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);

    // Check if category already exists
    $checkQuery = "SELECT * FROM category WHERE name = '$name'";
    $checkResult = mysqli_query($con, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $message = "<p class='error-message'>Category already exists!</p>";
    } else {
        // Insert the new category into the database
        $insertQuery = "INSERT INTO category (name) VALUES ('$name')";
        if (mysqli_query($con, $insertQuery)) {
            $message = "<p class='success-message'>Category added successfully!</p>";
        } else {
            $message = "<p class='error-message'>Error: " . mysqli_error($con) . "</p>";
        }
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link rel="stylesheet" href="style.css"> <!-- External stylesheet link -->
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
            content: '\2190'; /* Unicode for left arrow */
            font-size: 24px;
            position: absolute;
            left: 0;
            top: 0;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }
        button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2c3e50;
        }
        .success-message {
            color: #4CAF50;
            text-align: center;
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
        }
        #error1 {
            color: #e74c3c;
            display: none;
        }
    </style>
</head>
<body>
  
    <h1>Add Category</h1>
    <?php if (isset($message)) echo $message; ?>
    <form id="categoryForm" action="addcategory.php" method="post">
        <label for="name">Category Name:</label>
        <input type="text" id="name" name="name" required>

        <button type="submit">Add Category</button>
        <p id="error1">Invalid category name. Only letters and spaces are allowed.</p>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var namePattern = /^[A-Za-z][A-Za-z-\s]+$/;

            $("#categoryForm").on("submit", function(event) {
                var name = $("#name").val();
                if (!namePattern.test(name)) {
                    $("#error1").show();
                    event.preventDefault();
                }
            });

            $("#name").keyup(function() {
                var name = $(this).val();
                if (namePattern.test(name)) {
                    $("#error1").hide();
                } else {
                    $("#error1").show();
                }
            });
        });
    </script>
</body>
</html>
