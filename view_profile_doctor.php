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
$query = "SELECT d.*, l.email, d.image1,d.doctor_code
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
} else {
    echo "Error: " . mysqli_error($con);
    $doctor_name = 'Doctor';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
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

        /* Profile Tabs */
        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .profile-tab {
            padding: 1rem 2rem;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .profile-tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }

        /* Tab Content */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Tab Content */
        .profile-container {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-left {
            flex: 1;
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .profile-right {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .profile-image-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 1.5rem;
            border: 5px solid #f0f0f0;
            box-shadow: var(--shadow);
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-id-details {
            margin-bottom: 1.5rem;
        }

        .profile-doctor-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .profile-doctor-id {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .profile-doctor-specialty {
            font-size: 1rem;
            color: var(--text-secondary);
        }

        .profile-circle-overlay {
            display: none;
        }

        .profile-info-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .profile-info-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .profile-info-item {
            margin-bottom: 1.5rem;
        }

        .profile-info-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .profile-info-value {
            font-size: 1.1rem;
            color: var(--text);
        }

        .profile-download-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
            text-align: center;
        }

        .profile-download-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .profile-download-btn i {
            margin-left: 0.5rem;
        }

        /* Certificates Tab Content */
        .certificates-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .certificate-card {
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .certificate-preview {
            height: 200px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .certificate-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .certificate-card:hover .certificate-preview img {
            transform: scale(1.05);
        }

        .certificate-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
        }

        .certificate-card:hover .certificate-overlay {
            opacity: 1;
        }

        .certificate-overlay i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .certificate-details {
            padding: 1.5rem;
        }

        .certificate-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .certificate-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Settings Tab Content */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .settings-card {
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .settings-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .settings-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }

        .settings-items {
            padding: 1rem 0;
        }

        .settings-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .settings-item:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        .settings-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
        }

        .settings-info {
            flex: 1;
        }

        .settings-info h3 {
            margin: 0 0 0.25rem;
            font-size: 1.1rem;
            color: var(--text);
        }

        .settings-info p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .settings-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--surface);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid var(--border);
        }

        .settings-button:hover {
            background: var(--primary);
            color: white;
            transform: translateX(3px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            z-index: 1000;
        }

        .modal-content {
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            max-width: 700px;
            width: 90%;
            margin: 10vh auto;
            position: relative;
            box-shadow: var(--shadow-md);
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--background);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.25rem;
        }

        .close:hover {
            background: var(--text-secondary);
            color: var(--surface);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-image-container {
                min-height: 300px;
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
            
            .settings-container {
                grid-template-columns: 1fr;
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
        <div class="profile-tabs">
            <div class="profile-tab active" data-tab="profile">Profile</div>
            <div class="profile-tab" data-tab="certificates">Certificates</div>
            <div class="profile-tab" data-tab="settings">Settings</div>
        </div>
        
        <!-- Profile Tab Content -->
        <div class="tab-content active" id="profile-tab-content">
            <div class="profile-container">
                <div class="profile-left">
                    <div class="profile-image-container">
                        <?php if (!empty($doctor['image1']) && file_exists("uploads/" . $doctor['image1'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($doctor['image1']); ?>" alt="Doctor Profile" class="profile-image">
                        <?php else: ?>
                            <img src="assets/default-doctor.png" alt="Default Profile" class="profile-image">
                        <?php endif; ?>
                    </div>
                    <div class="profile-id-details">
                        <div class="profile-doctor-name">Dr. <?php echo htmlspecialchars($doctor['name']); ?></div>
                        <div class="profile-doctor-id">Doctor ID: <?php echo htmlspecialchars($doctor['doctor_code']); ?></div>
                        <div class="profile-doctor-specialty">Specialty: <?php echo htmlspecialchars($doctor['Qualification']); ?></div>
                    </div>
                </div>
                
                <div class="profile-right">
                    <div class="profile-info-card">
                        <h2 class="profile-info-title">About Me</h2>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Email</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['email']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Role</div>
                            <div class="profile-info-value">Dr. <?php echo htmlspecialchars($doctor['name']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Qualification</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['Qualification']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Phone</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['phone']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Experience</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['experience']); ?> Years</div>
                        </div>
                        
                      
                    </div>
                    
                    <div class="profile-info-card">
                        <h2 class="profile-info-title">Location</h2>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">Address</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['address']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">District</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['district']); ?></div>
                        </div>
                        
                        <div class="profile-info-item">
                            <div class="profile-info-label">State</div>
                            <div class="profile-info-value"><?php echo htmlspecialchars($doctor['state']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Certificates Tab Content -->
        <div class="tab-content" id="certificates-tab-content">
            <div class="certificates-container">
                <div class="certificate-card">
                    <div class="certificate-preview" onclick="openModal()">
                        <?php if (!empty($doctor['certificateimg2']) && file_exists("uploads/" . $doctor['certificateimg2'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($doctor['certificateimg2']); ?>" alt="Certificate">
                            <div class="certificate-overlay">
                                <i class="fas fa-search-plus"></i>
                                <span>View Certificate</span>
                            </div>
                        <?php else: ?>
                            <div class="no-certificate">
                                <i class="fas fa-file-upload"></i>
                                <p>No certificate uploaded yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="certificate-details">
                        <div class="certificate-title">Professional Certificate</div>
                        <div class="certificate-info">
                            <p>Qualification: <?php echo htmlspecialchars($doctor['Qualification']); ?></p>
                            <p>Experience: <?php echo htmlspecialchars($doctor['experience']); ?> Years</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Settings Tab Content -->
        <div class="tab-content" id="settings-tab-content">
            <div class="settings-container">
                <div class="settings-card">
                    <div class="settings-header">
                        <h3 class="settings-title">Account Settings</h3>
                    </div>
                    <div class="settings-items">
                        <div class="settings-item">
                            <div class="settings-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="settings-info">
                                <h3>Edit Profile</h3>
                                <p>Update your personal information</p>
                            </div>
                            <a href="edit_profile_doctor.php" class="settings-button">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        <div class="settings-item">
                            <div class="settings-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="settings-info">
                                <h3>Password & Security</h3>
                                <p>Manage your password and security settings</p>
                            </div>
                            <a href="update_password_doctor.php" class="settings-button">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        
                    </div>
                </div>
                
               
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <?php if (!empty($doctor['certificateimg2']) && file_exists("uploads/" . $doctor['certificateimg2'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($doctor['certificateimg2']); ?>" 
                     alt="Certificate" style="width: 100%; height: auto; border-radius: 10px;">
            <?php else: ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-file-upload" style="font-size: 3rem; color: var(--text-secondary);"></i>
                    <p style="margin-top: 1rem;">No certificate uploaded yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-tab-content').classList.add('active');
            });
        });

        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById("myModal")) {
                closeModal();
            }
        }
    </script>
</body>
</html>