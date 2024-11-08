<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php'; // Adjust the path if needed

session_start();
require('connection.php');

$con = mysqli_connect("localhost", "root", "", "project");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    
    // Check if the email exists in the database
    $query = "SELECT * FROM login WHERE email='$email'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        
        // Check if reset_token column exists
        $check_column_query = "SHOW COLUMNS FROM login LIKE 'reset_token'";
        $column_result = mysqli_query($con, $check_column_query);
        
        if (mysqli_num_rows($column_result) == 0) {
            // Add reset_token column if it doesn't exist
            $add_column_query = "ALTER TABLE login ADD COLUMN reset_token VARCHAR(255)";
            mysqli_query($con, $add_column_query);
        }
        
        // Store the token in the database
        $update_query = "UPDATE login SET reset_token='$token' WHERE email='$email'";
        mysqli_query($con, $update_query);
        
        // Send reset email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'petcentral68@gmail.com';
            $mail->Password = 'qgsi fbbr fupn vzyh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('petcentral68@gmail.com', 'PetCentral');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Please click the following link to reset your password: <a href='http://localhost/Minproject%20Pet%20central/reset_password.php?token=$token'>Reset Password</a>";

            $mail->send();
            echo "<script>alert('Password reset link has been sent to your email.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('No account found with that email address.');</script>";
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        input[type="email"] {
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
        <h2>Forgot Password</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" name="submit" value="Reset Password">
        </form>
    </div>
</body>
</html>