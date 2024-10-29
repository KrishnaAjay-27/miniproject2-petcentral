<?php
    ob_start();
    include('connection.php');
    include('useraccount.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>View Profile</title>
        <link rel="icon" href="bblogo.png" />
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ajaxy/1.6.1/scripts/jquery.ajaxy.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: rgb(185, 162, 143);
                font-family: Arial, sans-serif;
                color: #333;
            }
            .profile-container {
    background: #ffffff;
    width: 80%; /* Relative width */
    max-width: 1200px; /* Maximum width for larger screens */
    margin: 10px auto; /* Reduced top margin to move container up */
    padding: 40px 60px;
    border-radius: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}


            .profile-container h2 {
                text-align: center;
                color: #6d605e;
                margin-bottom: 20px;
            }
            .profile-container label {
    font-size: 18px; /* Slightly larger font size for better readability */
    color: #5b4f46; /* Darker color for better contrast */
    margin-bottom: 8px; /* Increased margin for better spacing */
    display: block;
    font-weight: bold; /* Bold text for emphasis */
}

.profile-container input[type="text"],
.profile-container input[type="email"],
.profile-container select {
    width: 100%;
    padding: 12px 15px; /* Increased padding for a more comfortable feel */
    margin-bottom: 20px; /* Increased margin for more space between elements */
    border: 1px solid #ddd; /* Lighter border for a subtle look */
    border-radius: 8px; /* Rounded corners for a modern look */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2); /* Slight shadow for depth */
    font-size: 16px; /* Consistent font size */
    transition: all 0.3s ease; /* Smooth transition for interactions */
}

.profile-container input[type="text"]:disabled {
    background-color: #f9f9f9; /* Lighter background for disabled inputs */
    color: #aaa; /* Lighter text color for disabled state */
}

.profile-container input[type="text"]:focus,
.profile-container input[type="email"]:focus,
.profile-container select:focus {
    border-color: #6d605e; /* Darker border color on focus */
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.3); /* Slightly more pronounced shadow on focus */
    outline: none; /* Remove default outline */
}

            .button-group {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
            }
            .button-group button {
                width: 48%;
                padding: 10px;
                background-color: #6d605e;
                border: none;
                color: #fff;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
            }
            .button-group button:hover {
                background-color: #5b534c;
            }
            .error {
                color: red;
                font-size: 14px;
            }
        </style>
        <script>
            function validateForm() {
                var pincode = document.getElementById("pincode").value;
                if (pincode.charAt(0) != '6') {
                    document.getElementById("pincodeError").style.display = 'block';
                    return false;
                } else {
                    document.getElementById("pincodeError").style.display = 'none';
                    return true;
                }
            }
        </script>
    </head>
    <body>
        <div class="profile-container">
            <h2>Update Profile</h2>
            <?php
                $userid = $_SESSION['uid'];
                $query = "SELECT * FROM registration WHERE lid='$userid'";
                $result = mysqli_query($con, $query);
                $row = mysqli_fetch_array($result);

                if ($result) {
                    $query = "SELECT * FROM login WHERE lid = $userid";
                    $result1 = mysqli_query($con, $query);
                    if ($result1) {
                        while ($row1 = mysqli_fetch_array($result1)) {
            ?>
            <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo $row['name'];?>" required>

                <label>Email (cannot be edited)</label>
                <input type="email" name="email" value="<?php echo $row['email'];?>" disabled>

                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo $row['phone'];?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo $row['address'];?>" required>

                <label>Landmark</label>
                <input type="text" name="landmark" value="<?php echo $row['landmark'];?>" required>

                <label>Road Name</label>
                <input type="text" name="roadname" value="<?php echo $row['roadname'];?>" required>

                <label>District</label>
<select name="district" required>
    <option value="" disabled>Select District</option>
    <option value="Thiruvananthapuram" <?php if ($row['district'] == "Thiruvananthapuram") echo "selected"; ?>>Thiruvananthapuram</option>
    <option value="Kollam" <?php if ($row['district'] == "Kollam") echo "selected"; ?>>Kollam</option>
    <option value="Pathanamthitta" <?php if ($row['district'] == "Pathanamthitta") echo "selected"; ?>>Pathanamthitta</option>
    <option value="Alappuzha" <?php if ($row['district'] == "Alappuzha") echo "selected"; ?>>Alappuzha</option>
    <option value="Kottayam" <?php if ($row['district'] == "Kottayam") echo "selected"; ?>>Kottayam</option>
    <option value="Idukki" <?php if ($row['district'] == "Idukki") echo "selected"; ?>>Idukki</option>
    <option value="Ernakulam" <?php if ($row['district'] == "Ernakulam") echo "selected"; ?>>Ernakulam</option>
    <option value="Thrissur" <?php if ($row['district'] == "Thrissur") echo "selected"; ?>>Thrissur</option>
    <option value="Palakkad" <?php if ($row['district'] == "Palakkad") echo "selected"; ?>>Palakkad</option>
    <option value="Malappuram" <?php if ($row['district'] == "Malappuram") echo "selected"; ?>>Malappuram</option>
    <option value="Kozhikode" <?php if ($row['district'] == "Kozhikode") echo "selected"; ?>>Kozhikode</option>
    <option value="Wayanad" <?php if ($row['district'] == "Wayanad") echo "selected"; ?>>Wayanad</option>
    <option value="Kannur" <?php if ($row['district'] == "Kannur") echo "selected"; ?>>Kannur</option>
    <option value="Kasaragod" <?php if ($row['district'] == "Kasaragod") echo "selected"; ?>>Kasaragod</option>
</select>

                <label>State</label>
                <input type="text" name="state" value="Kerala" readonly>

                <label>Pincode</label>
                <input type="text" id="pincode" name="pincode" value="<?php echo $row['pincode'];?>" required>
                <div id="pincodeError" class="error" style="display:none;">Pincode must start with 6.</div>

                <div class="button-group">
                    <button type="submit" name="sub">Update</button>
                    <button type="button" onclick="location.href='home.php'">Back</button>
                </div>
            </form>
            <?php
                        }
                    }
                }
            ?>
        </div>
        
        <?php
            if(isset($_POST["sub"]))
            {
                $name = $_POST["name"];
                $phone = $_POST["phone"];
                $address = $_POST["address"];
                $landmark = $_POST["landmark"];
                $roadname = $_POST["roadname"];
                $district = $_POST["district"];
                $pincode = $_POST["pincode"];

                $query = "UPDATE registration SET name='$name', phone='$phone', address='$address', landmark='$landmark', roadname='$roadname', district='$district', pincode='$pincode' WHERE lid='$userid'";
                $result = mysqli_query($con, $query);
                
                if($result) {
                    echo "<script>alert('Profile Updated Successfully');</script>";
                } else {
                    echo "<script>alert('Failed to Update Profile');</script>";
                }
            }
            mysqli_close($con);
        ?>
    </body>
</html>
