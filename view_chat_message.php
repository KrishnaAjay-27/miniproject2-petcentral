<?php
include('doctorindex.php');
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the logged-in doctor's ID
$doctor_id = $_SESSION['uid']; // Assuming the doctor's ID is stored in session

// Fetch chat messages for the logged-in doctor, including user details
$query = "SELECT cm.*, r.name AS user_name, r.district FROM chat_message cm 
          JOIN registration r ON cm.lid = r.lid 
          WHERE cm.did = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Messages</title>
    <style>
        /* Basic styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background: #f2f2f2;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .message {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            background: #f9f9f9;
            transition: background 0.3s;
        }
        .message:hover {
            background: #f1f1f1;
        }
        .message p {
            margin: 5px 0;
        }
        .reply-form {
            margin-top: 20px;
        }
        .reply-form textarea,
        .reply-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .reply-btn {
            background: #60adde;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        .reply-btn:hover {
            background: #003366;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Chat Messages</h2>
    <?php if (empty($messages)): ?>
        <p>No messages found.</p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <p><strong>Name (Pet Owner):</strong> <?php echo htmlspecialchars($message['user_name']); ?></p>
                <p><strong>District:</strong> <?php echo htmlspecialchars($message['district']); ?></p>
                <p><strong>Breed Name:</strong> <?php echo htmlspecialchars($message['breed_name']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($message['age']); ?></p>
                <p><strong>Vaccination Status:</strong> <?php echo htmlspecialchars($message['vaccination_status']); ?></p>
                <p><strong>Problem:</strong> <?php echo htmlspecialchars($message['problem']); ?></p>
                <p><strong>Submitted At:</strong> <?php echo htmlspecialchars($message['created_at']); ?></p>
                <?php if (isset($message['reply']) && !empty($message['reply'])): ?>
                    <p><strong>Doctor's Reply:</strong> <?php echo htmlspecialchars($message['reply']); ?></p>
                <?php else: ?>
                    <p><strong>Status:</strong> Pending Reply</p>
                    <form class="reply-form" method="POST" action="reply_to_chat.php">
                        <input type="hidden" name="chat_id" value="<?php echo $message['chatid']; ?>">
                        <textarea name="reply" rows="3" placeholder="Type your reply here..." required></textarea>
                        <input type="text" name="medicine" placeholder="Medicine prescribed (if any)" required>
                        <button type="submit" class="reply-btn">Send Reply</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="doctorindex.php" class="back-link">Back to Dashboard</a>
</div>

</body>
</html>
