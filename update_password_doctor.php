<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch doctor's details
$uid = $_SESSION['uid'];
$query = "SELECT d.*, l.email, l.password, d.image1, d.doctor_code
          FROM d_registration d 
          JOIN login l ON d.lid = l.lid 
          WHERE d.lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $doctor = mysqli_fetch_assoc($result);
    $doctor_name = $doctor['name'] ?? 'Doctor';
    $doctor_image = $doctor['image1'] ?? '';
    $doctor_email = $doctor['email'] ?? '';
    $doctor_code = $doctor['doctor_code'] ?? '';
    $current_password_hash = $doctor['password'] ?? '';
} else {
    echo "Error: " . mysqli_error($con);
    $doctor_name = 'Doctor';
}

$success_message = '';
$error_message = '';

// Handle form submission
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $current_password_hash)) {
        $error_message = "Current password is incorrect.";
    } 
    // Check if new password meets requirements
    elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    }
    elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error_message = "New password must contain at least one uppercase letter.";
    }
    elseif (!preg_match('/[a-z]/', $new_password)) {
        $error_message = "New password must contain at least one lowercase letter.";
    }
    elseif (!preg_match('/[0-9]/', $new_password)) {
        $error_message = "New password must contain at least one number.";
    }
    elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error_message = "New password must contain at least one special character.";
    }
    // Check if new password and confirm password match
    elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    }
    else {
        // Hash the new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $update_query = "UPDATE login SET password='$new_password_hash' WHERE lid='$uid'";
        if (mysqli_query($con, $update_query)) {
            $success_message = "Password updated successfully. You will be logged out in 3 seconds.";
            // Set a flag to log out after showing success message
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'logout.php';
                }, 3000);
            </script>";
        } else {
            $error_message = "Error updating password: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Color scheme */
            --primary: #3498db;
            --primary-light: #5dade2;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --accent: #e74c3c;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #333333;
            --text-secondary: #666666;
            --text-light: #ecf0f1;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --success: #2ecc71;
            --error: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--secondary);
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .doctor-info {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255,255,255,0.05);
            position: relative;
        }

        .doctor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid var(--primary);
            overflow: hidden;
            background: #fff;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-avatar i {
            font-size: 40px;
            color: var(--primary);
            line-height: 100px;
        }

        .doctor-name {
            color: var(--text-light);
            font-size: 24px;
            margin: 10px 0 5px;
            font-weight: 500;
        }

        .doctor-email {
            color: var(--primary-light);
            font-size: 14px;
            margin: 0;
        }

        .nav-links {
            padding: 20px 0;
            width: 100%;
        }

        .nav-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 15px;
            border-radius: 10px;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--primary);
            color: white;
        }

        .nav-item.active {
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logout-btn {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 15px;
            border-radius: 10px;
            background: transparent;
        }

        .sidebar-logout-btn i {
            margin-right: 15px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        .sidebar-logout-btn:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        /* Content Styles */
        .content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            padding: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        /* Password Form Styles */
        .password-form-container {
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .requirement i {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .requirement.valid {
            color: var(--success);
        }

        .requirement.invalid {
            color: var(--text-secondary);
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background-color: var(--background);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--border);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: var(--error);
        }

        .error-text {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .doctor-info {
                padding: 15px 5px;
            }
            
            .doctor-avatar {
                width: 50px;
                height: 50px;
            }
            
            .doctor-name, .doctor-email {
                display: none;
            }
            
            .nav-item {
                padding: 15px 0;
                justify-content: center;
                margin: 5px;
            }
            
            .nav-item i {
                margin-right: 0;
            }
            
            .sidebar-logout-btn {
                padding: 15px 0;
                justify-content: center;
                margin: 5px;
            }
            
            .sidebar-logout-btn i {
                margin-right: 0;
            }
            
            .content {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="doctor-info">
            <div class="doctor-avatar">
                <?php if (!empty($doctor_image) && file_exists("uploads/" . $doctor_image)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($doctor_image); ?>" alt="Doctor Profile">
                <?php else: ?>
                    <i class="fas fa-user-md"></i>
                <?php endif; ?>
            </div>
            <h1 class="doctor-name"><?php echo htmlspecialchars($doctor_name); ?></h1>
            <p class="doctor-email"><?php echo htmlspecialchars($doctor_email); ?></p>
        </div>

        <div class="nav-links">
            <a href="doctorindex.php" class="nav-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            
            <a href="view_profile_doctor.php" class="nav-item">
                <i class="fas fa-user"></i>
                View Profile
            </a>

            <a href="view_chat_message.php" class="nav-item">
                <i class="fas fa-comments"></i>
                Messages
            </a>
            
            <a href="upload_pet_video.php" class="nav-item">
                <i class="fas fa-video"></i>
                Video Classes
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1 class="page-title">Password & Security</h1>
        
        <div class="password-form-container">
            <h2 class="form-title">Update Password</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="current_password_error" class="error-text"></div>
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="new_password_error" class="error-text"></div>
                    
                    <div class="password-requirements">
                        <div class="requirement" id="length_requirement">
                            <i class="fas fa-circle"></i> At least 8 characters
                        </div>
                        <div class="requirement" id="uppercase_requirement">
                            <i class="fas fa-circle"></i> At least one uppercase letter
                        </div>
                        <div class="requirement" id="lowercase_requirement">
                            <i class="fas fa-circle"></i> At least one lowercase letter
                        </div>
                        <div class="requirement" id="number_requirement">
                            <i class="fas fa-circle"></i> At least one number
                        </div>
                        <div class="requirement" id="special_requirement">
                            <i class="fas fa-circle"></i> At least one special character
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div id="confirm_password_error" class="error-text"></div>
                </div>
                
                <div class="form-buttons">
                    <a href="view_profile_doctor.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Real-time password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        newPasswordInput.addEventListener('input', validatePasswordRequirements);
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        
        function validatePasswordRequirements() {
            const password = newPasswordInput.value;
            
            // Check length
            const lengthRequirement = document.getElementById('length_requirement');
            if (password.length >= 8) {
                lengthRequirement.classList.add('valid');
                lengthRequirement.classList.remove('invalid');
                lengthRequirement.querySelector('i').classList.remove('fa-circle');
                lengthRequirement.querySelector('i').classList.add('fa-check-circle');
            } else {
                lengthRequirement.classList.remove('valid');
                lengthRequirement.classList.add('invalid');
                lengthRequirement.querySelector('i').classList.remove('fa-check-circle');
                lengthRequirement.querySelector('i').classList.add('fa-circle');
            }
            
            // Check uppercase
            const uppercaseRequirement = document.getElementById('uppercase_requirement');
            if (/[A-Z]/.test(password)) {
                uppercaseRequirement.classList.add('valid');
                uppercaseRequirement.classList.remove('invalid');
                uppercaseRequirement.querySelector('i').classList.remove('fa-circle');
                uppercaseRequirement.querySelector('i').classList.add('fa-check-circle');
            } else {
                uppercaseRequirement.classList.remove('valid');
                uppercaseRequirement.classList.add('invalid');
                uppercaseRequirement.querySelector('i').classList.remove('fa-check-circle');
                uppercaseRequirement.querySelector('i').classList.add('fa-circle');
            }
            
            // Check lowercase
            const lowercaseRequirement = document.getElementById('lowercase_requirement');
            if (/[a-z]/.test(password)) {
                lowercaseRequirement.classList.add('valid');
                lowercaseRequirement.classList.remove('invalid');
                lowercaseRequirement.querySelector('i').classList.remove('fa-circle');
                lowercaseRequirement.querySelector('i').classList.add('fa-check-circle');
            } else {
                lowercaseRequirement.classList.remove('valid');
                lowercaseRequirement.classList.add('invalid');
                lowercaseRequirement.querySelector('i').classList.remove('fa-check-circle');
                lowercaseRequirement.querySelector('i').classList.add('fa-circle');
            }
            
            // Check number
            const numberRequirement = document.getElementById('number_requirement');
            if (/[0-9]/.test(password)) {
                numberRequirement.classList.add('valid');
                numberRequirement.classList.remove('invalid');
                numberRequirement.querySelector('i').classList.remove('fa-circle');
                numberRequirement.querySelector('i').classList.add('fa-check-circle');
            } else {
                numberRequirement.classList.remove('valid');
                numberRequirement.classList.add('invalid');
                numberRequirement.querySelector('i').classList.remove('fa-check-circle');
                numberRequirement.querySelector('i').classList.add('fa-circle');
            }
            
            // Check special character
            const specialRequirement = document.getElementById('special_requirement');
            if (/[^A-Za-z0-9]/.test(password)) {
                specialRequirement.classList.add('valid');
                specialRequirement.classList.remove('invalid');
                specialRequirement.querySelector('i').classList.remove('fa-circle');
                specialRequirement.querySelector('i').classList.add('fa-check-circle');
            } else {
                specialRequirement.classList.remove('valid');
                specialRequirement.classList.add('invalid');
                specialRequirement.querySelector('i').classList.remove('fa-check-circle');
                specialRequirement.querySelector('i').classList.add('fa-circle');
            }
            
            // Also check match if confirm password has a value
            if (confirmPasswordInput.value) {
                validatePasswordMatch();
            }
        }
        
        function validatePasswordMatch() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const confirmError = document.getElementById('confirm_password_error');
            
            if (confirmPassword && password !== confirmPassword) {
                confirmError.textContent = 'Passwords do not match';
                return false;
            } else {
                confirmError.textContent = '';
                return true;
            }
        }
        
        function validateForm() {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            const currentPasswordError = document.getElementById('current_password_error');
            const newPasswordError = document.getElementById('new_password_error');
            
            let isValid = true;
            
            // Check if current password is entered
            if (!currentPassword) {
                currentPasswordError.textContent = 'Please enter your current password';
                isValid = false;
            } else {
                currentPasswordError.textContent = '';
            }
            
            // Check password requirements
            if (newPassword.length < 8) {
                newPasswordError.textContent = 'Password must be at least 8 characters long';
                isValid = false;
            } else if (!/[A-Z]/.test(newPassword)) {
                newPasswordError.textContent = 'Password must contain at least one uppercase letter';
                isValid = false;
            } else if (!/[a-z]/.test(newPassword)) {
                newPasswordError.textContent = 'Password must contain at least one lowercase letter';
                isValid = false;
            } else if (!/[0-9]/.test(newPassword)) {
                newPasswordError.textContent = 'Password must contain at least one number';
                isValid = false;
            } else if (!/[^A-Za-z0-9]/.test(newPassword)) {
                newPasswordError.textContent = 'Password must contain at least one special character';
                isValid = false;
            } else {
                newPasswordError.textContent = '';
            }
            
            // Check if passwords match
            if (!validatePasswordMatch()) {
                isValid = false;
            }
            
            return isValid;
        }
    </script>
</body>
</html>