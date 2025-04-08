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
    <title>Chat Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
        :root {
            --primary-color: #f9c74f;
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f4f9;
            margin: 0;
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
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 30px;
            text-align: center;
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
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
        }

        /* DataTable Customization */
        .dataTables_wrapper {
            margin-top: 20px;
        }

        table.dataTable thead th {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-weight: 600;
            border-bottom: none;
        }

        .download-icon {
            cursor: pointer;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .download-icon:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
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
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="editpro.php" class="nav-item">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="userpassword.php" class="nav-item">
                <i class="fas fa-lock"></i> Password
            </a>
            <a href="myorders.php" class="nav-item">
                <i class="fas fa-shopping-bag"></i> My Orders
            </a>
            <a href="mycart.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i> My Cart
            </a>
            <a href="get_payment_details.php" class="nav-item">
                <i class="fas fa-credit-card"></i> Payments
            </a>
            <a href="mywishlist.php" class="nav-item">
                <i class="fas fa-heart"></i> Wishlist
            </a>
            <a href="view_user_chat.php" class="nav-item active">
                <i class="fas fa-comments"></i> Chat
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Chat Messages</h2>
            <?php if (empty($messages)): ?>
                <p class="text-center">No messages found.</p>
            <?php else: ?>
                <table id="chatTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>SI No.</th>
                            <th>Breed Name</th>
                            <th>Problem</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo $counter; ?></td>
                                <td><?php echo htmlspecialchars($message['breed_name']); ?></td>
                                <td><?php echo htmlspecialchars($message['problem']); ?></td>
                                <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                <td>
                                    <span class="badge <?php echo !empty($message['reply']) ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo !empty($message['reply']) ? 'Replied' : 'Pending'; ?>
                                    </span>
                                </td>
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
    </div>

    <!-- Keep your existing modal and PDF generation code -->
    <div id="prescriptionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Prescription</h2>
            <div id="prescriptionContent"></div>
            <button onclick="generatePDF()" class="download-icon">Download PDF</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#chatTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc']], // Sort by SI No column ascending
                columnDefs: [
                    {
                        targets: -1, // Last column (download)
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    search: "Search messages:",
                    lengthMenu: "Show _MENU_ messages per page",
                }
            });
        });

        // K<script>
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