<?php
include('doctorindex.php');
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch doctor's profile
$lid = $_SESSION['uid']; // Assuming the logged-in user's ID is stored in session
$query = "SELECT * FROM d_registration WHERE lid='$lid'";
$result = mysqli_query($con, $query);
$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    echo "<script>alert('Doctor not found.'); window.location.href = 'doctorindex.php';</script>";
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    // Collect form data
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];

    // Validate form data
    $errors = [];
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($district)) $errors[] = "District is required.";
    if (empty($qualification)) $errors[] = "Qualification is required.";
    if (empty($experience)) $errors[] = "Experience is required.";

    // Handle file uploads
    $image1 = $_FILES['image1']['name'];
    $certificateimg2 = $_FILES['certificateimg2']['name'];
    $target_dir = "uploads/"; // Ensure this directory exists and is writable

    // Validate file uploads
    if ($_FILES['image1']['error'] == UPLOAD_ERR_NO_FILE) {
        $image1 = $doctor['image1']; // Keep the existing image if no new file is uploaded
    } else {
        move_uploaded_file($_FILES['image1']['tmp_name'], $target_dir . $image1);
    }

    if ($_FILES['certificateimg2']['error'] == UPLOAD_ERR_NO_FILE) {
        $certificateimg2 = $doctor['certificateimg2']; // Keep the existing certificate if no new file is uploaded
    } else {
        move_uploaded_file($_FILES['certificateimg2']['tmp_name'], $target_dir . $certificateimg2);
    }

    if (empty($errors)) {
        // Sanitize input
        $phone = mysqli_real_escape_string($con, $phone);
        $address = mysqli_real_escape_string($con, $address);
        $state = mysqli_real_escape_string($con, $state);
        $district = mysqli_real_escape_string($con, $district);
        $qualification = mysqli_real_escape_string($con, $qualification);
        $experience = mysqli_real_escape_string($con, $experience);

        // Prepare the update query
        $updateQuery = "UPDATE d_registration 
                        SET phone='$phone', address='$address', state='$state', district='$district', 
                            Qualification='$qualification', experience='$experience', 
                            image1='$image1', certificateimg2='$certificateimg2' 
                        WHERE lid='$lid'";

        // Execute the update query
        if (mysqli_query($con, $updateQuery)) {
            echo "<script>alert('Profile updated successfully.'); window.location.href = 'doctor_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Doctor Profile</title>
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
            width: 500px;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .input-box {
            margin-bottom: 15px;
        }
        .input-box label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .input-box input, .input-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .button {
            margin-top: 20px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            background: #60adde;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Your Profile</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="input-box">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
            </div>
            <div class="input-box">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($doctor['address']); ?>" required>
            </div>
            <div class="input-box">
                <label for="state">State:</label>
                <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($doctor['state']); ?>" required>
            </div>
            <div class="input-box">
                <label for="district">District:</label>
                <select name="district" id="district" required>
                    <option value="">Select District</option>
                    <option value="Alappuzha" <?php echo ($doctor['district'] == 'Alappuzha') ? 'selected' : ''; ?>>Alappuzha</option>
                    <option value="Ernakulam" <?php echo ($doctor['district'] == 'Ernakulam') ? 'selected' : ''; ?>>Ernakulam</option>
                    <option value="Idukki" <?php echo ($doctor['district'] == 'Idukki') ? 'selected' : ''; ?>>Idukki</option>
                    <option value="Kannur" <?php echo ($doctor['district'] == 'Kannur') ? 'selected' : ''; ?>>Kannur</option>
                    <option value="Kasaragod" <?php echo ($doctor['district'] == 'Kasaragod') ? 'selected' : ''; ?>>Kasaragod</option>
                    <option value="Kollam" <?php echo ($doctor['district'] == 'Kollam') ? 'selected' : ''; ?>>Kollam</option>
                    <option value="Kottayam" <?php echo ($doctor['district'] == 'Kottayam') ? 'selected' : ''; ?>>Kottayam</option>
                    <option value="Kozhikode" <?php echo ($doctor['district'] == 'Kozhikode') ? 'selected' : ''; ?>>Kozhikode</option>
                    <option value="Malappuram" <?php echo ($doctor['district'] == 'Malappuram') ? 'selected' : ''; ?>>Malappuram</option>
                    <option value="Palakkad" <?php echo ($doctor['district'] == 'Palakkad') ? 'selected' : ''; ?>>Palakkad</option>
                    <option value="Pathanamthitta" <?php echo ($doctor['district'] == 'Pathanamthitta') ? 'selected' : ''; ?>>Pathanamthitta</option>
                    <option value="Thrissur" <?php echo ($doctor['district'] == 'Thrissur') ? 'selected' : ''; ?>>Thrissur</option>
                    <option value="Wayanad" <?php echo ($doctor['district'] == 'Wayanad') ? 'selected' : ''; ?>>Wayanad</option>
                </select>
            </div>
            <div class="input-box">
                <label for="qualification">Qualification:</label>
                <input type="text" name="qualification" id="qualification" value="<?php echo htmlspecialchars($doctor['Qualification']); ?>" required>
            </div>
            <div class="input-box">
                <label for="experience">Experience:</label>
                <input type="number" name="experience" id="experience" value="<?php echo htmlspecialchars($doctor['experience']); ?>" required>
            </div>
            <div class="input-box">
                <label for="image1">Profile Image:</label>
                <input type="file" name="image1" id="image1" accept="image/*">
                <small>Leave blank to keep the current image.</small>
            </div>
            <div class="input-box">
                <label for="certificateimg2">Certificate Image:</label>
                <input type="file" name="certificateimg2" id="certificateimg2" accept="image/*">
                <small>Leave blank to keep the current certificate.</small>
            </div>
            <div class="button">
                <input type="submit" class="btn" name="update" value="Update Profile">
            </div>
        </form>
    </div>
</body>
</html>