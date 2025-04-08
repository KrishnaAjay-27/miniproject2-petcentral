<?php
session_start();
require('connection.php');


// Fetch supplier details and stock data


if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

require('connection.php');

$uid = $_SESSION['uid'];
$success_message = '';
$error_message = '';

// Fetch current supplier details
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validation for Name: No special characters, no leading spaces, no numbers
    if (empty($name) || !preg_match("/^[a-zA-Z ]+$/", $name)) {
        $error_message = "Name must only contain letters and spaces, with no leading spaces.";
    }
    // Validation for Phone: Starts with 6,7,8,9 and exactly 10 digits
    elseif (empty($phone) || !preg_match("/^[6789][0-9]{9}$/", $phone)) {
        $error_message = "Phone number must start with 6, 7, 8, or 9 and be 10 digits long.";
    }
    // Validation for Address: No leading spaces and no repeated characters like "aaaa"
    elseif (empty($address) || preg_match("/^\s+/", $address) || preg_match("/(.)\1{2,}/", $address)) {
        $error_message = "Address must not have leading spaces and must not contain repeated characters.";
    } else {
        // Update the supplier details
        $update_query = "UPDATE s_registration SET name = ?, phone = ?, address = ? WHERE lid = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("sisi", $name, $phone, $address, $uid);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh supplier details after update
            $supplier['name'] = $name;
            $supplier['phone'] = $phone;
            $supplier['address'] = $address;
        } else {
            $error_message = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            margin-top: 100px;
            padding: 20px;
            width: calc(100% - 260px);
        }

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
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            transition: border 0.2s;
        }

        input[type="text"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            border-color: #003366;
            outline: none;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .submit-btn {
            background: #003366;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .submit-btn:hover {
            background: #004080;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            color: #333;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            <a href="edit_profilesupplier.php" class="active">
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
            <h3 class="section-title">Edit Profile Information</h3>
            
            <?php if ($success_message): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($supplier['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($supplier['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required><?php echo htmlspecialchars($supplier['address']); ?></textarea>
                </div>

                <div class="form-actions">
                    
                    <button type="submit" class="submit-btn">Update Profile</button>
                </div>
            </form>
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

        // Name validation (No special characters, no numbers, no leading spaces)
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            if (/[^a-zA-Z ]/.test(name)) {
                alert('Name should only contain letters and spaces.');
            }
        });

        // Phone validation (Starts with 6,7,8,9 and 10 digits)
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, ''); // Allow only digits
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            if (!/^[6789]/.test(value) && value.length === 10) {
                alert('Phone number must start with 6, 7, 8, or 9.');
            }
            this.value = value;
        });

        // Address validation (No leading spaces and no repeated characters)
        document.getElementById('address').addEventListener('input', function() {
            const address = this.value;
            if (/^\s+/.test(address)) {
                alert('Address must not have leading spaces.');
            }
            if (/(.)\1{2,}/.test(address)) {
                alert('Address must not contain repeated characters like "aaaa".');
            }
        });
    </script>
</body>
</html>
