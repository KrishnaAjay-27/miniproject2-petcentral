<?php
include("header.php");

require('connection.php');

if (isset($_SESSION['uid'])) {
    $userid = $_SESSION['uid'];
    $query = "SELECT * FROM registration WHERE lid='$userid'";
    $re = mysqli_query($con, $query);
    $row = mysqli_fetch_array($re);
} else {
    echo "<script>window.location.href='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f9c74f; /* Yellow theme color */
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
        }

        .page-container {
            display: flex;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            gap: 20px;
        }

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

        .dashboard-container {
            flex: 1;
            padding: 20px;
            background-color: var(--bg-color);
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            color: var(--text-color);
        }

        .welcome-section h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
            border: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--secondary-color);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 32px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 16px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .action-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            background-color: var(--secondary-color);
        }

        .action-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .action-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .action-desc {
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
                padding: 10px;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }

            .dashboard-container {
                margin: 10px;
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="userdashboard.php" class="nav-item active">
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
            <a href="view_user_chat.php" class="nav-item">
                <i class="fas fa-comments"></i> Chat
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Your existing dashboard content -->
        <div class="dashboard-container">
            <div class="welcome-section">
                <h3>Welcome back, <?php echo htmlspecialchars($row['name']); ?>! 👋</h3>
                <p>Here's what's happening with your account today.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Total Orders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Wishlist Items</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Cart Items</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-number">8</div>
                    <div class="stat-label">Messages</div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="seebooking.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="action-title">Booking</div>
                    <div class="action-desc">See The Booking Statics</div>
                </a>

                <a href="myorders.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="action-title">Track Orders</div>
                    <div class="action-desc">View your order status</div>
                </a>

                <a href="get_payment_details.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="action-title">Payments</div>
                    <div class="action-desc">View your transactions</div>
                </a>

                <a href="view_user_chat.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="action-title">Messages</div>
                    <div class="action-desc">Check your conversations</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>