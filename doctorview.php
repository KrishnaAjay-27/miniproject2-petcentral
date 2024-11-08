<?php
include('header.php');
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
    <title>Doctors List</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background: #f0f2f5;
            color: #1c1e21;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #1877f2;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        .doctors-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            justify-content: center;
        }
        .doctor-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .doctor-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #1877f2;
        }
        .doctor-card h3 {
            margin: 10px 0;
            color: #1c1e21;
        }
        .view-more {
            background: #1877f2;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }
        .view-more:hover {
            background: #166fe5;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            animation: slideIn 0.3s;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .close:hover,
        .close:focus {
            color: #1877f2;
            text-decoration: none;
            cursor: pointer;
        }
        .modal h3 {
            margin-bottom: 20px;
            color: #1877f2;
        }
        .modal p {
            margin-bottom: 15px;
        }
        #chat-button {
            display: block;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Our Expert Doctors</h2>

    <div class="doctors-container">
        <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <img src="uploads/<?php echo htmlspecialchars($doctor['image1']); ?>" alt="Doctor Image">
                <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                <button class="view-more" onclick="openModal('<?php echo htmlspecialchars($doctor['phone']); ?>', '<?php echo htmlspecialchars($doctor['Qualification']); ?>', '<?php echo htmlspecialchars($doctor['experience']); ?>', '<?php echo htmlspecialchars($doctor['lid']); ?>')">View More</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Doctor Details</h3>
        <p><strong>Phone:</strong> <span id="doctor-phone"></span></p>
        <p><strong>Qualification:</strong> <span id="doctor-qualification"></span></p>
        <p><strong>Experience:</strong> <span id="doctor-experience"></span> years</p>
        <button id="chat-button" class="view-more">Chat Now</button>
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