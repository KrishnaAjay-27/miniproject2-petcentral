<?php
include("useraccount.php");
require('connection.php');
if (isset($_SESSION['uid'])) {
    $userid = $_SESSION['uid'];
    $query = "SELECT * FROM registration WHERE lid='$userid'";
    $re = mysqli_query($con, $query);
    $row = mysqli_fetch_array($re);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa; /* Light grey background */
            color: #333;
        }

        .content {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h3 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #ffa500; /* Bright orange color */
            border-bottom: 3px solid #ffa500; /* Orange underline */
            padding-bottom: 10px;
        }

        p {
            font-size: 18px;
            line-height: 1.8;
            color: #555;
        }

        .dashboard-options {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .dashboard-option {
            flex: 1;
            margin: 0 10px;
            padding: 20px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .dashboard-option a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            display: block;
            margin-top: 15px;
            font-size: 18px;
        }

        .dashboard-option a:hover {
            color: #ffa500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            h3 {
                font-size: 24px;
            }

            p {
                font-size: 16px;
            }

            .dashboard-options {
                flex-direction: column;
            }

            .dashboard-option {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
<div class="content">
            <h3>Welcome, <?php echo htmlspecialchars($row['name']); ?>!</h3>
            <p>From your account dashboard, you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
        </div>
    </div>
</body>
</html>
