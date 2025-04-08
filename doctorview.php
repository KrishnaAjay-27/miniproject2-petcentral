<?php
include('header.php');
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all doctors
$query = "SELECT * FROM d_registration";
$result = mysqli_query($con, $query);
$doctors = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($con);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Expert Doctors</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f9c74f;
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .page-container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            gap: 20px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-item i {
            margin-right: 12px;
            font-size: 18px;
            color: var(--primary-color);
        }

        .nav-item:hover {
            background: var(--secondary-color);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-item.active i {
            color: white;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 600;
        }

        .doctors-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            padding: 20px;
        }

        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(249, 199, 79, 0.2);
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(249, 199, 79, 0.3);
        }

        .doctor-card img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid var(--primary-color);
            padding: 3px;
            background: white;
        }

        .doctor-card h3 {
            margin: 15px 0;
            color: var(--text-color);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .view-more {
            background: var(--primary-color);
            color: var(--text-color);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .view-more:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 8% auto;
            padding: 35px;
            width: 90%;
            max-width: 550px;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.15);
            position: relative;
        }

        .close {
            position: absolute;
            right: 25px;
            top: 20px;
            color: #666;
            font-size: 28px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .close:hover {
            color: var(--primary-color);
        }

        .modal h3 {
            color: var(--text-color);
            font-size: 1.5rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
        }

        .modal p {
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: #555;
        }

        .modal strong {
            color: var(--text-color);
            display: inline-block;
            width: 120px;
        }

        #chat-button {
            width: 100%;
            margin-top: 25px;
            padding: 15px;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }

            .doctors-container {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="userdashboard.php" class="nav-item">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <!-- ... (add all your sidebar items) ... -->
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Our Expert Doctors</h2>
            <div class="doctors-container">
                <?php foreach ($doctors as $doctor): ?>
                    <div class="doctor-card">
                        <img src="uploads/<?php echo htmlspecialchars($doctor['image1']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>">
                        <h3>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <button class="view-more" onclick="openModal('<?php echo htmlspecialchars($doctor['phone']); ?>', '<?php echo htmlspecialchars($doctor['Qualification']); ?>', '<?php echo htmlspecialchars($doctor['experience']); ?>', '<?php echo htmlspecialchars($doctor['lid']); ?>')">
                            <i class="fas fa-user-md"></i> View Profile
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Doctor Profile</h3>
            <p><strong>Phone:</strong> <span id="doctor-phone"></span></p>
            <p><strong>Qualification:</strong> <span id="doctor-qualification"></span></p>
            <p><strong>Experience:</strong> <span id="doctor-experience"></span> years</p>
            <button id="chat-button" class="view-more">
                <i class="fas fa-comments"></i> Start Consultation
            </button>
        </div>
    </div>

    <script>
        var modal = document.getElementById("myModal");

        function openModal(phone, qualification, experience, doctorId) {
            document.getElementById("doctor-phone").innerText = phone;
            document.getElementById("doctor-qualification").innerText = qualification;
            document.getElementById("doctor-experience").innerText = experience;
            modal.style.display = "block";

            document.getElementById("chat-button").onclick = function() {
                window.location.href = "chat_with_doctor.php?doctor_id=" + doctorId;
            };
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>