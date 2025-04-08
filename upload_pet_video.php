<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch doctor's details
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
    $doctor_name = 'Doctor';
}

// Create upload directories
$directories = ['uploads/videos', 'uploads/thumbnails'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Pet Video</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --text-light: #ecf0f1;
            --hover-color: #2980b9;
            --light-bg: #f5f6fa;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--light-bg);
        }

        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
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
            border: 3px solid var(--accent-color);
            overflow: hidden;
            background: #fff;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }


        .doctor-name {
            color: var(--text-light);
            font-size: 24px;
            margin: 10px 0 5px;
            font-weight: 500;
        }

        .doctor-email {
            color: var(--accent-color);
            font-size: 14px;
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
            background: var(--accent-color);
            color: white;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }

        .upload-form {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .preview-container {
            margin: 15px 0;
            padding: 15px;
            border: 1px dashed var(--border-color);
            border-radius: var(--border-radius);
            background: var(--light-bg);
        }

        .video-preview {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .thumbnail-preview {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .doctor-info {
                padding: 15px 10px;
            }
            
            .doctor-avatar {
                width: 50px;
                height: 50px;
            }
            
            .doctor-name, .doctor-email {
                display: none;
            }
            
            .nav-item {
                padding: 15px;
                margin: 5px;
                justify-content: center;
            }
            
            .nav-item i {
                margin-right: 0;
            }
            
            .nav-item span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
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
                <span>Dashboard</span>
            </a>
            
            <a href="view_profile_doctor.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>View Profile</span>
            </a>

            <a href="view_chat_message.php" class="nav-item">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
            </a>
            
            <a href="upload_pet_video.php" class="nav-item active">
                <i class="fas fa-video"></i>
                <span>Video Classes</span>
            </a>
        </div>

        <div class="nav-links" style="margin-top: auto;">
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="upload-form">
            <h2 class="mb-4">Upload Pet Video</h2>
            
            <form id="videoUploadForm" method="POST" enctype="multipart/form-data" action="process_pet_video.php">
                <div class="mb-3">
                    <label for="title" class="form-label">Video Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Health">Pet Health</option>
                        <option value="Training">Pet Training</option>
                        <option value="Grooming">Pet Grooming</option>
                        <option value="Behavior">Pet Behavior</option>
                        <option value="Nutrition">Pet Nutrition</option>
                        <option value="Emergency">Emergency Care</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="video" class="form-label">Video File</label>
                    <input type="file" class="form-control" id="video" name="video" accept="video/*" required>
                    <div id="videoPreview" class="preview-container"></div>
                </div>

                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Thumbnail Image (Optional)</label>
                    <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                    <div id="thumbnailPreview" class="preview-container"></div>
                </div>

                <div class="mb-3">
                    <label for="content_file" class="form-label">Content File (TXT/SRT)</label>
                    <input type="file" class="form-control" id="content_file" name="content_file" accept=".txt,.srt">
                    <small class="text-muted">Upload video transcript or content file (TXT or SRT format)</small>
                    <div id="contentPreview" class="preview-container">
                        <p class="selected-file-name"></p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Upload Video</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Video preview
            $('#video').change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    const videoURL = URL.createObjectURL(file);
                    $('#videoPreview').html(`
                        <video controls class="video-preview">
                            <source src="${videoURL}" type="${file.type}">
                            Your browser does not support the video tag.
                        </video>
                    `);
                }
            });

            // Thumbnail preview
            $('#thumbnail').change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    const imageURL = URL.createObjectURL(file);
                    $('#thumbnailPreview').html(`
                        <img src="${imageURL}" class="thumbnail-preview" alt="Thumbnail preview">
                    `);
                }
            });

            // Content file preview
            $('#content_file').change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    $('#contentPreview .selected-file-name').text('Selected file: ' + file.name);
                }
            });

            // Form submission
            $('#videoUploadForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                // Show loading state
                Swal.fire({
                    title: 'Uploading...',
                    text: 'Please wait while we upload your files',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'process_pet_video.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Response:', response);
                        try {
                            const result = JSON.parse(response);
                            if (result.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.error,
                                });
                            } else if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Video and content uploaded successfully!',
                                }).then(() => {
                                    window.location.href = 'view_pet_videos.php';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Warning',
                                    text: 'Unknown response from server',
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error processing server response',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error uploading files: ' + error
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
