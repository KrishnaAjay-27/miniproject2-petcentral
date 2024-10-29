<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch categories
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

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Categories</title>
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
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
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
        .action-links a {
            margin-right: 10px;
            color: black;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .status-button {
            border: none;
            padding: 5px 10px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        .activate {
            background-color: #4CAF50;
        }
        .deactivate {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
    <a href="adminindex.php" class="back-link">Back to Admin Index</a>
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
                        <a href="editcategory.php?cid=<?php echo htmlspecialchars($row['cid']); ?>">Edit</a>
                       
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
