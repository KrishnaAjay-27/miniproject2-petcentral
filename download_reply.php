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

// Check if chat_id is set
if (isset($_POST['chat_id'])) {
    $chat_id = $_POST['chat_id'];

    // Fetch the reply, doctor's name, and user's details
    $query = "SELECT cm.reply, cm.medicine, cm.breed_name, cm.age, cm.problem,
                     (SELECT dr.name FROM registration dr WHERE dr.lid = cm.lid) AS user_name,
                     (SELECT dr.district FROM registration dr WHERE dr.lid = cm.lid) AS user_district,
                     (SELECT d.name FROM d_registration d WHERE d.lid = cm.did) AS doctor_name 
              FROM chat_message cm 
              WHERE cm.chatid = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $chat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();

    if ($message) {
        $breed_name = $message['breed_name'];
        $age = $message['age'];
        $problem = $message['problem'];
        $reply = $message['reply'];
        $doctor_name = $message['doctor_name'];
        $medicine = $message['medicine'];
        $user_name = $message['user_name'];
        $user_district = $message['user_district'];

        // Prepare HTML content with JavaScript for PDF download
        $content = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Prescription Download</title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    margin-top: 100px;
                }
                button {
                    padding: 10px 20px;
                    font-size: 16px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #45a049;
                }
            </style>
            <script>
                window.jsPDF = window.jspdf.jsPDF;

                function downloadPDF() {
                    const doc = new jsPDF();
                    const logoUrl = "images/logo.gif"; // Adjust path to your logo
                    const companyName = "PetCentral";
                    const companyEmail = "petcentral62@gmail.com";

                    // Add logo
                    doc.addImage(logoUrl, "PNG", 10, 10, 50, 30);

                    // Add company name and email
                    doc.setFontSize(20);
                    doc.setFont("helvetica", "bold");
                    doc.text(companyName, 70, 20);
                    doc.setFontSize(10);
                    doc.text(companyEmail, 70, 30);

                    // Title
                    doc.setFontSize(18);
                    doc.text("Prescription", 105, 50, null, null, "center");

                    // User details
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "normal");
                    doc.text("Patient\'s Name: ' . htmlspecialchars($user_name) . '", 20, 70);
                    doc.text("District: ' . htmlspecialchars($user_district) . '", 20, 80);
                    doc.text("Doctor\'s Name: ' . htmlspecialchars($doctor_name) . '", 20, 90);
                    doc.text("Dog Breed: ' . htmlspecialchars($breed_name) . '", 20, 100);
                    doc.text("Age: ' . htmlspecialchars($age) . '", 20, 110);
                    doc.text("Problem: ' . htmlspecialchars($problem) . '", 20, 120);
                    doc.text("Finds: ' . htmlspecialchars($reply) . '", 20, 140);
                    doc.text("Medicine Prescribed: ' . htmlspecialchars($medicine) . '", 20, 160);

                    // Footer
                    doc.setFontSize(10);
                    doc.setFont("helvetica", "italic");
                    doc.text("Thank you for your trust!", 105, 200, null, null, "center");

                    // Save PDF
                    doc.save("prescription.pdf");
                }
            </script>
        </head>
        <body>
            <button onclick="downloadPDF()">Download Prescription</button>
        </body>
        </html>';

        // Set headers to display the HTML content
        header('Content-Type: text/html');
        echo $content;
    } else {
        echo "No reply found.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>
