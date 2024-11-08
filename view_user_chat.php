<?php
include('header.php');
require('connection.php'); // Start the session at the beginning of the file

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}


// Establish database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the logged-in user's ID
$user_id = $_SESSION['uid']; // Assuming the user's ID is stored in session

// Fetch chat messages for the logged-in user
$query = "SELECT cm.*, dr.name AS doctor_name FROM chat_message cm 
          JOIN d_registration dr ON cm.did = dr.lid 
          WHERE cm.lid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
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
    <title>User Chat Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .download-icon {
            color: #007bff;
            cursor: pointer;
        }
        .download-icon:hover {
            color: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Your Chat Messages</h1>
    <?php if (empty($messages)): ?>
        <p>No messages found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>SI No.</th>
                    <th>Breed Name</th>
                    <th>Problem</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach ($messages as $message): ?>
                <tr>
                    <td><?php echo htmlspecialchars($counter); ?></td>
                    <td><?php echo htmlspecialchars($message['breed_name']); ?></td>
                    <td><?php echo htmlspecialchars($message['problem']); ?></td>
                    <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                    <td><?php echo !empty($message['reply']) ? 'Replied' : 'Pending'; ?></td>
                    <td>
                        <?php if (!empty($message['reply'])): ?>
                            <i class="fas fa-download download-icon" onclick='showPrescription(<?php echo json_encode($message); ?>)'></i>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                $counter++;
                endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div id="prescriptionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Prescription</h2>
        <div id="prescriptionContent"></div>
        <button onclick="generatePDF()" class="download-icon">Download PDF</button>
    </div>
</div>

<script>
    window.jsPDF = window.jspdf.jsPDF;

    var modal = document.getElementById("prescriptionModal");
    var span = document.getElementsByClassName("close")[0];
    var currentPrescription;

    function showPrescription(message) {
        currentPrescription = message;
        var content = document.getElementById("prescriptionContent");
        content.innerHTML = `
            <p><strong>Breed Name:</strong> ${message.breed_name}</p>
            <p><strong>Age:</strong> ${message.age}</p>
            <p><strong>Vaccination Status:</strong> ${message.vaccination_status}</p>
            <p><strong>Problem:</strong> ${message.problem}</p>
            <p><strong>Findings:</strong> ${message.reply}</p>
            <p><strong>Prescribed Medicine:</strong> ${message.medicine}</p>
            <p><strong>Doctor's Name:</strong> ${message.doctor_name}</p>
            <p><strong>Date:</strong> ${message.created_at}</p>
        `;
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function generatePDF() {
        const doc = new jsPDF();
        const logoUrl = 'images/logo.gif'; // Adjust path to your logo
        const companyName = "PetCentral";
        const companyEmail = "petcentral62@gmail.com";
        
        // Add logo
        doc.addImage(logoUrl, 'PNG', 10, 10, 50, 30);
        
        // Add company name and email
        doc.setFontSize(20);
        doc.setFont("helvetica", "bold");
        doc.text(companyName, 70, 20);
        doc.setFontSize(10);
        doc.setFont("helvetica", "normal");
        doc.text(companyEmail, 70, 30);
        
        // Add Prescription title
        doc.setFontSize(18);
        doc.setFont("helvetica", "bold");
        doc.text('Prescription', 105, 50, null, null, 'center');
        
        // Add prescription details
        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text(`Breed Name: ${currentPrescription.breed_name}`, 20, 70);
        doc.text(`Age: ${currentPrescription.age}`, 20, 80);
        doc.text(`Vaccination Status: ${currentPrescription.vaccination_status}`, 20, 90);
        doc.text(`Problem: ${currentPrescription.problem}`, 20, 100);
        doc.text(`Findings: ${currentPrescription.reply}`, 20, 110);
        doc.text(`Prescribed Medicine: ${currentPrescription.medicine}`, 20, 120);
        doc.text(`Doctor's Name: ${currentPrescription.doctor_name}`, 20, 130);
        doc.text(`Date: ${currentPrescription.created_at}`, 20, 140);
        
        // Footer
        doc.setFontSize(10);
        doc.setFont("helvetica", "italic");
        doc.text('Thank you for choosing PetCentral!', 105, 200, null, null, 'center');
        
        doc.save(`Prescription_${currentPrescription.chatid}.pdf`);
    }
</script>

</body>
</html>