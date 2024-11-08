<?php
session_start();
require('connection.php');

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and chat_id is provided
if (!isset($_SESSION['uid']) || !isset($_POST['chat_id'])) {
    die("Error: Invalid request. Please log in and try again.");
}

$chat_id = $_POST['chat_id'];
$user_id = $_SESSION['uid'];

// Sanitize inputs
$chat_id = filter_var($chat_id, FILTER_SANITIZE_NUMBER_INT);
$user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

// Fetch chat message details
$query = "SELECT cm.*, dr.name AS doctor_name FROM chat_message cm 
          JOIN d_registration dr ON cm.did = dr.lid 
          WHERE cm.chatid = ? AND cm.lid = ?";
$stmt = $con->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $con->error);
}

$stmt->bind_param("ii", $chat_id, $user_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
$message = $result->fetch_assoc();

if (!$message) {
    die("Prescription not found or you don't have permission to access it.");
}

$stmt->close();
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Prescription</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <h1>Prescription Details</h1>
    <button onclick="generatePDF()">Download Prescription</button>

    <script>
        window.jsPDF = window.jspdf.jsPDF;

        function generatePDF() {
            const doc = new jsPDF();
            const logoUrl = 'images/logo.gif'; // Adjust path to your logo
            const companyName = "PetCentral"; // Replace with your company name
            
            // Add logo
            doc.addImage(logoUrl, 'PNG', 10, 10, 50, 30); // Adjust position and size as needed
            
            // Add company name
            doc.setFontSize(20);
            doc.setFont("helvetica", "bold");
            doc.text(companyName, 70, 20);
            
            // Add Prescription title
            doc.setFontSize(18);
            doc.setFont("helvetica", "bold");
            doc.text('Prescription', 105, 50, null, null, 'center');
            
            // Add prescription details
            doc.setFontSize(12);
            doc.setFont("helvetica", "normal");
            doc.text(`Breed Name: <?php echo addslashes($message['breed_name']); ?>`, 20, 70);
            doc.text(`Age: <?php echo addslashes($message['age']); ?>`, 20, 80);
            doc.text(`Vaccination Status: <?php echo addslashes($message['vaccination_status']); ?>`, 20, 90);
            doc.text(`Problem: <?php echo addslashes($message['problem']); ?>`, 20, 100);
            doc.text(`Findings: <?php echo addslashes($message['reply']); ?>`, 20, 110);
            doc.text(`Prescribed Medicine: <?php echo addslashes($message['medicine']); ?>`, 20, 120);
            doc.text(`Doctor's Name: <?php echo addslashes($message['doctor_name']); ?>`, 20, 130);
            doc.text(`Date: <?php echo date('Y-m-d'); ?>`, 20, 140);
            
            // Footer
            doc.setFontSize(10);
            doc.setFont("helvetica", "italic");
            doc.text('Thank you for choosing PetCentral!', 105, 200, null, null, 'center');
            
            doc.save(`Prescription_<?php echo $chat_id; ?>.pdf`);
        }
    </script>
</body>
</html>