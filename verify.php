<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "project");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the verification code is set in the URL
if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Look for the user with the corresponding verification code
    $query = "SELECT * FROM registration WHERE verification_code = '$verification_code' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // If user is found, update the verification status
        $row = mysqli_fetch_assoc($result);
        $update_query = "UPDATE registration SET verified = 1 WHERE verification_code = '$verification_code'";

        if (mysqli_query($con, $update_query)) {
            echo "<script>alert('Your email has been verified. You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Failed to verify email. Please try again.'); window.location.href='register.php';</script>";
        }
    } else {
        // If the verification code is invalid
        echo "<script>alert('Invalid verification code.'); window.location.href='register.php';</script>";
    }
} else {
    echo "<script>alert('No verification code provided.'); window.location.href='register.php';</script>";
}

mysqli_close($con);
?>
