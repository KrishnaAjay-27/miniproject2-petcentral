<?php
session_start();
require('connection.php');

// Check if delivery boy is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];

// Fetch the delivery boy's details
$query = "SELECT d.name, d.assign_date, d.email, d.phone, d.doctor_code 
         FROM deliveryboy d 
         WHERE d.lid='$deid'";
$result = mysqli_query($con, $query);
$delivery_boy = mysqli_fetch_assoc($result);
$delivery_boy_name = $delivery_boy ? $delivery_boy['name'] : 'Delivery Boy';

// Fetch profile details if exists
$profile_query = "SELECT * FROM deliveryboy_profile WHERE lid='$deid'";
$profile_result = mysqli_query($con, $profile_query);
$profile = mysqli_fetch_assoc($profile_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $license_number = mysqli_real_escape_string($con, $_POST['license_number']);
        
        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $profile_photo = uploadFile($_FILES['profile_photo'], 'profile_photos/');
        }
        
        // Handle license photo upload
        if (isset($_FILES['license_photo']) && $_FILES['license_photo']['error'] == 0) {
            $license_photo = uploadFile($_FILES['license_photo'], 'license_photos/');
        }
        
        // Check if profile exists and update or insert accordingly
        if ($profile) {
            $update_query = "UPDATE deliveryboy_profile SET 
                license_number = '$license_number'";
            
            if (isset($profile_photo)) {
                $update_query .= ", profile_photo = '$profile_photo'";
            }
            if (isset($license_photo)) {
                $update_query .= ", license_photo = '$license_photo'";
            }
            
            $update_query .= " WHERE lid = '$deid'";
            mysqli_query($con, $update_query);
        } else {
            $insert_query = "INSERT INTO deliveryboy_profile (lid, license_number, profile_photo, license_photo, joining_date) 
                VALUES ('$deid', '$license_number', 
                '" . (isset($profile_photo) ? $profile_photo : '') . "', 
                '" . (isset($license_photo) ? $license_photo : '') . "', 
                CURRENT_DATE())";
            mysqli_query($con, $insert_query);
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $old_password = mysqli_real_escape_string($con, $_POST['old_password']);
        $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
        $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);
        
        // Password validation
        if (strlen($new_password) < 8 || 
            !preg_match('/[0-9]/', $new_password) || 
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password) || 
            !preg_match('/[a-zA-Z]/', $new_password)) {
            echo "<script>alert('Password must be at least 8 characters and include numbers, letters, and special characters');</script>";
        } elseif ($new_password !== $confirm_password) {
            echo "<script>alert('Passwords do not match');</script>";
        } else {
            // Verify old password and update
            $password_query = "UPDATE login SET password = '$new_password' 
                WHERE lid = '$deid' AND password = '$old_password'";
            if (mysqli_query($con, $password_query)) {
                echo "<script>alert('Password updated successfully');</script>";
            } else {
                echo "<script>alert('Old password is incorrect');</script>";
            }
        }
    }
    
    // Refresh profile data after update
    $profile_result = mysqli_query($con, $profile_query);
    $profile = mysqli_fetch_assoc($profile_result);
}

// File upload helper function
function uploadFile($file, $directory) {
    $target_dir = $directory;
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    }
    return null;
}

// Check if profile is complete
$is_profile_complete = false;
if($profile && 
   !empty($profile['license_photo']) && 
   !empty($profile['profile_photo']) && 
   !empty($profile['license_number']) && 
   !empty($profile['joining_date'])) {
    $is_profile_complete = true;
}

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']);
$change_password_mode = isset($_GET['change_password']);

// Get unread message count
$unread_query = "SELECT COUNT(*) as unread_count 
                 FROM chatmessage 
                 WHERE lid = ? 
                 AND sender_id = 16 
                 AND is_read = 0";
$stmt = mysqli_prepare($con, $unread_query);
mysqli_stmt_bind_param($stmt, 'i', $deid);
mysqli_stmt_execute($stmt);
$unread_result = mysqli_stmt_get_result($stmt);
$unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Copy the existing styles from deliveryindex.php and add these: */
        .profile-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        
        .license-photo {
            max-width: 300px;
            margin-bottom: 20px;
        }

        /* Add existing styles first, then add: */
        .sidebar {
            background: #003366;
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-links {
            padding: 0;
            margin: 0;
            list-style: none;
            flex-grow: 1;
        }

        .nav-links a {
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-link-content {
            display: flex;
            align-items: center;
            gap: 15px; /* Space between icon and text */
        }

        .nav-link-content i {
            width: 20px;
            font-size: 18px;
            text-align: center;
        }

        .nav-link-content span {
            font-size: 16px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
            margin-left: 10px;
        }

        .logout-btn {
            margin: 20px;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }

        /* Ensure content is pushed to the right */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 80%;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }

        .logout-btn {
            margin-top: auto;
            width: 80%;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }

        /* Add this for the sidebar bottom spacing */
        .sidebar {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .nav-links {
            flex-grow: 1;
        }

        .profile-view {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-actions {
            margin-bottom: 20px;
        }

        .incomplete-profile-alert {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .info-value {
            color: #212529;
            padding: 8px 0;
        }

        /* Add this to your existing CSS */
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Add to your existing CSS */
        .text-danger {
            color: #dc3545;
        }

        .form-group label {
            font-weight: 500;
        }

        .profile-photo,
        .license-photo {
            max-width: 200px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px;
        }
    </style>
</head>
<body>
    <!-- Add this HTML for the sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Welcome</h2>
            <p><?php echo htmlspecialchars($delivery_boy_name); ?></p>
        </div>
        
        <div class="nav-links">
            <a href="deliveryindex.php">
                <div class="nav-link-content">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
            </a>
            <a href="delivery_profile.php" class="active">
                <div class="nav-link-content">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </div>
            </a>
            <a href="viewdeliveryassignment.php">
                <div class="nav-link-content">
                    <i class="fas fa-truck"></i>
                    <span>View Assignments</span>
                </div>
            </a>
            <a href="delivery_chat.php">
                <div class="nav-link-content">
                    <i class="fas fa-comments"></i>
                    <span>Chat with Admin</span>
                </div>
                <?php if ($unread_count > 0): ?>
                    <span class="badge badge-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="notificationdelivery.php">
                <div class="nav-link-content">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </div>
            </a>
        </div>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="content">
        <div class="content-header">
            <h1>My Profile</h1>
            <p class="text-muted">View and manage your profile information</p>
        </div>

        <?php if(!$is_profile_complete && !$edit_mode): ?>
        <div class="incomplete-profile-alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Please complete your profile by adding your license number, license photo, and profile photo.
            <a href="?edit=true" class="alert-link">Complete your profile</a> to access all features.
        </div>
        <?php endif; ?>

        <?php if(!$edit_mode && !$change_password_mode): ?>
        <!-- View Mode -->
        <div class="row">
            <div class="col-md-8">
                <div class="profile-view">
                    <div class="profile-header">
                        <img src="<?php echo !empty($profile['profile_photo']) ? htmlspecialchars($profile['profile_photo']) : 'assets/images/avatar.jpg'; ?>" 
                             alt="Profile Photo" class="profile-avatar">
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($delivery_boy_name); ?></h3>
                            <p class="text-muted mb-0">Delivery Partner</p>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="?edit=true" class="btn btn-primary mr-2">
                            <i class="fas fa-edit mr-1"></i> Edit Profile
                        </a>
                        <a href="?change_password=true" class="btn btn-warning">
                            <i class="fas fa-key mr-1"></i> Change Password
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($delivery_boy['phone'] ?? 'Not set'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($delivery_boy['email'] ?? 'Not set'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">Doctor Code</div>
                                <div class="info-value"><?php echo htmlspecialchars($delivery_boy['doctor_code'] ?? 'Not set'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">License Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['license_number'] ?? 'Not set'); ?></div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['address'] ?? 'Not set'); ?></div>
                    </div>

                    <?php if(!empty($profile['license_photo'])): ?>
                    <div class="info-group">
                        <div class="info-label">License Photo</div>
                        <img src="<?php echo htmlspecialchars($profile['license_photo']); ?>" 
                             alt="License Photo" class="img-fluid mt-2" style="max-width: 300px;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php elseif($edit_mode): ?>
        <!-- Edit Mode -->
        <div class="row">
            <div class="col-md-8">
                <div class="profile-section">
                    <h4 class="mb-4">Edit Profile Information</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($delivery_boy_name); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Profile Photo <span class="text-danger">*</span></label>
                            <?php if (!empty($profile['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($profile['profile_photo']); ?>" class="profile-photo d-block">
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="profile_photo" accept="image/*" <?php echo empty($profile['profile_photo']) ? 'required' : ''; ?>>
                            <small class="form-text text-muted">Upload your profile photo</small>
                        </div>
                        
                        <div class="form-group">
                            <label>License Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="license_number" id="licenseNumber" 
                                   value="<?php echo htmlspecialchars($profile['license_number'] ?? ''); ?>" 
                                   pattern="KL\d{2}\s?\d{10}" 
                                   placeholder="Format: KL03 1234567890"
                                   required>
                            <div class="invalid-feedback" id="licenseFeedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label>License Photo <span class="text-danger">*</span></label>
                            <?php if (!empty($profile['license_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($profile['license_photo']); ?>" class="license-photo d-block">
                            <?php endif; ?>
                            <input type="file" class="form-control-file" name="license_photo" accept="image/*" <?php echo empty($profile['license_photo']) ? 'required' : ''; ?>>
                            <small class="form-text text-muted">Upload your license photo</small>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        <a href="delivery_profile.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>

        <?php elseif($change_password_mode): ?>
        <!-- Change Password Mode -->
        <div class="row">
            <div class="col-md-6">
                <div class="profile-section">
                    <h4 class="mb-4">Change Password</h4>
                    <form method="POST" id="passwordForm">
                        <div class="form-group">
                            <label>Old Password</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" class="form-control" name="new_password" id="newPassword" required>
                            <div class="invalid-feedback" id="passwordFeedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                            <div class="invalid-feedback" id="confirmPasswordFeedback"></div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a profile photo
        const profileAvatar = document.querySelector('.profile-avatar');
        profileAvatar.onerror = function() {
            this.src = 'assets/images/avatar.jpg';
        };

        // Password validation
        const passwordForm = document.getElementById('passwordForm');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const passwordFeedback = document.getElementById('passwordFeedback');

        function validatePassword(password) {
            const minLength = 8;
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            const hasLetter = /[a-zA-Z]/.test(password);

            let errors = [];
            if (password.length < minLength) {
                errors.push('Password must be at least 8 characters long');
            }
            if (!hasNumber) {
                errors.push('Include at least one number');
            }
            if (!hasSpecial) {
                errors.push('Include at least one special character');
            }
            if (!hasLetter) {
                errors.push('Include at least one letter');
            }

            return errors;
        }

        newPassword.addEventListener('input', function() {
            const errors = validatePassword(this.value);
            if (errors.length > 0) {
                this.classList.add('is-invalid');
                passwordFeedback.innerHTML = errors.join('<br>');
            } else {
                this.classList.remove('is-invalid');
                passwordFeedback.innerHTML = '';
            }
        });

        // License number validation
        const licenseInput = document.getElementById('licenseNumber');
        const licenseFeedback = document.getElementById('licenseFeedback');

        licenseInput.addEventListener('input', function() {
            const licensePattern = /^KL\d{2}\s?\d{10}$/;
            if (!licensePattern.test(this.value)) {
                this.classList.add('is-invalid');
                licenseFeedback.textContent = 'License number should be in format KL03 followed by 10 digits';
            } else {
                this.classList.remove('is-invalid');
                licenseFeedback.textContent = '';
            }
        });

        // Form submission validation
        passwordForm.addEventListener('submit', function(e) {
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                document.getElementById('confirmPasswordFeedback').textContent = 'Passwords do not match';
            }
        });
    });
    </script>
</body>
</html>

<?php mysqli_close($con); ?>
