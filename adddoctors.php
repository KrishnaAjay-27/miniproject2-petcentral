<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid']) || $_SESSION['u_type'] != 0) {
    header('Location: login.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php'; // Adjust the path if needed

function sendVerificationEmail($email, $name, $password, $doctor_code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Turn off debugging for production
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'petcentral68@gmail.com'; // SMTP username
        $mail->Password = 'qgsi fbbr fupn vzyh'; // App password (use your actual app password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('petcentral68@gmail.com', 'PetCentral');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body = 'Dear ' . $name . ',<br><br>' .
            'Thank you for registering with us. Here are your registration details:<br><br>' .
            'Email ID: <b>' . $email . '</b><br>' .
            'Password: <b>' . $password . '</b><br>' . // Avoid sending plain passwords in production
            'Doctor Code: <b>' . $doctor_code . '</b><br><br>' .
            'Best regards,<br>Your PetCentral';

        $mail->send();
        echo 'Verification email has been sent';
    } catch (Exception $e) {
        echo "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
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



mysqli_close($con);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add supplier</title>
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
        
    
    
 
    


        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            margin-left: 20%;
            margin-top: 8%;
        
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            color: #007BFF;
            text-decoration: none;
            transition: color 0.3s;
        }

        .container .back-button:hover {
            color: #0056b3;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container .input-box {
            margin-bottom: 15px;
        }

        .container .input-box input {
            width: 100%;
          
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .container .input-box input[type="submit"] {
            background:#2c3e50;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .container .input-box input[type="submit"]:hover {
            background:#2c3e50;
        }

        .container .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
   
    <div class="container">
       
        <h2>Add The Doctor</h2>
        <form id="form" method="POST" action="adddoctors.php" enctype="multipart/form-data">
            <div class="input-box">
                <input type="text" placeholder="Enter your Name" name="name" id="p1" required />
                <p id="error1" class="error">Enter Valid Name</p>
            </div>
            <div class="input-box">
                <input type="email" placeholder="Enter your Email id" name="email" id="p3" required />
                <p id="error3" class="error">Enter Valid Email</p>
            </div>
            <div class="input-box">
                <input type="password" placeholder="Enter your Password" name="password" id="p6" required />
                <p id="error6" class="error">Enter Valid Password. Password must have Uppercase, special character, and number.</p>
            </div>
            <div class="input-box">
                <input type="password" placeholder="Confirm Password again" name="pass2" id="p7" required />
                <p id="error7" class="error">Password Doesn't Match</p>
            </div>
            <div class="input-box">
                <input type="submit" class="btn" name="submit" value="Submit" />
            </div>
            
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#error1").hide();
            $("#error3").hide();
            $("#error4").hide();
            $("#error5").hide();
            $("#error6").hide();
            $("#error7").hide();

            var name = /^[A-Za-z][A-Za-z-\s]+$/;
            $("#p1").keyup(function() {
                var x = document.getElementById("p1").value;
                if (name.test(x) == false) {
                    $("#error1").show();
                } else {
                    $("#error1").hide();
                }
            });

            var mail = /^\w+([\.-]?\w+)*(@gmail|@yahoo)+([\.-]?\w+)*(\.\w{2,3})+$/;
            $("#p3").keyup(function() {
                var x = document.getElementById("p3").value;
                if (mail.test(x) == false) {
                    $("#error3").show();
                } else {
                    $("#error3").hide();
                }
            });

            var psw1 = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
            $("#p6").keyup(function() {
                var x1 = document.getElementById("p6").value;
                if (psw1.test(x1) == false) {
                    $("#error6").show();
                } else {
                    $("#error6").hide();
                }
            });

            $("#p7").keyup(function() {
                if (document.getElementById("p6").value != document.getElementById("p7").value) {
                    $("#error7").show();
                } else {
                    $("#error7").hide();
                }
            });

            $(".btn").click(function() {
                var f1 = $("#error1").is(":visible");
                var f2 = $("#error3").is(":visible");
                var f6 = $("#error6").is(":visible");
                var f7 = $("#error7").is(":visible");

                if (f1 || f2 || f6 || f7) {
                    alert('Please Fill The Form Correctly');
                    return false;
                }
                return true;
            });
        });
    </script>
</body>
</html>

<?php
function generateUniqueSupplierCode() {
    $timestamp = microtime(true);
    $randomNumber = rand(1000, 9999);
    return strtoupper(substr(md5($timestamp . $randomNumber), 0, 10));
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $doctor_code = generateUniqueSupplierCode();

    $con = mysqli_connect("localhost", "root", "", "project");

    // Check if email already exists
    $email_check_query = "SELECT * FROM d_registration WHERE email='$email' LIMIT 1";
    $result = mysqli_query($con, $email_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['email'] === $email) {
            echo "<script>alert('Email already exists, please use a different email.');</script>";
        }
    } else {
        // Insert into login table
        $query = "INSERT INTO login (email, password, u_type) VALUES ('$email', '$password', 3)";
        $re = mysqli_query($con, $query);

        if ($re) {
            // Retrieve the last inserted lid
            $lid = mysqli_insert_id($con);

            // Insert into s_registration table
            $query = "INSERT INTO d_registration (lid, name, email, u_type, doctor_code) VALUES ('$lid', '$name', '$email', 3, '$doctor_code')";
            $re = mysqli_query($con, $query);

            if ($re) {
                sendVerificationEmail($email, $name, $password, $doctor_code);
                echo "<script>alert('Registration successful. A verification email has been sent.');</script>";
            } else {
                echo "<script>alert('Failed to register.');</script>";
            }
        } else {
            echo "<script>alert('Failed to register.');</script>";
        }
    }
    mysqli_close($con);
}
?>