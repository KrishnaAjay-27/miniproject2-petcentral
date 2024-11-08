<?php
require('connection.php');
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['verify'])) {
    $code = mysqli_real_escape_string($con, $_POST['verification_code']);
    
    // Check if the code exists and is not already verified
    $query = "SELECT * FROM registration WHERE verification_code = '$code' AND verified = 0";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update the user's status to verified
        $update_query = "UPDATE registration SET verified = 1 WHERE verification_code = '$code'";
        if (mysqli_query($con, $update_query)) {
            echo "<script>alert('Your email has been verified successfully!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Failed to verify email. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid or already used verification code.');</script>";
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
    <style>
        /* Add your styling here */
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="verify_code.php">
            <h2>Verify Your Email</h2>
            <label for="verification_code">Enter Verification Code:</label>
            <input type="text" name="verification_code" id="verification_code" required>
            <input type="submit" name="verify" value="Verify">
        </form>
    </div>
</body>
</html>
