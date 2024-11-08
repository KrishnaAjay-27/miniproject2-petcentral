<?php

include('doctorindex.php');
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch doctor's profile
$lid = $_SESSION['uid']; // Assuming the logged-in user's ID is stored in session
$query = "SELECT * FROM d_registration WHERE lid='$lid'";
$result = mysqli_query($con, $query);
$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    echo "<script>alert('Doctor not found.'); window.location.href = 'doctor_dashboard.php';</script>";
    exit();
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctor Profile</title>
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
            width: 350px; /* Reduced width for a smaller card */
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .profile-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
            text-align: left; /* Align text to the left */
        }
        .profile-card label {
            font-weight: bold;
            color: #555;
            display: block; /* Make labels block elements */
            margin-bottom: 5px; /* Add space below labels */
        }
        .profile-card p {
            margin: 5px 0;
            color: #333;
        }
        .button {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            background: #60adde;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background: #003366;
        }
        .profile-image {
            width: 80px; /* Smaller profile image */
            height: auto;
            border-radius: 50%; /* Circular profile image */
            margin-bottom: 10px;
        }
        .certificate-icon {
            width: 30px; /* Smaller certificate icon */
            height: auto;
            cursor: pointer;
        }
        .image-container {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Space between image and text */
        }
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Doctor Profile</h2>
        <div class="profile-card">
            <img src="uploads/<?php echo htmlspecialchars($doctor['image1']); ?>" alt="Profile Image" class="profile-image">
            <label>Name:</label>
            <p><?php echo htmlspecialchars($doctor['name']); ?></p>
            <label>Email:</label>
            <p><?php echo htmlspecialchars($doctor['email']); ?></p>
            <label>Phone Number:</label>
            <p><?php echo htmlspecialchars($doctor['phone']); ?></p>
            <label>Address:</label>
            <p><?php echo htmlspecialchars($doctor['address']); ?></p>
            <label>State:</label>
            <p><?php echo htmlspecialchars($doctor['state']); ?></p>
            <label>District:</label>
            <p><?php echo htmlspecialchars($doctor['district']); ?></p>
            <label>Qualification:</label>
            <p><?php echo htmlspecialchars($doctor['Qualification']); ?></p>
            <label>Experience:</label>
            <p><?php echo htmlspecialchars($doctor['experience']); ?> years</p>
            <label>Certificate Image:</label>
            <div class="image-container">
                <img src="uploads/<?php echo htmlspecialchars($doctor['certificateimg2']); ?>" alt="Certificate Image" class="certificate-icon" onclick="openModal()">
                <span style="cursor: pointer;" onclick="openModal()">View Certificate</span>
            </div>
        </div>
        <div class="button">
            <a href="edit_profile_doctor.php" class="btn">Edit Profile</a>
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <img src="uploads/<?php echo htmlspecialchars($doctor['certificateimg2']); ?>" alt="Certificate Image" style="width: 100%; height: auto;">
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Function to open the modal
        function openModal() {
            modal.style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>