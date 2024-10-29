<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php'; // Adjust the path if needed

function sendVerificationEmail($email, $name, $password, $supplier_code) {
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
            'Supplier Code: <b>' . $supplier_code . '</b><br><br>' .
            'Best regards,<br>Your PetCentral';

        $mail->send();
        echo 'Verification email has been sent';
    } catch (Exception $e) {
        echo "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
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
            background: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .container .input-box input[type="submit"]:hover {
            background: #0056b3;
        }

        .container .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
     <a href="adminindex.php" class="back-button">&#9664; Back</a>
    <div class="container">
       
        <h2>Register</h2>
        <form id="form" method="POST" action="addsuppliers.php" enctype="multipart/form-data">
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
    $supplier_code = generateUniqueSupplierCode();

    $con = mysqli_connect("localhost", "root", "", "project");

    // Check if email already exists
    $email_check_query = "SELECT * FROM s_registration WHERE email='$email' LIMIT 1";
    $result = mysqli_query($con, $email_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['email'] === $email) {
            echo "<script>alert('Email already exists, please use a different email.');</script>";
        }
    } else {
        // Insert into login table
        $query = "INSERT INTO login (email, password, u_type) VALUES ('$email', '$password', 2)";
        $re = mysqli_query($con, $query);

        if ($re) {
            // Retrieve the last inserted lid
            $lid = mysqli_insert_id($con);

            // Insert into s_registration table
            $query = "INSERT INTO s_registration (lid, name, email, u_type, supplier_code) VALUES ('$lid', '$name', '$email', 2, '$supplier_code')";
            $re = mysqli_query($con, $query);

            if ($re) {
                sendVerificationEmail($email, $name, $password, $supplier_code);
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