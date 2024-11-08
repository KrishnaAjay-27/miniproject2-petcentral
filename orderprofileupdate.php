<?php
    ob_start();
    include('connection.php');
include('header.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>View Profile</title>
        <link rel="icon" href="bblogo.png" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                function showError(inputElement, errorMessage) {
                    let errorElement = inputElement.next('.error');
                    if (errorElement.length === 0) {
                        errorElement = $('<div class="error"></div>').insertAfter(inputElement);
                    }
                    errorElement.text(errorMessage).show();
                }

                function hideError(inputElement) {
                    inputElement.next('.error').hide();
                }

                function validateName(input) {
                    const name = input.val();
                    if (!/^[a-zA-Z\s]*$/.test(name) || name.trim()[0] === ' ') {
                        showError(input, "Name should only contain letters and spaces, and cannot start with a space.");
                        return false;
                    }
                    hideError(input);
                    return true;
                }

                function validatePhone(input) {
                    const phone = input.val();
                    if (!/^[6-9]\d{9}$/.test(phone) || /^(\d)\1{9}$/.test(phone)) {
                        showError(input, "Phone number should start with 6-9, be exactly 10 digits, and not be all the same digit.");
                        return false;
                    }
                    hideError(input);
                    return true;
                }

                function validatePincode(input) {
                    const pincode = input.val();
                    if (!/^6\d{5}$/.test(pincode)) {
                        showError(input, "Pincode must start with 6 and be exactly 6 digits long.");
                        return false;
                    }
                    hideError(input);
                    return true;
                }

                function validateLandmarkRoadname(input) {
                    const value = input.val();
                    if (!/^[a-zA-Z0-9\s]*$/.test(value)) {
                        showError(input, "Only letters, numbers, and spaces are allowed.");
                        return false;
                    }
                    hideError(input);
                    return true;
                }

                $('input[name="name"]').on('input', function() {
                    validateName($(this));
                });

                $('input[name="phone"]').on('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 10) {
                        this.value = this.value.slice(0, 10);
                    }
                    validatePhone($(this));
                });

                $('input[name="pincode"]').on('input', function() {
                    validatePincode($(this));
                });

                $('input[name="landmark"], input[name="roadname"]').on('input', function() {
                    validateLandmarkRoadname($(this));
                });

                $('form').submit(function(e) {
                    let isValid = true;
                    isValid = validateName($('input[name="name"]')) && isValid;
                    isValid = validatePhone($('input[name="phone"]')) && isValid;
                    isValid = validatePincode($('input[name="pincode"]')) && isValid;
                    isValid = validateLandmarkRoadname($('input[name="landmark"]')) && isValid;
                    isValid = validateLandmarkRoadname($('input[name="roadname"]')) && isValid;

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });
        </script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: rgb(185, 162, 143);
                font-family: Arial, sans-serif;
            }
            .profile-container {
                background: #fff;
                width: 80%;
                max-width: 1200px;
                margin: 10px auto;
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
                font-size: 18px;
                margin-bottom: 8px;
                display: block;
                font-weight: bold;
            }
            .profile-container input, select {
                width: 100%;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 8px;
                border: 1px solid #ddd;
            }
            .button-group {
                display: flex;
                justify-content: space-between;
            }
            .button-group button {
                width: 48%;
                padding: 10px;
                background-color: #6d605e;
                color: white;
                border: none;
                cursor: pointer;
            }
            .button-group button:hover {
                background-color: #5b534c;
            }
            .error {
                color: red;
                display: none;
            }
        </style>
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
            ?>
            <form method="post">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo $row['name']; ?>" required>

                <label>Email (cannot be edited)</label>
                <input type="email" value="<?php echo $row['email']; ?>" readonly>

                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo $row['phone']; ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo $row['address']; ?>" required>

                <label>Landmark</label>
                <input type="text" name="landmark" value="<?php echo $row['landmark']; ?>" required>

                <label>Road Name</label>
                <input type="text" name="roadname" value="<?php echo $row['roadname']; ?>" required>

                <label>District</label>
                <select name="district" required>
                    <option value="Thiruvananthapuram" <?= ($row['district'] == 'Thiruvananthapuram') ? 'selected' : ''; ?>>Thiruvananthapuram</option>
                    <option value="Kollam" <?= ($row['district'] == 'Kollam') ? 'selected' : ''; ?>>Kollam</option>
                    <!-- Add other districts as needed -->
                </select>

                <label>State</label>
                <input type="text" value="Kerala" readonly>

                <label>Pincode</label>
                <input type="text" name="pincode" value="<?php echo $row['pincode']; ?>" required>

                <div class="button-group">
                    <button type="submit" name="sub">Update</button>
                    <button type="button" onclick="location.href='order_confirmation.php'">Back</button>
                </div>
            </form>
            <?php
                }

                if (isset($_POST['sub'])) {
                    $name = $_POST['name'];
                    $phone = $_POST['phone'];
                    $address = $_POST['address'];
                    $landmark = $_POST['landmark'];
                    $roadname = $_POST['roadname'];
                    $district = $_POST['district'];
                    $pincode = $_POST['pincode'];

                    $updateQuery = "UPDATE registration SET name='$name', phone='$phone', address='$address', 
                                    landmark='$landmark', roadname='$roadname', district='$district', 
                                    pincode='$pincode' WHERE lid='$userid'";

                    if (mysqli_query($con, $updateQuery)) {
                        echo "<script>alert('Profile updated successfully');</script>";
                        header("Location: order_confirmation.php");
                    } else {
                        echo "<script>alert('Error updating profile');</script>";
                    }
                }
            ?>
        </div>
    </body>
</html>
