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

// Fetch all categories
$categoriesQuery = "SELECT cid, name FROM category";
$categoriesResult = mysqli_query($con, $categoriesQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $cid = intval($_POST['category_id']);
    
    // Check if subcategory already exists
    $checkQuery = "SELECT * FROM subcategory WHERE name = '$name' AND cid = $cid";
    $checkResult = mysqli_query($con, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $message = "<p class='error-message'>Subcategory already exists!</p>";
    } else {
        // Insert the new subcategory into the database
        $insertQuery = "INSERT INTO subcategory (cid, name) VALUES ($cid, '$name')";
        if (mysqli_query($con, $insertQuery)) {
            $message = "<p class='success-message'>Subcategory added successfully!</p>";
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
    <title>Add Subcategory</title>
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
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 16px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
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
    <a href="adminindex.php" class="back-link">Back to Admin Index</a>
    <h1>Add Subcategory</h1>
    <?php if (isset($message)) echo $message; ?>
    <form id="subcategoryForm" action="addsubcategory.php" method="post">
        <label for="name">Subcategory Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="category">Category:</label>
        <select id="category" name="category_id" required>
            <option value="">Select a category</option>
            <?php
            // Populate the categories dropdown
            while ($row = mysqli_fetch_assoc($categoriesResult)) {
                echo "<option value='{$row['cid']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <button type="submit">Add Subcategory</button>
        <p id="error1">Invalid subcategory name. Only letters and spaces are allowed.</p>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var namePattern = /^[A-Za-z][A-Za-z-\s]+$/;

            $("#subcategoryForm").on("submit", function(event) {
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
