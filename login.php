<?php
session_start();
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Establish database connection
    $con = mysqli_connect("localhost", "root", "", "project");

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute the query
    $query = "SELECT * FROM login WHERE email='$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Check if the user is active
        if ($user['status'] == 0) {
            // Check if the password matches
            if ($password === $user['password']) {
                $_SESSION['uid'] = $user['lid'];
                $_SESSION['u_type'] = $user['u_type'];

                // Redirect based on user type
                switch ($user['u_type']) {
                    case 0:
                        header('Location: adminindex.php');
                        break;
                    case 1:
                        // Fetch user details to check for incomplete profile
                        $profileQuery = "SELECT * FROM registration WHERE lid=" . $_SESSION['uid'];
                        $profileResult = mysqli_query($con, $profileQuery);
                        $profile = mysqli_fetch_assoc($profileResult);
                        if (empty($profile['landmark']) || empty($profile['pincode']) || empty($profile['roadname']) || empty($profile['district']) || empty($profile['state'])) {
                            // If any field is empty, redirect to the profile completion page
                            header('Location: complete_profile.php');
                        } else {
                            // If all fields are complete, redirect to the user index page
                            header('Location: userindex.php');
                        }
                        exit();
                    case 2:
                      $lid = $_SESSION['uid'];
                      $result = mysqli_query($con, "SELECT * FROM s_registration WHERE lid='$lid'");
                      $user = mysqli_fetch_assoc($result);
                      
                      // Check if any of the fields are empty
                      
                      

                      // Check if any required fields are empty
                      if (empty($user['phone']) || empty($user['address']) || empty($user['state']) || empty($user['district'])) {
                        header('Location: suppliercomplete_profile.php');
                        exit();
                    
                      } else {
                          // Redirect to the supplier index page if all fields are complete
                          header('Location: supplierindex.php');
                      }
                      exit();
              }
                
            } else {
                echo "<script>alert('Email and password do not match.');</script>";
            }
        } else {
            echo "<script>alert('Your account is inactive. Please contact support.');</script>";
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

    <title> login user</title>	
  <style>
  
  *{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins" , sans-serif;
}
body{
    background-image: linear-gradient(rgba(0,0,0,0.75),rgba(0,0,0,0.75)),url(res.jpg);
      background-size: 1650px 900px;
      align-items: center;
      background-repeat: no-repeat;
}
.container{
    margin: 100px;
      width: 628px;
      background: 787878;
      border-radius: 6px;
      margin-top: 116px;
      margin-left: 400px;
      padding: 82px 65px 30px 40px;
      box-shadow: 0 040px 20px rgba(0,0,0,0.26);
}
.container .content{
  display: flex;
  align-items: center;

}
.container .content .right-side{
  width: 75%;
  margin-left: 75px;
}
.content .right-side .topic-text{
  font-size: 23px;
  font-weight: 600;
  color:#60adde ;
  }
.right-side .input-box{
  height: 50px;
  width: 100%;
  margin: 20px 0;
}
.right-side .input-box input,
.right-side .input-box textarea{
  height: 100%;
  width: 100%;
  border: none;
  outline: none;
  font-size: 16px;
  background: transparent;
  color:white;
 border-bottom:1px dotted #fff;
  border-radius: 6px;
  padding: 0 15px;
}
.right-side .message-box{
  min-height: 110px;
}
.right-side .input-box textarea{
  padding-top: 6px;
}
.right-side .button{
  display: inline-block;
  margin-top: 12px;
}

 .btn {
    width: 100%;
    box-sizing:border-box;
    padding:5px 18px;
    margin-top:30px;
    outline:none;
    border:none;
    background:#60adde;
    opacity:0.7;
    border-radius:20px;
    font-size: 20px;
    color:#fff;
		}
        .btn:hover{
    background:#003366;
    opacity:0.7;
}
.forgot-password{
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




  
 </style>
  

   
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
	var v1=0;
	var v2=0;
	var v3=0;
        $(document).ready(function () {
            $("#error1").hide();
            $("#error2").hide();
            $("#error3").hide();
            var user =   /^\w+([\.-]?\w+)*(@gmail|@yahoo)+([\.-]?\w+)*(\.\w{2,3})+$/;
            $("#p1").keyup(function () {
                x = document.getElementById("p1").value;
                if (name.test(x) == false) {
                     v1 = 1
                    $("#error1").show();
                }
                else if (name.test(x) == true) {
                   v1 = 0;
                    $("#error1").hide();
                }
            });
			        x  = document.getElementById("p2").value;
					y  = document.getElementById("p3").value;
					
			   psw1= /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
               $("#p2").keyup(function () {
                x1 = document.getElementById("p2").value;
                if (psw1.test(x1) == false) {
                     v2 = 1
                    $("#error2").show();
                }
                else if (psw1.test(x1) == true) {
                   v2 = 0;
                    $("#error2").hide();
                }
            });
            $(".btn").click(function () {
                if (v1==0 && v2==0 && v3==0)
                    $("#error3").hide();
                else
				{
                   alert('Please Fill The Form Correctly');
					return false;
					}
            });
        });
    </script>
   </head>
<body>

  <div class="container">
    <div class="content">
      <div class="right-side">
        <div class="topic-text">LOGIN</div><br>
        <form id="form" method="post">
          <div class="input-box">
            <input type="text" placeholder="Enter email id" name="email" id="p1" required/>
            <p id="error1"><b style='font-family:cursive; font-size:12px; color:green;'> &nbsp;&nbsp;Invalid email id</p><br>
          </div>
          <div class="input-box">
            <input type="password" placeholder="Enter Password" name="password" id="p2" required/>
            <p id="error2"><b style='font-family:cursive; font-size:12px; color:green;'> &nbsp;&nbsp;Invalid Password</p><br>
          </div>
          <div class="button">
            <input type="submit" class="btn" name="submit" value="Login"/>
          </div>
          <div class="forgot-password">
            <a href="forgot_password.php" style="color:#60adde;">Forgot Password?</a>
          </div>
        </form>
        <div class="or-divider">
          <span style="color:#fff;">OR</span>
        </div>
        <div id="googleSignInButton">Sign in with Google</div>
        <div id="google-signin-result"></div>
        <div class="register">
            &nbsp;&nbsp;<a href="register.php"><p style="color:#fff;width:100%;"><b>I am new here<b></p></a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function handleCredentialResponse(response) {
        console.log("Encoded JWT ID token: " + response.credential);
        
        // Send the ID token to your server
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'google_signin.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            console.log("Server response status: " + xhr.status);
            console.log("Server response text: " + xhr.responseText);
            
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log(response.message);
                    console.log("User type:", response.u_type); // Log the user type
                    // Redirect based on user type
                    switch(response.u_type) {
                        case 0:
                        case '0':
                            window.location.href = 'adminindex.php';
                            break;
                        case 1:
                        case '1':
                            window.location.href = 'userindex.php';
                            break;
                        case 2:
                        case '2':
                            window.location.href = 'supplierindex.php';
                            break;
                        default:
                            alert('Unknown user type: ' + response.u_type);
                            document.getElementById('google-signin-result').innerHTML = 'Error: Unknown user type (' + response.u_type + ')';
                    }
                } else {
                    document.getElementById('google-signin-result').innerHTML = response.message;
                }
            } else {
                console.error('Google Sign-In failed: ' + xhr.responseText);
                document.getElementById('google-signin-result').innerHTML = 'Google Sign-In failed. Please try again or use regular login.';
            }
        };
        xhr.onerror = function() {
            console.error('XHR error:', xhr.status, xhr.statusText);
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