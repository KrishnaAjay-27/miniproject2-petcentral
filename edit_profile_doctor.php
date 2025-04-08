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

// Fetch doctor's details including image
$uid = $_SESSION['uid'];
$query = "SELECT d.*, l.email, d.image1 
          FROM d_registration d 
          JOIN login l ON d.lid = l.lid 
          WHERE d.lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $doctor = mysqli_fetch_assoc($result);
    $doctor_name = $doctor['name'] ?? 'Doctor';
    $doctor_image = $doctor['image1'] ?? '';
    $doctor_email = $doctor['email'] ?? '';
} else {
    echo "Error: " . mysqli_error($con);
    $doctor_name = 'Doctor';
}

// Handle form submission
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];

    $errors = [];
    if (empty($name) || !preg_match('/^[A-Za-z\s]+$/', $name) || preg_match('/^\s/', $name)) {
        $errors[] = "Name is required and cannot start with a space or contain special characters or numbers.";
    }
    if (empty($phone) || !preg_match('/^[6789]\d{9}$/', $phone) || preg_match('/(\d)\1{9}/', $phone)) {
        $errors[] = "Phone number is required, must start with 6, 7, 8, or 9, and cannot have the same digit repeated 10 times.";
    }
    if (empty($address) || preg_match('/^\s/', $address)) {
        $errors[] = "Address is required and cannot start with a space.";
    }
    if (empty($state) || !preg_match('/^[A-Za-z]+$/', $state) || preg_match('/^\s/', $state)) {
        $errors[] = "State is required, must contain only alphabets, and cannot start with a space.";
    }
    if (empty($qualification) || preg_match('/^\s/', $qualification)) {
        $errors[] = "Qualification is required and cannot start with a space.";
    }
    if (empty($experience) || $experience < 0) {
        $errors[] = "Experience is required and cannot be negative.";
    }

    if (empty($errors)) {
        $name = mysqli_real_escape_string($con, $name);
        $phone = mysqli_real_escape_string($con, $phone);
        $address = mysqli_real_escape_string($con, $address);
        $state = mysqli_real_escape_string($con, $state);
        $district = mysqli_real_escape_string($con, $district);
        $qualification = mysqli_real_escape_string($con, $qualification);
        $experience = mysqli_real_escape_string($con, $experience);

        $updateQuery = "UPDATE d_registration 
                        SET name='$name', phone='$phone', address='$address', state='$state', district='$district', 
                            Qualification='$qualification', experience='$experience' 
                        WHERE lid='$uid'";

        if (mysqli_query($con, $updateQuery)) {
            echo "<script>alert('Profile updated successfully.'); window.location.href = 'view_profile_doctor.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor Profile</title>
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
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        /* Form Styles */
        .edit-form-container {
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text);
            transition: border-color 0.2s ease;
            background: var(--background);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .error-message {
            color: var(--accent);
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .form-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background: var(--background);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
        }

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
            
            <a href="view_profile_doctor.php" class="nav-item active">
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
        <h1 class="page-title">Edit Profile</h1>
        
        <div class="edit-form-container">
            <form method="post" onsubmit="return validateForm();">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                        <span id="nameError" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                        <span id="phoneError" class="error-message"></span>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($doctor['address']); ?>" required>
                        <span id="addressError" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($doctor['state']); ?>" required>
                        <span id="stateError" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="district" class="form-label">District</label>
                        <select id="district" name="district" class="form-control" required>
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
                    
                    <div class="form-group">
                        <label for="qualification" class="form-label">Qualification</label>
                        <input type="text" id="qualification" name="qualification" class="form-control" value="<?php echo htmlspecialchars($doctor['Qualification']); ?>" required>
                        <span id="qualificationError" class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience" class="form-label">Experience (Years)</label>
                        <input type="number" id="experience" name="experience" class="form-control" value="<?php echo htmlspecialchars($doctor['experience']); ?>" required>
                        <span id="experienceError" class="error-message"></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="view_profile_doctor.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="update" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateName() {
            const nameInput = document.getElementById('name');
            const nameValue = nameInput.value.trim();
            const errorMessage = document.getElementById('nameError');

            errorMessage.textContent = '';
            if (!nameValue) {
                errorMessage.textContent = 'Name is required.';
                return false;
            }
            if (!/^[A-Za-z\s]+$/.test(nameValue)) {
                errorMessage.textContent = 'Name cannot contain special characters or numbers.';
                return false;
            }
            if (/^\s/.test(nameValue)) {
                errorMessage.textContent = 'Name cannot start with a space.';
                return false;
            }
            return true;
        }

        function validatePhone() {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value.trim();
            const phoneError = document.getElementById('phoneError');

            phoneError.textContent = '';
            if (!phoneValue) {
                phoneError.textContent = 'Phone number is required.';
                return false;
            }
            if (!/^[6789]\d{9}$/.test(phoneValue)) {
                phoneError.textContent = 'Phone number must start with 6, 7, 8, or 9 and be 10 digits long.';
                return false;
            }
            if (/(\d)\1{9}/.test(phoneValue)) {
                phoneError.textContent = 'Phone number cannot contain the same digit repeated 10 times.';
                return false;
            }
            return true;
        }

        function validateAddress() {
            const addressInput = document.getElementById('address');
            const addressError = document.getElementById('addressError');
            addressError.textContent = '';
            if (/^\s/.test(addressInput.value)) {
                addressError.textContent = 'Address cannot start with a space.';
                return false;
            }
            return true;
        }

        function validateState() {
            const stateInput = document.getElementById('state');
            const stateError = document.getElementById('stateError');
            stateError.textContent = '';
            if (!/^[A-Za-z]+$/.test(stateInput.value.trim()) || /^\s/.test(stateInput.value)) {
                stateError.textContent = 'State must contain only alphabets and cannot start with a space.';
                return false;
            }
            return true;
        }

        function validateQualification() {
            const qualificationInput = document.getElementById('qualification');
            const qualificationError = document.getElementById('qualificationError');
            qualificationError.textContent = '';
            if (/^\s/.test(qualificationInput.value)) {
                qualificationError.textContent = 'Qualification cannot start with a space.';
                return false;
            }
            return true;
        }

        function validateExperience() {
            const experienceInput = document.getElementById('experience');
            const experienceError = document.getElementById('experienceError');
            experienceError.textContent = '';
            if (experienceInput.value < 0) {
                experienceError.textContent = 'Experience cannot be negative.';
                return false;
            }
            return true;
        }

        function validateForm() {
            return (
                validateName() &&
                validatePhone() &&
                validateAddress() &&
                validateState() &&
                validateQualification() &&
                validateExperience()
            );
        }

        // Add event listeners for real-time validation
        document.getElementById('name').addEventListener('blur', validateName);
        document.getElementById('phone').addEventListener('blur', validatePhone);
        document.getElementById('address').addEventListener('blur', validateAddress);
        document.getElementById('state').addEventListener('blur', validateState);
        document.getElementById('qualification').addEventListener('blur', validateQualification);
        document.getElementById('experience').addEventListener('blur', validateExperience);
    </script>
</body>
</html>