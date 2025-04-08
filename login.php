<?php
session_start();
require('connection.php');

if (isset($_GET['message'])) {
    echo '<div class="message success">' . htmlspecialchars($_GET['message']) . '</div>';
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Updated query to check both registration and vaccination_centers tables
    $query = "SELECT l.*, r.verified, 
              COALESCE(vc.status) as account_status 
              FROM login l 
              LEFT JOIN registration r ON l.lid = r.lid 
              LEFT JOIN vaccination_centers vc ON l.lid = vc.lid 
              WHERE l.email='$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password === $user['password']) {
            // For u_type=1, check if email is verified
            if ($user['u_type'] == 1 && $user['verified'] != 1) {
                echo "<script>alert('Please verify your email before logging in.'); window.location.href='login.php';</script>";
                exit();
            }

            // For vaccination centers, check if approved
            if ($user['u_type'] == 5 && $user['account_status'] != 1) {
                echo "<script>alert('Your vaccination center account is pending approval or has been rejected.'); window.location.href='login.php';</script>";
                exit();
            }

            $_SESSION['uid'] = $user['lid'];
            $_SESSION['u_type'] = $user['u_type'];

            // Redirect based on user type
            switch ($user['u_type']) {
                case 0:
                    header('Location: adminindex.php');
                    break;
                case 1:
                    // Check for incomplete profile
                    $profileQuery = "SELECT * FROM registration WHERE lid=" . $_SESSION['uid'];
                    $profileResult = mysqli_query($con, $profileQuery);
                    $profile = mysqli_fetch_assoc($profileResult);
                    if (empty($profile['landmark']) || empty($profile['pincode']) || empty($profile['roadname']) || empty($profile['district']) || empty($profile['state'])) {
                        header('Location: complete_profile.php');
                    } else {
                        header('Location: userindex.php');
                    }
                    exit();
                case 2:
                    $lid = $_SESSION['uid'];
                    $result = mysqli_query($con, "SELECT * FROM s_registration WHERE lid='$lid'");
                    $user = mysqli_fetch_assoc($result);
                    if (empty($user['phone']) || empty($user['address']) || empty($user['state']) || empty($user['district'])) {
                        header('Location: suppliercomplete_profile.php');
                    } else {
                        header('Location: supplierindex.php');
                    }
                    exit();
                case 3:
                    $lid = $_SESSION['uid'];
                    $result = mysqli_query($con, "SELECT * FROM d_registration WHERE lid='$lid'");
                    $user = mysqli_fetch_assoc($result);
                    if (empty($user['phone']) || empty($user['address']) || empty($user['state']) || empty($user['district']) || empty($user['experience']) || empty($user['Qualification']) || empty($user['image1']) || empty($user['certificateimg2'])) {
                        header('Location: doctorcomplete_profile.php');
                    } else {
                        header('Location: doctorindex.php');
                    }
                    exit();
                case 4:
                    header('Location: deliveryindex.php');
                    exit();
                case 5:
                    // Vaccination Center
                    $lid = $_SESSION['uid'];
                    $result = mysqli_query($con, "SELECT status FROM vaccination_centers WHERE lid='$lid'");
                    $center = mysqli_fetch_assoc($result);
                    
                    if ($center && $center['status'] == 1) {
                        header('Location: vaccination_center_index.php');
                    } else {
                        session_destroy();
                        echo "<script>
                            alert('Your vaccination center account is pending approval or has been rejected.');
                            window.location.href='login.php';
                        </script>";
                    }
                    exit();
            }
        } else {
            echo "<script>alert('Email and password do not match.');</script>";
        }
    } else {
        echo "<script>alert('No user found with this email.');</script>";
    }

    mysqli_close($con);
}
?>

<!DOCTYPE html>
<head>
    <meta name="google-signin-client_id" content="151430511839-rm5ljn03n9qpf98nsh9od7q1h0vc319l.apps.googleusercontent.com">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-image: linear-gradient(rgba(0,0,0,0.75),rgba(0,0,0,0.75)),url(res.jpg);
            background-size: 1650px 900px;
            align-items: center;
            background-repeat: no-repeat;
        }

        .container {
            margin: 100px;
            width: 628px;
            background: 787878;
            border-radius: 6px;
            margin-top: 116px;
            margin-left: 400px;
            padding: 82px 65px 30px 40px;
            box-shadow: 0 40px 20px rgba(0,0,0,0.28);
        }

        .container .content {
            display: flex;
            align-items: center;
        }

        .container .content .right-side {
            width: 75%;
            margin-left: 75px;
        }

        .content .right-side .topic-text {
            font-size: 23px;
            font-weight: 600;
            color: #60adde;
        }

        .right-side .input-box {
            height: 50px;
            width: 100%;
            margin: 20px 0;
        }

        .right-side .input-box input,
        .right-side .input-box textarea {
            height: 100%;
            width: 100%;
            border: none;
            outline: none;
            font-size: 16px;
            background: transparent;
            color: white;
            border-bottom: 1px dotted #fff;
            border-radius: 6px;
            padding: 0 15px;
        }

        .right-side .message-box {
            min-height: 110px;
        }

        .right-side .input-box textarea {
            padding-top: 6px;
        }

        .right-side .button {
            display: inline-block;
            margin-top: 12px;
        }

        .btn {
            width: 100%;
            box-sizing: border-box;
            padding: 5px 18px;
            margin-top: 30px;
            outline: none;
            border: none;
            background: #60adde;
            opacity: 0.7;
            border-radius: 20px;
            font-size: 20px;
            color: #fff;
        }

        .btn:hover {
            background: #003366;
            opacity: 0.7;
        }

        .forgot-password {
            margin-left: 300px;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #60adde;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #003366;
            text-decoration: underline;
        }

        .or-divider {
            text-align: center;
            margin: 20px 0;
            color: #fff;
        }

        #googleSignInButton {
            width: 100%;
            text-align: center;
            margin: 15px 0;
        }

        .register {
            text-align: center;
            margin-top: 20px;
        }

        .error {
            color: green;
            font-family: cursive;
            font-size: 12px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <div class="right-side">
                <div class="topic-text">LOGIN</div><br>
                <form id="form" method="post">
                    <div class="input-box">
                        <input type="text" placeholder="Enter email id" name="email" id="p1" required/>
                        <p id="error1"><b style='font-family:cursive; font-size:12px; color:green;'> &nbsp;&nbsp;Invalid email id</p>
                    </div>
                    <div class="input-box">
                        <input type="password" placeholder="Enter Password" name="password" id="p2" required/>
                        <p id="error2"><b style='font-family:cursive; font-size:12px; color:green;'> &nbsp;&nbsp;Invalid Password</p>
                    </div>
                    <div class="button">
                        <input type="submit" class="btn" name="submit" value="Login"/>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>
                </form>
                <div class="or-divider">OR</div>
                <div id="googleSignInButton"></div>
                <div id="google-signin-result"></div>
                <div class="register">
                    <a href="register.php"><p style="color:#fff"><b>I am new here</b></p></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Your existing JavaScript validation code
        $(document).ready(function () {
            $("#error1, #error2").hide();
            
            var emailPattern = /^\w+([\.-]?\w+)*(@gmail|@yahoo)+([\.-]?\w+)*(\.\w{2,3})+$/;
            var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
            
            $("#p1").keyup(function() {
                validateField($(this), emailPattern, "#error1");
            });
            
            $("#p2").keyup(function() {
                validateField($(this), passwordPattern, "#error2");
            });
            
            function validateField(element, pattern, errorElement) {
                if (!pattern.test(element.val())) {
                    $(errorElement).show();
                    return false;
                } else {
                    $(errorElement).hide();
                    return true;
                }
            }
        });

        // Google Sign-In handling
        function handleCredentialResponse(response) {
            console.log("Encoded JWT ID token: " + response.credential);
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'google_signin.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        switch(response.u_type) {
                            case '0':
                                window.location.href = 'adminindex.php';
                                break;
                            case '1':
                                window.location.href = 'userindex.php';
                                break;
                            case '2':
                                window.location.href = 'supplierindex.php';
                                break;
                            case '5':
                                window.location.href = 'vaccination_center_index.php';
                                break;
                            default:
                                alert('Unknown user type: ' + response.u_type);
                        }
                    } else {
                        document.getElementById('google-signin-result').innerHTML = response.message;
                    }
                }
            };
            xhr.send('credential=' + response.credential);
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: "151430511839-rm5ljn03n9qpf98nsh9od7q1h0vc319l.apps.googleusercontent.com",
                callback: handleCredentialResponse
            });
            google.accounts.id.renderButton(
                document.getElementById("googleSignInButton"),
                { theme: "outline", size: "large" }
            );
        }
    </script>
</body>
</html>