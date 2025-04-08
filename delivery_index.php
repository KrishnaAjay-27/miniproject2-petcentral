<?php
session_start();
require('connection.php');

// Check if delivery boy is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];

// Fetch the delivery boy's details
$query = "SELECT name, assign_date FROM deliveryboy WHERE lid='$deid'";
$result = mysqli_query($con, $query);
$delivery_boy = mysqli_fetch_assoc($result);
$delivery_boy_name = $delivery_boy ? $delivery_boy['name'] : 'Delivery Boy';

// Fetch delivery statistics
$stats_query = "
    SELECT 
        COUNT(CASE WHEN order_status = 3 THEN 1 END) as delivered_orders,
        COUNT(CASE WHEN order_status = 2 THEN 1 END) as pending_orders,
        COUNT(*) as total_orders
    FROM order_details 
    WHERE deid = '$deid'";
$stats_result = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
        }

        .sidebar {
            background: linear-gradient(to bottom, #003366, #004080);
            color: white;
            width: 250px;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            width: 100%;
        }

        .sidebar h1 {
            margin: 0;
            padding-bottom: 5px;
            font-size: 22px;
            color: white;
        }

        .sidebar h2 {
            font-size: 16px;
            font-weight: normal;
            margin: 5px 0;
            color: #cce0ff;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding-top: 20px;
        }

        .nav-links a {
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            width: 100%;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-links a i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.1);
            padding-left: 25px;
        }

        .nav-links a.active {
            background-color: rgba(255,255,255,0.2);
            border-left: 4px solid #fff;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        .content-header {
            margin-bottom: 25px;
        }

        .content-header h1 {
            color: #003366;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stats-label {
            color: #6c757d;
            font-size: 14px;
        }

        .logout-btn {
            margin-top: auto;
            width: 80%;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }

        .quick-actions {
            margin-top: 30px;
        }

        .action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .action-icon {
            font-size: 24px;
            margin-bottom: 15px;
            color: #003366;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Welcome</h1>
            <h2><?php echo htmlspecialchars($delivery_boy_name); ?></h2>
        </div>
        
        <div class="nav-links">
            <a href="delivery_index.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="viewdeliveryassignment.php">
                <i class="fas fa-truck"></i> View Assignments
            </a>
            <a href="notificationdelivery.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </div>
        
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="content">
        <div class="content-header">
            <h1>Delivery Dashboard</h1>
            <p class="text-muted">Overview of your delivery activities</p>
        </div>

        <div class="row">
            <!-- Total Orders -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #e3f2fd;">
                        <i class="fas fa-boxes fa-2x text-primary"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>

            <!-- Delivered Orders -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #e8f5e9;">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['delivered_orders']; ?></div>
                    <div class="stats-label">Delivered Orders</div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #fff3e0;">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['pending_orders']; ?></div>
                    <div class="stats-label">Pending Deliveries</div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h4 class="mb-4">Quick Actions</h4>
            <div class="row">
                <div class="col-md-6">
                    <a href="viewdeliveryassignment.php" class="text-decoration-none">
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h5>View Assignments</h5>
                            <p class="text-muted mb-0">Check your delivery assignments and update their status</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="notificationdelivery.php" class="text-decoration-none">
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <h5>Check Notifications</h5>
                            <p class="text-muted mb-0">View your latest notifications and updates</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($con); ?> 
 