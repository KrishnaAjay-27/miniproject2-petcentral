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

// Fetch subcategories with category names
$subcategoriesQuery = "
    SELECT sc.subid, sc.name AS subcategory_name, c.name AS category_name, sc.status
    FROM subcategory sc
    JOIN category c ON sc.cid = c.cid
";
$subcategoriesResult = mysqli_query($con, $subcategoriesQuery);

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Subcategories</title>
    
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
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-links a {
            margin-right: 10px;
            color: #4CAF50;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .back-link {
            display: block;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .status {
            font-weight: bold;
        }
        .status.active {
            color: #4CAF50;
        }
        .status.inactive {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <h1>Subcategories</h1>
    
    <!-- Back link -->
    <div class="back-link">
        <a href="adminindex.php">Back to Admin Index</a>
    </div>
    
    <!-- Table to display subcategories -->
    <table>
        <thead>
            <tr>
                <th>Subcategory Name</th>
                <th>Category Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($subcategoriesResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['subcategory_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td class="status <?php echo $row['status'] ? 'inactive' : 'active'; ?>">
                        <?php echo $row['status'] ? 'Inactive' : 'Active'; ?>
                    </td>
                    <td class="action-links">
                        <a href="editsubcategory.php?subid=<?php echo $row['subid']; ?>">Edit</a>
                       
                        <a href="toggle_subcategory_status.php?subid=<?php echo $row['subid']; ?>&action=<?php echo $row['status'] ? 'activate' : 'deactivate'; ?>">
                            <?php echo $row['status'] ? 'Activate' : 'Deactivate'; ?>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
