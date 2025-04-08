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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    
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
            transform: scale(1.1);
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
            <a href="get_payment_details.php" class="nav-item active">
                <i class="fas fa-credit-card"></i> Payments
            </a>
            <a href="mywishlist.php" class="nav-item">
                <i class="fas fa-heart"></i> Wishlist
            </a>
            <a href="view_user_chat.php" class="nav-item">
                <i class="fas fa-comments"></i> Chat
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Payment History</h2>
            <table id="paymentsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>SI No.</th>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($counter); ?></td>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td>Rs. <?php echo htmlspecialchars($row['amount']); ?></td>
                        <td>
                            <span class="badge <?php echo $row['payment_status'] == 'Completed' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo htmlspecialchars($row['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td>
                            <i class="fas fa-download download-icon" onclick='downloadPDF(<?php echo json_encode($row); ?>)'></i>
                        </td>
                    </tr>
                    <?php $counter++; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        window.jsPDF = window.jspdf.jsPDF;
        
        $(document).ready(function() {
            $('#paymentsTable').DataTable({
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
                    search: "Search payments:",
                    lengthMenu: "Show _MENU_ payments per page",
                }
            });
        });

        // Keep your existing downloadPDF function
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
</body>
</html>

<?php
$stmt->close();
$con->close();
?>