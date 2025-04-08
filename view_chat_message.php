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

// Fetch chat messages
$doctor_id = $_SESSION['uid'];
$query = "SELECT cm.*, r.name AS user_name, r.district FROM chat_message cm 
          JOIN registration r ON cm.lid = r.lid 
          WHERE cm.did = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --text-light: #ecf0f1;
            --hover-color: #2980b9;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --light-bg: #f5f6fa;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
        }

        /* Sidebar Styles - matching doctorindex.php */
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
            font-size: 16px;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
        }

        .nav-item:hover, .nav-item.active {
            background: #3498db;
            color: white;
        }

        /* WhatsApp-like Chat Styles */
        .container {
            margin-left: 280px;
            padding: 30px;
            background-color: #e5ded8;
            min-height: 100vh;
        }

        .chat-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin-bottom: 20px;
        }

        .message {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .message-content {
            background: #dcf8c6;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
        }

        .reply-form {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            font-family: inherit;
            resize: vertical;
        }

        .reply-form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            font-family: inherit;
        }

        .reply-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s;
        }

        .reply-btn:hover {
            background: var(--hover-color);
        }

        .error {
            color: var(--danger-color);
            font-size: 14px;
            margin: 5px 0;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--accent-color);
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
            
            .container {
                margin-left: 80px;
            }
        }
    </style>
    <!-- Keep your existing script -->
</head>
<body>
    <!-- Add Sidebar -->
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

            <a href="view_chat_message.php" class="nav-item active">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
            </a>
            
            <a href="upload_pet_video.php" class="nav-item">
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


        
<div class="container">
    <div class="chat-header">
        <h2>Chat Messages</h2>
    </div>
    
    <?php if (empty($messages)): ?>
        <div class="message">
            <p>No messages found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <div class="message-header">
                    <h3><?php echo htmlspecialchars($message['user_name']); ?></h3>
                    <small><?php echo htmlspecialchars($message['created_at']); ?></small>
                </div>
                
                <div class="message-content">
                    <p><strong>District:</strong> <?php echo htmlspecialchars($message['district']); ?></p>
                    <p><strong>Breed Name:</strong> <?php echo htmlspecialchars($message['breed_name']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($message['age']); ?></p>
                    <p><strong>Vaccination Status:</strong> <?php echo htmlspecialchars($message['vaccination_status']); ?></p>
                    <p><strong>Problem:</strong> <?php echo htmlspecialchars($message['problem']); ?></p>
                </div>

                <?php if (isset($message['reply']) && !empty($message['reply'])): ?>
                    <div class="message-content" style="background: #e3f2fd;">
                        <p><strong>Doctor's Reply:</strong> <?php echo htmlspecialchars($message['reply']); ?></p>
                    </div>
                <?php else: ?>
                    <p class="pending-status">Status: Pending Reply</p>
                    <form class="reply-form" method="POST" action="reply_to_chat.php" onsubmit="return validateForm(event)">
                        <input type="hidden" name="chat_id" value="<?php echo $message['chatid']; ?>">
                        <textarea name="reply" rows="3" placeholder="Type your reply here..." required></textarea>
                        <div id="replyError" class="error"></div>
                        <input type="text" name="medicine" placeholder="Medicine prescribed (if any)" required>
                        <div id="medicineError" class="error"></div>
                        <button type="submit" class="reply-btn">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>

 