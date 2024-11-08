<?php
session_start();
require('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
} else {
    $reg = $_SESSION['uid'];

    // Establish database connection
    $con = mysqli_connect("localhost", "root", "", "project");
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch user details
    $q = "SELECT * FROM registration WHERE lid='$reg';";
    $re = mysqli_query($con, $q);
    if ($row = mysqli_fetch_array($re)) {
        if (!empty($row['landmark']) && !empty($row['pincode']) && !empty($row['roadname']) && !empty($row['district']) && $row['state'] == 'Kerala') {
            // If all fields are complete and state is Kerala, redirect to index page
            header('Location: userindex.php');
            exit();
        }
    } else {
        die("Error fetching user data.");
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $landmark = htmlspecialchars($_POST['landmark']);
        $pincode = htmlspecialchars($_POST['pincode']);
        $roadname = htmlspecialchars($_POST['roadname']);
        $district = htmlspecialchars($_POST['district']);
        $state = 'Kerala'; // Set state as Kerala

        // Update user details in the registration table
        $updateQuery = "UPDATE registration SET landmark='$landmark', pincode='$pincode', roadname='$roadname', district='$district', state='$state' WHERE lid='$reg'";
        if (mysqli_query($con, $updateQuery)) {
            // Redirect to user index page after updating
            header('Location: userindex.php');
            exit();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }

    mysqli_close($con);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .profile-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .profile-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .profile-container label {
            display: block;
            margin-bottom: 10px;
            color: #555;
        }
        .profile-container input,
        .profile-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .profile-container input:focus,
        .profile-container select:focus {
            border-color: #007bff;
            outline: none;
        }
        .profile-container input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .profile-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        .profile-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: none;
        }
    </style>
</head>
<script>
function validateLandmark() {
    var landmark = document.getElementById("landmark").value;
    var landmarkError = document.getElementById("landmarkError");
    var landmarkRegex = /^[A-Za-z\s]+$/;
    
    if (!landmarkRegex.test(landmark)) {
        landmarkError.style.display = "block";
        return false;
    } else {
        landmarkError.style.display = "none";
        return true;
    }
}

function validateRoadname() {
    var roadname = document.getElementById("roadname").value;
    var roadnameError = document.getElementById("roadnameError");
    var roadnameRegex = /^[A-Za-z0-9\s]+$/;
    
    if (!roadnameRegex.test(roadname)) {
        roadnameError.style.display = "block";
        return false;
    } else {
        roadnameError.style.display = "none";
        return true;
    }
}

function validatePincode() {
    var pincode = document.getElementById("pincode").value;
    var pincodeError = document.getElementById("pincodeError");
    var pincodeRegex = /^6\d{5}$/;
    
    if (!pincodeRegex.test(pincode)) {
        pincodeError.style.display = "block";
        return false;
    } else {
        pincodeError.style.display = "none";
        return true;
    }
}

function validateForm() {
    var isLandmarkValid = validateLandmark();
    var isRoadnameValid = validateRoadname();
    var isPincodeValid = validatePincode();

    return isLandmarkValid && isRoadnameValid && isPincodeValid;
}
</script>
<body>

    <div class="profile-container">
        <h2>Complete Your Profile</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" onsubmit="return validateForm()">
            <label for="landmark">Landmark</label>
            <input type="text" id="landmark" name="landmark" required onkeyup="validateLandmark()">
            <div id="landmarkError" class="error-message">Landmark should only contain letters and spaces.</div>

            <label for="pincode">Pincode</label>
            <input type="text" id="pincode" name="pincode" required onkeyup="validatePincode()">
            <div id="pincodeError" class="error-message">Pincode should start with 6 and be exactly 6 digits long.</div>

            <label for="roadname">Road Name</label>
            <input type="text" id="roadname" name="roadname" required onkeyup="validateRoadname()">
            <div id="roadnameError" class="error-message">Road name should not contain special characters.</div>

            <label for="district">District</label>
            <select id="district" name="district" required>
                <option value="">Select District</option>
                <option value="Thiruvananthapuram">Thiruvananthapuram</option>
                <option value="Kollam">Kollam</option>
                <option value="Pathanamthitta">Pathanamthitta</option>
                <option value="Alappuzha">Alappuzha</option>
                <option value="Kottayam">Kottayam</option>
                <option value="Idukki">Idukki</option>
                <option value="Ernakulam">Ernakulam</option>
                <option value="Thrissur">Thrissur</option>
                <option value="Palakkad">Palakkad</option>
                <option value="Malappuram">Malappuram</option>
                <option value="Kozhikode">Kozhikode</option>
                <option value="Wayanad">Wayanad</option>
                <option value="Kannur">Kannur</option>
                <option value="Kasaragod">Kasaragod</option>
            </select>

            <label for="state">State</label>
            <input type="text" id="state" name="state" value="Kerala" readonly>

            <button type="submit">Save and Continue</button>
        </form>
    </div>
</body>
</html>