<?php
include("header.php");
include("goback.php");
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f7f7; /* Very light gray background */
            color: #333;
        }

        /* Header Styling */
        .header {
            background: lavender; /* Soft yellow background */
            color: #333; /* Dark text for contrast */
            padding: 20px;
            text-align: center;
            border-bottom: 5px solid lavenderblush; /* Darker yellow border */
        }

        .header h1 {
            font-size: 36px;
            font-weight: 700;
        }

        /* Main Container Styling */
        .main-container {
            display: flex;
            flex-direction: row;
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: lavenderblush;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: lavender; /* Soft yellow background */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }

        .sidebar a {
            text-decoration: none;
        }

        .sidebar button {
            display: block;
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            background-color: lavender; /* Bright yellow background */
            color: #333; /* Dark text */
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-align: left;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        .sidebar button:hover {
            background-color: lavenderblush; /* Darker yellow on hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .sidebar button:active {
            background-color: lavenderblush; /* Even darker yellow for active state */
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Content Styling */
        .content {
            flex: 1;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 20px;
            border-radius: 8px;
        }

        .content h1 {
            font-size: 28px;
            color: black; /* Black text */
            margin-bottom: 20px;
            border-bottom: 3px solid black; /* Black underline effect */
            padding-bottom: 10px;
        }

        /* Modified Welcome Message Styling */
        .content h3 {
            font-size: 24px;
            color: black; /* Change text color to black */
            margin-bottom: 20px;
            border-bottom: 3px solid black; /* Add black underline effect */
            padding-bottom: 10px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 3px solid black; /* Black border */
        }

        /* Card Layout for Dashboard Widgets */
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
        }

        .card p {
            font-size: 16px;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                padding: 10px;
            }

            .sidebar {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .header h1 {
                font-size: 32px;
            }

            .content h1 {
                font-size: 24px;
            }

            .content p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>My Account</h1>
    </div>
    <div class="main-container">
        <div class="sidebar">
            <a href="userdashboard.php"><button>DASHBOARD</button></a>
            <a href="profile.php"><button>PROFILE</button></a>
            <a href="editprofile.php"><button>EDIT PROFILE</button></a>
            <a href="userpass.php"><button>PASSWORD</button></a>
            <a href="myorders.php"><button>MY ORDERS</button></a>
            <a href="mycart.php"><button>MY CART</button></a>
            <a href="mywishlist.php"><button>MY WISHLIST</button></a>
            <a href="logout.php"><button>LOGOUT</button></a>
        </div>
        
</body>
</html>
