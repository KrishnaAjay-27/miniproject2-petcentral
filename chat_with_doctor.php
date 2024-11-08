<?php
// Include the Twilio PHP library (ensure you have installed it via Composer)
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
require('connection.php');
include('header.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all doctors
$query = "SELECT * FROM d_registration";
$result = mysqli_query($con, $query);
$doctors = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['uid'];
    $doctor_id = $_POST['did'] ?? null;
    $breed_name = $_POST['breed_name'] ?? null;
    $age = $_POST['age'] ?? null;
    $vaccination_status = $_POST['vaccination_status'] ?? null;
    $problem = $_POST['problem'] ?? null;

    // Validate the required fields
    if (empty($doctor_id) || empty($breed_name) || empty($age) || empty($vaccination_status) || empty($problem)) {
        echo "<script>alert('Please fill in all fields.');</script>";
        exit;
    }

    // Insert chat message into the database
    $stmt = $con->prepare("INSERT INTO chat_message (lid, did, breed_name, age, vaccination_status, problem) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $user_id, $doctor_id, $breed_name, $age, $vaccination_status, $problem);
    $stmt->execute();
    $stmt->close();

    // Send SMS to the doctor
   // Fetch doctor phone number and format it
$doctor_info_query = "SELECT phone, name FROM d_registration WHERE lid = ?";
$stmt = $con->prepare($doctor_info_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$stmt->bind_result($doctor_phone, $doctor_name);
$stmt->fetch();
$stmt->close();

// Ensure the phone number is in E.164 format
if (strpos($doctor_phone, '+') !== 0) {
    // Assuming it's an Indian number; adjust the country code as needed
    $doctor_phone = '+91' . $doctor_phone; // Adjust this for your country
}

// Check if doctor phone number is valid
if (empty($doctor_phone)) {
    echo "<script>alert('Doctor phone number not found.');</script>";
    exit;
}


    // Twilio API Credentials
    $account_sid = 'AC62053f58b59fb05c6c45baae390f51a3';
    $auth_token = '5793fbce6e93204c84d6e0403688661e';
    $twilio_number = '+19162998178'; // Replace with your Twilio phone number

    // Initialize Twilio Client
    $client = new Client($account_sid, $auth_token);

    try {
        // Send the SMS
        $client->messages->create(
            $doctor_phone,
            [
                'from' => $twilio_number,
                'body' => "Hello Dr. $doctor_name, a new chat message from the client has arrived for you. Please log in to check and reply."
            ]
        );

        echo "<script>alert('Message sent to the doctor!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send SMS: " . $e->getMessage() . "');</script>";
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Doctor</title>
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
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background: #60adde;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background: #003366;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Chat with Doctor</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="doctor_id">Select Doctor:</label>
            <select name="did" id="doctor_id" required>
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?php echo htmlspecialchars($doctor['lid']); ?>"><?php echo htmlspecialchars($doctor['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="breed_name">Breed Name:</label>
            <input type="text" name="breed_name" id="breed_name" required>
        </div>
        <div class="form-group">
            <label for="age">Age of Dog:</label>
            <input type="number" name="age" id="age" required>
        </div>
        <div class="form-group">
            <label for="vaccination_status">Vaccination Status:</label>
            <input type="text" name="vaccination_status" id="vaccination_status" required>
        </div>
        <div class="form-group">
            <label for="problem">Problem:</label>
            <textarea name="problem" id="problem" rows="4" required></textarea>
        </div>
        <button type="submit" class="submit-btn">Submit</button>
    </form>
</div>

</body>
</html>
