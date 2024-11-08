<?php
session_start();
require('connection.php');

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $query = "SELECT * FROM login WHERE reset_token='$token'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        if (isset($_POST['submit'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                $user = mysqli_fetch_assoc($result);
                $email = $user['email'];
                
                // Update the password and clear the reset token
                $update_query = "UPDATE login SET password='$new_password', reset_token=NULL WHERE email='$email'";
                if (mysqli_query($con, $update_query)) {
                    echo "<script>alert('Password updated successfully. You can now login with your new password.'); window.location.href='login.php';</script>";
                } else {
                    echo "<script>alert('Failed to update password. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Passwords do not match. Please try again.');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid or expired reset token.'); window.location.href='login.php';</script>";
    }
} else {
    echo "<script>alert('No reset token provided.'); window.location.href='login.php';</script>";
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.75),rgba(0,0,0,0.75)),url(res.jpg);
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #60adde;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="post">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <input type="submit" name="submit" value="Reset Password">
        </form>
    </div>
</body>
</html>
