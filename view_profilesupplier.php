<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$uid = $_SESSION['uid'];

// Fetch supplier details
$query = "SELECT s.*, l.email FROM s_registration s JOIN login l ON s.lid = l.lid WHERE s.lid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $supplier = $row;
    $suppliers = $row['name']; // For sidebar display
} else {
    echo "<script>alert('Supplier not found.'); window.location.href = 'supplierindex.php';</script>";
    exit();
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Updated Sidebar Styles */
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

        /* Updated Header Styles */
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

        /* Content Adjustment */
        .content {
            margin-left: 260px;
            margin-top: 100px;
            padding: 20px;
            width: calc(100% - 260px);
        }

        /* Minimalist Profile Container */
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 25px 30px;
        }
        
        .section-title {
            font-size: 16px;
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .profile-item {
            margin-bottom: 20px;
        }
        
        .profile-item h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .profile-item p {
            color: #333;
            font-size: 15px;
            font-weight: 400;
        }
        
        .divider {
            height: 1px;
            background: #eee;
            margin: 25px 0;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            color: #1877f2;
            font-size: 18px;
        }
        
        .social-links a:nth-child(2) {
            color: #1da1f2;
        }
        
        .social-links a:nth-child(3) {
            color: #c32aa3;
        }
        
        .btn-edit {
            display: inline-block;
            padding: 8px 20px;
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            margin-top: 15px;
        }
        
        .btn-edit:hover {
            background: #eee;
        }
        
        .btn-edit i {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .content, .top-header {
                width: 100%;
                margin-left: 0;
            }
            .profile-grid {
                grid-template-columns: 1fr;
            }
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
            <a href="view_profilesupplier.php" class="active">
                <i class="fas fa-user"></i>My Profile
            </a>
            <a href="addproductdog.php">
                <i class="fas fa-box"></i>Manage Products
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
            <a href="payment_orders.php">
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
            <p>Here's what's happening with your store today.</p>
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
        <div class="container">
            <h3 class="section-title">Information</h3>
            
            <div class="profile-grid">
                <div class="profile-item">
                    <h4>Email</h4>
                    <p><?php echo htmlspecialchars($supplier['email']); ?></p>
                </div>
                
                <div class="profile-item">
                    <h4>Phone</h4>
                    <p><?php echo htmlspecialchars($supplier['phone']); ?></p>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <h3 class="section-title">Supplier Details</h3>
            
            <div class="profile-grid">
                <div class="profile-item">
                    <h4>Name</h4>
                    <p><?php echo htmlspecialchars($supplier['name']); ?></p>
                </div>
                
                <div class="profile-item">
                    <h4>Supplier Code</h4>
                    <p><?php echo htmlspecialchars($supplier['supplier_code']); ?></p>
                </div>
                
                <div class="profile-item" style="grid-column: span 2;">
                    <h4>Address</h4>
                    <p><?php echo htmlspecialchars($supplier['address']); ?></p>
                </div>
            </div>
            
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
            
            <div style="text-align: right;">
                <a href="edit_profilesupplier.php" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
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