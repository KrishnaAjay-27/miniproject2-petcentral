
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php'; // Adjust the path if needed

function sendVerificationEmail($email, $name, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
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
        $mail->Body = 'Dear ' . $name . ',<br><br>Thank you for registering with us. Please visit the following page to verify your email address:<br><br>
    <a href="http://localhost/Minproject%20Pet%20central/verify_code.php">Verify Email</a><br><br>Or, use the following verification code:<br><br>
    <b>' . $verification_code . '</b><br><br>Best regards,<br>Your PetCentral';

        $mail->send();
        echo 'Verification email has been sent';
    } catch (Exception $e) {
        echo "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>

<head>
  <title>registration form user</title>

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
      box-shadow:0 040px 20px rgba(0,0,0,0.28);
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
      color:#60adde;

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
      background:transparent;
      color:white;
      border-bottom:1px dotted #fff;
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


  </style>
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    var f1 = 0;
    var f2 = 0;
    var f3 = 0;
    var f4 = 0;
    var f5 = 0;
    var f6 = 0;
	
	
    $(document).ready(function() {
      $("#error1").hide();
      $("#error3").hide();
      $("#error4").hide();
      $("#error5").hide();
      $("#error6").hide();
	  $("#error7").hide();

	var name = /^[A-Za-z][A-Za-z-\s]+$/;
      $("#p1").keyup(function() {
        x = document.getElementById("p1").value;
        if (name.test(x) == false) {
          f1 = 1
          $("#error1").show();
        } else if (name.test(x) == true) {
          f1= 0;
          $("#error1").hide();
        }
      });
	  
	      var mail = /^\w+([\.-]?\w+)*(@gmail|@yahoo)+([\.-]?\w+)*(\.\w{2,3})+$/;
      $("#p3").keyup(function() {
        x = document.getElementById("p3").value;
        if (mail.test(x) == false) {
          f2 = 1
          $("#error3").show();
        } else if (mail.test(x) == true) {
          f2 = 0
          $("#error3").hide();
        }
      });
	  
	    var add = /^(?![0-9]+$)[a-zA-Z0-9\s\,\#\-]+$/;
      $("#p5").keyup(function() {
        x = document.getElementById("p5").value;
        if (add.test(x) == false) {
          f4 = 1
          $("#error5").show();
        } else if (add.test(x) == true) {
          f4 = 0;
          $("#error5").hide();
        }
      });
	  
	  	var phone=/^[7-9][0-9]{9}$/;
	  $("#p4").keyup(function(){
		  x=document.getElementById("p4").value;
		  if(phone.test(x)==false)
		  { f5=1
	        $("#error4").show();
		  }
		  else if(phone.test(x)==true)
		  {
			  f5=0
			  $("#error4").hide();
		  }
	  });	
      
	  
	  
	  
	  x = document.getElementById("p6").value;
      y = document.getElementById("p7").value;

      psw1 = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
      $("#p6").keyup(function() {
        x1 = document.getElementById("p6").value;
        if (psw1.test(x1) == false) {
          f6 = 1
          $("#error6").show();
        } else if (psw1.test(x1) == true) {
          f6= 0;
          $("#error6").hide();
        }
      });
	  
	  psw2 = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
      $("#p7").keyup(function() {
        y1 = document.getElementById("p7").value;
        if (document.getElementById("p6").value != document.getElementById("p7").value) 
		{
          f7 = 1
          $("#error7").show();
        } 
		else if (document.getElementById("p6").value == document.getElementById("p7").value)
			{
          f7= 0;
          $("#error7").hide();
        }
      });
	  

      
      $(".btn").click(function() {
        if (f1 == 0  && f2 == 0  && f4 == 0 && f5== 0 && f6==0 && f7==0)
          return true;
        else {
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
        <div class="topic-text"><b>REGISTER</b></div><br>
        <form id="form" method="POST" action="register.php" enctype="multipart/form-data">
          <div>
            <div class="input-box">
              <input type="text" placeholder="Enter your  Name" name="name" id="p1" required />
              <p id="error1"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Enter Valid Name </p><br>
            </div>
			
            <div class="input-box">
              <input type="email" placeholder="Enter your Email id" name="email" id="p3" required />
              <p id="error3"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Enter Valid Email </p><br>
            </div>
			
			<div class="input-box">
              <input type="text" placeholder="Enter your Phone Number" name="phone" id="p4" required />
              <p id="error4"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Enter a valid 10-digit phone number starting with 6-9 (cannot be the same digit repeated)</p><br>
            </div>

             <div class="input-box">
              <input type="address" placeholder="Enter your Address" name="address" id="p5" required />
              <p id="error5"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Enter Valid Address </p><br>
            </div>
			
            <div class="input-box">
              <input type="password" placeholder="Enter your Password" name="password" id="p6" required />
              <p id="error6"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Enter Valid Password .password must have Uppercase,spec,num</p><br>
            </div>

            <div class="input-box">
              <input type="password" placeholder="Confirm Password again" name="pass2" id="p7" required />
              <p id="error7"><b style='font-family:cursive; font-size:12px; color:red;'> &nbsp;&nbsp;Password Doesn't Match</p><br>
            </div>
            <div class="button">
              <input type="submit" class="btn" name="submit" value="submit" />
            </div>
          
            <div class="register">
              &nbsp;&nbsp;<a href="login.php"><p style="color:#fff"><b>Already have an account</b></p> </a>
            </div>
          </div>
          
        </form>
      </div>
    </div>
  </div>

</body>
<?php
if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];
  $password = $_POST['password'];
 
  $verification_code = md5(uniqid(rand(), true));
  $u_type = 1; // Set default user type to 1 (assuming 1 is for regular users)

  $con = mysqli_connect("localhost", "root", "", "project");

  if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Check if email already exists
  $email_check_query = "SELECT * FROM registration WHERE email='$email' LIMIT 1";
  $result = mysqli_query($con, $email_check_query);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
      if ($user['email'] === $email) {
          echo "<script>alert('Email already exists, please use a different email.');</script>";
      }
  } else {
    $query = "INSERT INTO login (email, password, u_type) VALUES ('$email', '$password', $u_type)";
    $re = mysqli_query($con, $query);

    if ($re) {
        $lid = mysqli_insert_id($con);
        $q = "INSERT INTO registration (lid, name, email, phone, address, verification_code, u_type) VALUES ('$lid', '$name', '$email', '$phone', '$address', '$verification_code', $u_type)";
        $re = mysqli_query($con, $q);

        if ($re) {
          sendVerificationEmail($email, $name, $verification_code); // Send verification email
          echo "<script>alert('Registration successful. Please check your email to verify your account.'); window.location.href='login.php';</script>";
          exit();
        } else {
            echo "<script>alert('Registration failed');</script>";
        }
    }
  }

  mysqli_close($con);
}
?>