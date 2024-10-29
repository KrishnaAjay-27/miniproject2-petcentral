<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Collect form data
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];

    // Validate form data
    $errors = [];
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($district)) $errors[] = "District is required.";

    if (empty($errors)) {
        // Establish database connection
        $con = mysqli_connect("localhost", "root", "", "project");
        if (!$con) die("Connection failed: " . mysqli_connect_error());

        // Sanitize input
        $phone = mysqli_real_escape_string($con, $phone);
        $address = mysqli_real_escape_string($con, $address);
        $state = mysqli_real_escape_string($con, $state);
        $district = mysqli_real_escape_string($con, $district);
        $lid = $_SESSION['uid'];

        // Prepare the query
        $query = "UPDATE s_registration 
                  SET phone='$phone', address='$address', state='$state', district='$district' 
                  WHERE lid='$lid'";

        // Execute the query
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Profile updated successfully.'); window.location.href = 'supplierindex.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        }

        mysqli_close($con);
    } else {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile</title>
    <style>
        /* Basic styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 500px;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .input-box {
            margin-bottom: 15px;
        }
        .input-box label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .input-box input, .input-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .button {
            margin-top: 20px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            background: #60adde;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Complete Your Profile</h2>
        <form method="post">
            <div class="input-box">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" id="phone" required>
            </div>
            <div class="input-box">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address" required>
            </div>
            <div class="input-box">
                <label for="state">State:</label>
                <input type="text" name="state" id="state" value="Kerala" readonly required>
            </div>
            <div class="input-box">
                <label for="district">District:</label>
                <select name="district" id="district" required>
                    <option value="">Select District</option>
                    <option value="Alappuzha">Alappuzha</option>
                    <option value="Ernakulam">Ernakulam</option>
                    <option value="Idukki">Idukki</option>
                    <option value="Kannur">Kannur</option>
                    <option value="Kasaragod">Kasaragod</option>
                    <option value="Kollam">Kollam</option>
                    <option value="Kottayam">Kottayam</option>
                    <option value="Kozhikode">Kozhikode</option>
                    <option value="Malappuram">Malappuram</option>
                    <option value="Palakkad">Palakkad</option>
                    <option value="Pathanamthitta">Pathanamthitta</option>
                    <option value="Thrissur">Thrissur</option>
                    <option value="Wayanad">Wayanad</option>
                </select>
            </div>
            <div class="button">
                <input type="submit" class="btn" name="submit" value="Complete Profile">
            </div>
        </form>
    </div>
</body>
</html>
