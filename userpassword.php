<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['uid'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Server-side password validation
    $passwordPattern = '/^(?=.*[!@#$%^&*])(?=.*[0-9])(?=.*[A-Za-z]).{8,}$/';
    if (!preg_match($passwordPattern, $newPassword)) {
        $error = "New password must be at least 8 characters long and contain at least one special character and one number.";
    } else {
        // Verify current password
        $query = "SELECT password FROM login WHERE lid = $userid";
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            die("Query failed: " . mysqli_error($con));
        }
        
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            die("User not found");
        }

        if ($currentPassword === $user['password']) {
            // Password is correct
            $updateQuery = "UPDATE login SET password = '$newPassword' WHERE lid = $userid";
            $result = mysqli_query($con, $updateQuery);
            
            if ($result) {
                // Password updated successfully, destroy session and redirect to login page
                session_destroy();
                header("Location: login.php?message=Password updated successfully. Please login again.");
                exit();
            } else {
                $error = "Error updating password. Please try again. " . mysqli_error($con);
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input[type="password"] {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-button:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #721c24;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .password-requirements {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function validatePassword(password) {
                var passwordPattern = /^(?=.*[!@#$%^&*])(?=.*[0-9])(?=.*[A-Za-z]).{8,}$/;
                return passwordPattern.test(password);
            }

            function showError(inputElement, errorMessage) {
                hidePasswordRequirements();
                var errorElement = inputElement.next('.error-message');
                if (errorElement.length === 0) {
                    errorElement = $('<div class="error-message"></div>').insertAfter(inputElement);
                }
                errorElement.text(errorMessage);
            }

            function hideError(inputElement) {
                inputElement.next('.error-message').remove();
            }

            function showPasswordRequirements() {
                $('#password-requirements').show();
            }

            function hidePasswordRequirements() {
                $('#password-requirements').hide();
            }

            $('#new_password').on('input', function() {
                var password = $(this).val();
                if (password.length > 0) {
                    if (!validatePassword(password)) {
                        showError($(this), "Password must be at least 8 characters long and contain at least one special character and one number.");
                    } else {
                        hideError($(this));
                        hidePasswordRequirements();
                    }
                } else {
                    hideError($(this));
                    showPasswordRequirements();
                }
            });

            $('#confirm_password').on('input', function() {
                var confirmPassword = $(this).val();
                var newPassword = $('#new_password').val();
                if (confirmPassword.length > 0) {
                    if (confirmPassword !== newPassword) {
                        showError($(this), "Passwords do not match.");
                    } else {
                        hideError($(this));
                    }
                } else {
                    hideError($(this));
                }
            });

            $('form').submit(function(e) {
                var newPassword = $('#new_password').val();
                var confirmPassword = $('#confirm_password').val();

                if (!validatePassword(newPassword)) {
                    e.preventDefault();
                    showError($('#new_password'), "Password must be at least 8 characters long and contain at least one special character and one number.");
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    showError($('#confirm_password'), "Passwords do not match.");
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Update Password</h2>
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <small id="password-requirements" class="password-requirements">Password must be at least 8 characters long and contain at least one special character and one number.</small>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <input type="submit" value="Update Password">
        </form>
        <a href="profile.php" class="back-button">Back to Profile</a>
    </div>
</body>
</html>