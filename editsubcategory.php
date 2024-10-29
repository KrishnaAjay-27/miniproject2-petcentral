<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['subid'])) {
    $subid = intval($_GET['subid']);

    // Fetch the subcategory details
    $subcatQuery = "SELECT * FROM subcategory WHERE subid = $subid";
    $subcatResult = mysqli_query($con, $subcatQuery);
    $subcategory = mysqli_fetch_assoc($subcatResult);

    // Fetch all categories
    $categoriesQuery = "SELECT cid, name FROM category";
    $categoriesResult = mysqli_query($con, $categoriesQuery);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $cid = intval($_POST['category_id']);

    // Update the subcategory
    $updateQuery = "UPDATE subcategory SET name = '$name', cid = $cid WHERE subid = $subid";
    
    if (mysqli_query($con, $updateQuery)) {
        header('Location: viewsubcategory.php');
        exit();
    } else {
        die("Error: " . mysqli_error($con));
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subcategory</title>
    
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
            width: 50%;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #45a049;
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
    </style>
</head>
<body>
    <h1>Edit Subcategory</h1>
    
    <!-- Back link -->
    <div class="back-link">
        <a href="viewsubcategory.php">Back to Subcategories</a>
    </div>
    
    <!-- Edit Subcategory Form -->
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label for="name">Subcategory Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                        <option value="<?php echo $category['cid']; ?>" <?php echo $category['cid'] == $subcategory['cid'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Update Subcategory">
            </div>
        </form>
    </div>
</body>
</html>
