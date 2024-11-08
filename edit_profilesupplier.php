<?php
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

require('connection.php');

$uid = $_SESSION['uid'];
$success_message = '';
$error_message = '';

// Fetch current supplier details
$query = "SELECT name, phone, address FROM s_registration WHERE lid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validation
    if (empty($name) || empty($phone) || empty($address)) {
        $error_message = "All fields are required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error_message = "Please enter a valid 10-digit phone number.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($supplier['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($supplier['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($supplier['address']); ?></textarea>
            </div>

            <button type="submit" class="submit-btn">Update Profile</button>
        </form>

        <a href="supplierindex.php" class="back-link">Back to Dashboard</a>
    </div>

    <script>
        // Add client-side validation for phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>