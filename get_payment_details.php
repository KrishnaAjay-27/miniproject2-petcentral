<?php
include("header.php");
require('connection.php');

if (isset($_SESSION['uid'])) {
    $userid = $_SESSION['uid'];
} else {
    echo "<script>window.location.href='login.php';</script>";
}

$query = "
    SELECT p.payment_id, p.order_id, p.amount, 
           CASE 
               WHEN p.payment_status = 0 THEN 'Pending'
               WHEN p.payment_status = 1 THEN 'Completed'
               ELSE 'Unknown'
           END AS payment_status, 
           p.payment_date, 
           r.name, r.email, r.address, 
           GROUP_CONCAT(od.product_name SEPARATOR ', ') AS product_names,
           GROUP_CONCAT(od.size SEPARATOR ', ') AS sizes,
           GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities
    FROM payments p
    JOIN registration r ON p.lid = r.lid
    JOIN order_details od ON p.order_id = od.order_id
    WHERE p.lid = ? 
    GROUP BY p.payment_id
    ORDER BY p.payment_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
        
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color:#f9c74f; /* Green background */
            color: white; /* White text */
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Light gray for even rows */
        }

        tr:hover {
            background-color: #f1f1f1; /* Light gray on hover */
        }

        .download-icon {
            cursor: pointer;
            color: #007bff; /* Bootstrap primary color */
            transition: color 0.3s;
        }

        .download-icon:hover {
            color: #0056b3; /* Darker blue on hover */
        }

        @media (max-width: 768px) {
            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <script>
        window.jsPDF = window.jspdf.jsPDF;

        function downloadPDF(paymentData) {
            const doc = new jsPDF();
            const logoUrl = 'images/logo.gif'; // Adjust path to your logo
            const companyName = "PetCentral"; // Replace with your company name
            const companyEmail = "petcentral62@gmail.com"; // Replace with your company email
            
            // Add logo
            doc.addImage(logoUrl, 'PNG', 10, 10, 50, 30); // Adjust position and size as needed
            
            // Add company name
            doc.setFontSize(20);
            doc.setFont("helvetica", "bold");
            doc.text(companyName, 70, 20);
            doc.setFontSize(10);
            doc.setFont("helvetica", "normal");
            doc.text(companyEmail, 70, 30); // Add company email if needed
            
            // Add Invoice title
            doc.setFontSize(18);
            doc.setFont("helvetica", "bold");
            doc.text('Payment', 105, 50, null, null, 'center');
            
            // Add user details
            doc.setFontSize(12);
            doc.setFont("helvetica", "normal");
            doc.text(`Name: ${paymentData.name}`, 20, 70);
            doc.text(`Email: ${paymentData.email}`, 20, 80);
            doc.text(`Address: ${paymentData.address}`, 20, 90);
            
            // Add invoice details
            doc.text(`Payment ID: ${paymentData.payment_id}`, 20, 110);
            doc.text(`Order ID: ${paymentData.order_id}`, 20, 120);
            doc.text(`Amount: Rs. ${paymentData.amount}`, 20, 130);
            doc.text(`Payment Status: ${paymentData.payment_status}`, 20, 140);
            doc.text(`Payment Date: ${paymentData.payment_date}`, 20, 150);
            
            // Add order details
            doc.text(`Products: ${paymentData.product_names}`, 20, 160);
            doc.text(`Quantities: ${paymentData.quantities}`, 20, 180);
            
            // Footer
            doc.setFontSize(10);
            doc.setFont("helvetica", "italic");
            doc.text('Thank you for your business!', 105, 200, null, null, 'center');
            
            doc.save(`payment_${paymentData.payment_id}.pdf`);
        }
    </script>

    <h1>Payment History</h1>
    <table>
        <thead>
            <tr>
                <th>SI No.</th>
                <th>Order ID</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Payment Date</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1;
            while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($counter); ?></td>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['amount']; ?></td>
                <td><?php echo $row['payment_status']; ?></td>
                <td><?php echo $row['payment_date']; ?></td>
                <td>
                    <i class="fas fa-download download-icon" onclick='downloadPDF(<?php echo json_encode($row); ?>)'></i>
                </td>
            </tr>
            <?php $counter++; // Increment counter ?>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$stmt->close();
$con->close();
?>