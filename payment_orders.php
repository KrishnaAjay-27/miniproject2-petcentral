<?php
session_start();
require('connection.php');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Retrieve the supplier ID based on user ID
$uid = $_SESSION['uid'];
$sidQuery = "SELECT s.*, l.email FROM s_registration s JOIN login l ON s.lid = l.lid WHERE s.lid = '$uid'";
$sidResult = mysqli_query($con, $sidQuery);
if ($sidResult && mysqli_num_rows($sidResult) > 0) {
    $sidRow = mysqli_fetch_assoc($sidResult);
    $supplier_id = intval($sidRow['sid']);
    $suppliers = $sidRow['name']; // For sidebar display
} else {
    die("Supplier ID (sid) not found for the logged-in user.");
}

// Fetch payment and user details
$query = "
    SELECT 
        p.payment_id, 
        p.order_id, 
        p.amount, 
        p.payment_status, 
        p.payment_date, 
        r.name, 
        r.phone 
    FROM 
        payments p 
    INNER JOIN 
        registration r ON p.lid = r.lid
";
$result = mysqli_query($con, $query);
$payments = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f4f7fe;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: #003366;
            padding: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 10px;
        }

        .sidebar-header h1 {
            color: white;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header h2 {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            font-weight: normal;
        }

        .nav-links {
            padding: 10px 0;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            font-size: 15px;
            transition: 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a i {
            width: 24px;
            font-size: 16px;
            margin-right: 10px;
        }

        /* Header Styles */
        .top-header {
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - 260px);
            background: white;
            color: #333;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-text h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 2px;
        }

        .welcome-text p {
            color: #666;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .date-time {
            background: #f5f5f5;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }

        .logout-btn {
            background: #cc0000;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: #b30000;
        }

        /* Content Styles */
        .content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 20px;
            width: calc(100% - 260px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .status {
            font-weight: 500;
        }

        .status-0 {
            color: #dc3545;
        }

        .status-1 {
            color: #198754;
        }

        @media (max-width: 768px) {
            .sidebar, .top-header {
                display: none;
            }
            .content {
                width: 100%;
                margin-left: 0;
                margin-top: 20px;
            }
        }

        /* Update the DataTables styling */
        table.dataTable thead th {
            background-color: #003366 !important;
            color: white !important;
            font-weight: 500;
            padding: 12px;
            border-bottom: 2px solid #003366;
        }

        /* Add this to override Bootstrap's default header styling */
        .table > thead {
            background-color: #003366 !important;
            color: white !important;
        }

        .table > thead > tr > th {
            background-color: #003366 !important;
            color: white !important;
            border-bottom: none;
        }

        /* Additional styling for better appearance */
        .card-header {
            background-color: white !important;
            color: #333 !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #333;
            margin: 10px 0;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Welcome</h1>
            <h2><?php echo htmlspecialchars($suppliers); ?></h2>
        </div>
        <div class="nav-links">
            <a href="supplierindex.php">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="view_profilesupplier.php">
                <i class="fas fa-user"></i>My Profile
            </a>
            <a href="addproductdog.php">
                <i class="fas fa-dog"></i>Manage Dogs
            </a>
            <a href="addproductpets.php">
                <i class="fas fa-paw"></i>Manage Pets
            </a>
            <a href="viewproduct.php">
                <i class="fas fa-list"></i>View Products
            </a>
            <a href="viewpetstbl.php">
                <i class="fas fa-table"></i>View Pets
            </a>
            <a href="view_orders.php">
                <i class="fas fa-shopping-cart"></i>Order History
            </a>
            <a href="payment_orders.php" class="active">
                <i class="fas fa-money-bill"></i>Payment History
            </a>
            <a href="edit_profilesupplier.php">
                <i class="fas fa-edit"></i>Edit Profile
            </a>
            <a href="supplierpassword.php">
                <i class="fas fa-key"></i>Change Password
            </a>
        </div>
    </div>

    <div class="top-header">
        <div class="welcome-text">
            <h1>Welcome Back, <?php echo htmlspecialchars($suppliers); ?>!</h1>
            <p>View your payment history and transactions.</p>
        </div>
        <div class="header-right">
            <div class="date-time">
                <i class="fas fa-calendar"></i>
                <span id="currentDateTime"></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid px-4">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Payment History</h5>
                        <a href="download_payment_report.php" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p>No payment records found.</p>
                    <?php else: ?>
                        <table id="paymentsTable" class="table table-hover display nowrap w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Payment ID</th>
                                    <th>Order ID</th>
                                    <th>User Name</th>
                                    <th>Phone Number</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $index => $payment): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['phone']); ?></td>
                                        <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $payment['payment_status'] == 1 ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $payment['payment_status'] == 1 ? 'Paid' : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString();
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>
