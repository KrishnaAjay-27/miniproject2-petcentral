<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid']) || $_SESSION['u_type'] != 0) {
    header('Location: login.php');
    exit();
}

// Establish a single database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch admin email
$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
$adminEmail = $admin ? $admin['email'] : 'Admin';

// Fetch all categories
$categoriesQuery = "SELECT cid, name FROM category";
$categoriesResult = mysqli_query($con, $categoriesQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $cid = intval($_POST['category_id']);

    // Check if subcategory exists
    $checkQuery = "SELECT * FROM subcategory WHERE name = ? AND cid = ?";
    $stmt = mysqli_prepare($con, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'si', $name, $cid);
    mysqli_stmt_execute($stmt);
    $checkResult = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($checkResult) > 0) {
        $message = "<p class='error-message'>Subcategory already exists!</p>";
    } else {
        $insertQuery = "INSERT INTO subcategory (cid, name) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'is', $cid, $name);

        if (mysqli_stmt_execute($stmt)) {
            $message = "<p class='success-message'>Subcategory added successfully!</p>";
        } else {
            $message = "<p class='error-message'>Error: " . mysqli_error($con) . "</p>";
        }
    }
}

// Fetch all existing subcategories
$subcategoryQuery = "SELECT name FROM subcategory";
$subcategoryResult = mysqli_query($con, $subcategoryQuery);
$existingSubcategories = [];
while ($subcat = mysqli_fetch_assoc($subcategoryResult)) {
    $existingSubcategories[] = $subcat['name'];
}


mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subcategory</title>
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
        }

        .sidebar .admin-info p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .header .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .header .logout-btn:hover {
            background-color: #c0392b;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
            margin-top: 50px;
        }

        input, select, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .success-message, .error-message {
            text-align: center;
            font-weight: bold;
        }

        .success-message {
            color: #27ae60;
        }

        .error-message {
            color: #e74c3c;
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
    <a href="addcategory.php">Manage Categories</a>
    <a href="addsubcategory.php">Manage Subcategory</a>
    <a href="viewcategory.php">View Categories</a>
    <a href="viewsubcategory.php">View Subcategories</a>
    <a href="addsuppliers.php">Add Suppliers</a>
    <a href="adddoctors.php">Add Doctors</a>
    <a href="managesupplieadmin.php">Manage Suppliers</a>
    <a href="fetch_products.php">View Products</a>
</div>

<div class="main-content">
    <div class="header">
        <a href="admindashboard.php" class="logo">Admin Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if (isset($message)) echo $message; ?>
    <p style="text-align: center; font-weight: bold;">Add Subcategory</p>

    <form action="addsubcategory.php" method="post">
        <label for="category">Category:</label>
        <select id="category" name="category_id" required>
            <option value="">Select a category</option>
            <?php while ($row = mysqli_fetch_assoc($categoriesResult)) {
                echo "<option value='{$row['cid']}'>{$row['name']}</option>";
            } ?>
        </select>

        <label for="name">Subcategory Name:</label>
        <input type="text" id="name" name="name" required>

        <button type="submit">Add Subcategory</button>
       
   
<script>
    // JavaScript validation for duplicate subcategories
    document.addEventListener('DOMContentLoaded', () => {
        const existingSubcategories = <?php echo json_encode($existingSubcategories); ?>;
        const form = document.querySelector('form');
        const nameInput = document.getElementById('name');
        
        form.addEventListener('submit', (event) => {
            const subcategoryName = nameInput.value.trim();
            if (existingSubcategories.includes(subcategoryName)) {
                event.preventDefault();
                alert('This subcategory already exists!');
            }
        });
    });
</script>

    </form>
</div>

</body>
</html>
