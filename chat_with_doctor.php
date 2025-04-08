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
    $auth_token = '1fa631f290337c1a84635eb542548afe';
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
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
        .error-message {
            color: red;
            display: none; /* Hidden by default */
            margin-top: 5px;
        }
        .chat-container {
            max-width: 600px;
            margin: 20px auto;
            background: #E5DDD5;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chat-header {
           
            background:#f9c74f;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        .doctor-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .doctor-info i {
            font-size: 24px;
        }
        .chat-messages {
            padding: 20px;
            height: calc(100vh - 140px);
            overflow-y: auto;
        }
        .system-message {
            text-align: center;
            background: rgba(225, 245, 254, 0.92);
            padding: 8px 12px;
            border-radius: 8px;
            margin: 10px auto;
            max-width: 80%;
            font-size: 0.9em;
            color: #075E54;
        }
        .message-bubble {
            max-width: 80%;
            margin: 10px 0;
            padding: 15px;
            border-radius: 7.5px;
            position: relative;
        }
        .message-bubble.incoming {
            background: white;
            margin-right: auto;
            border-top-left-radius: 0;
        }
        .message-bubble label {
            display: block;
            color: #075E54;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .message-bubble input,
        .message-bubble select,
        .message-bubble textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #DCE6EC;
            border-radius: 4px;
            font-size: 15px;
            background: #F8FAFC;
            transition: all 0.3s ease;
        }
        .message-bubble input:focus,
        .message-bubble select:focus,
        .message-bubble textarea:focus {
            outline: none;
            border-color: #128C7E;
            background: white;
        }
        .chat-input-area {
            background: white;
            padding: 15px;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #E2E8F0;
            display: flex;
            justify-content: flex-end;
        }
        .send-button {
            background: #f9c74f;;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .send-button:hover {
            background:#f9c74f;;
        }
        .send-button i {
            font-size: 18px;
        }
        .chat-messages {
            scroll-behavior: smooth;
        }
        @media (max-width: 640px) {
            .chat-container {
                margin: 0;
                border-radius: 0;
                height: 100vh;
            }
            .chat-messages {
                height: calc(100vh - 130px);
            }
        }
        .message-bubble {
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
  <script>
   

   function validateForm() {
    const breedInput = document.getElementById('breed_name');
    const ageInput = document.getElementById('age');
    const vaccinationInput = document.getElementById('vaccination_status');
    const problemInput = document.getElementById('problem');
    const breedError = document.getElementById('breed_error');
    const ageError = document.getElementById('age_error');
    const vaccinationError = document.getElementById('vaccination_error');
    const problemError = document.getElementById('problem_error');

    let isValid = true;

    // List of disallowed words (case insensitive)
    const invalidWords = ['popoooo', 'popo', 'sir', 'pop', 'poooo']; // Add more as needed

    // Validate Breed Name
    const breedValue = breedInput.value.trim(); // Trim leading/trailing spaces
    const breedRegex = /^[A-Za-z\s]+$/; // Only letters and spaces
    const specialCharRegex = /[!@#$%^&*(),.?":{}|<>]/; // Special characters
    const repeatedCharRegex = /^(.)\1{2,}$/; // Check for three or more repeated characters
    if (!breedRegex.test(breedValue) || breedValue.startsWith(' ') || specialCharRegex.test(breedValue) || repeatedCharRegex.test(breedValue)) {
        breedError.style.display = 'block';
        breedError.textContent = 'Please enter a valid breed name (only letters and spaces, no leading spaces, no special characters, and no repeated characters).';
        isValid = false;
    } else {
        breedError.style.display = 'none';
    }

    // Validate Age
    const ageValue = ageInput.value.trim(); // Trim leading/trailing spaces
    const ageRegex = /^[A-Za-z0-9\s]+$/; // Only letters, numbers, and spaces
    if (!ageRegex.test(ageValue) || ageValue.startsWith(' ')) {
        ageError.style.display = 'block';
        ageError.textContent = 'Please enter a valid age (only letters, numbers, and spaces, no leading spaces).';
        isValid = false;
    } else {
        ageError.style.display = 'none';
    }

    // Validate Vaccination Status
    const vaccinationValue = vaccinationInput.value.trim(); // Trim leading/trailing spaces
    const vaccinationRegex = /^[A-Za-z\s]+$/; // Only letters and spaces
    if (!vaccinationRegex.test(vaccinationValue) || invalidWords.some(word => vaccinationValue.toLowerCase().includes(word))) {
        vaccinationError.style.display = 'block';
        vaccinationError.textContent = 'Please enter a valid vaccination status (only letters and spaces, no invalid words).';
        isValid = false;
    } else {
        vaccinationError.style.display = 'none';
    }

    // Validate Problem Description
    const problemValue = problemInput.value.trim(); // Trim leading/trailing spaces
    const problemRegex = /^[A-Za-z0-9\s.,!?]+$/; // Allow letters, numbers, spaces, and some punctuation
    if (problemValue.startsWith(' ') || repeatedCharRegex.test(problemValue) || invalidWords.some(word => problemValue.toLowerCase().includes(word))) {
        problemError.style.display = 'block';
        problemError.textContent = 'Please enter a valid problem description (no leading spaces, no repeated characters, and no invalid words).';
        isValid = false;
    } else {
        problemError.style.display = 'none';
    }

    return isValid; // Return the overall validity
}

document.addEventListener('DOMContentLoaded', function() {
    const breedInput = document.getElementById('breed_name');
    const ageInput = document.getElementById('age');
    const problemInput = document.getElementById('problem');

    breedInput.addEventListener('input', function() {
        const breedError = document.getElementById('breed_error');
        breedInput.value = breedInput.value.trim(); // Automatically trim leading spaces
        if (breedInput.value.startsWith(' ')) {
            breedError.style.display = 'block';
            breedError.textContent = 'Leading spaces are not allowed in breed name.';
        } else {
            breedError.style.display = 'none';
        }
    });

    ageInput.addEventListener('input', function() {
        const ageError = document.getElementById('age_error');
        ageInput.value = ageInput.value.trim(); // Automatically trim leading spaces
        if (ageInput.value.startsWith(' ')) {
            ageError.style.display = 'block';
            ageError.textContent = 'Leading spaces are not allowed in age.';
        } else {
            ageError.style.display = 'none';
        }
    });

    problemInput.addEventListener('input', function() {
        const problemError = document.getElementById('problem_error');
        problemInput.value = problemInput.value.trim(); // Automatically trim leading spaces
        if (problemInput.value.startsWith(' ')) {
            problemError.style.display = 'block';
            problemError.textContent = 'Leading spaces are not allowed in problem description.';
        } else {
            problemError.style.display = 'none';
        }
    });
});

</script>

</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="doctor-info">
                <i class="fas fa-user-md"></i>
                <span>Chat with Doctor</span>
            </div>
        </div>
        
        <div class="chat-messages">
            <div class="system-message">
                Please provide your pet's details to start the consultation
            </div>
            
            <form method="POST" action="" onsubmit="return validateForm();" class="chat-form">
                <div class="message-bubble incoming">
                    <label for="doctor_id">Select your doctor:</label>
                    <select name="did" id="doctor_id" required>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo htmlspecialchars($doctor['lid']); ?>">
                                Dr. <?php echo htmlspecialchars($doctor['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="message-bubble incoming">
                    <label for="breed_name">What's your pet's breed?</label>
                    <input type="text" name="breed_name" id="breed_name" required placeholder="Enter breed name">
                    <div id="breed_error" class="error-message"></div>
                </div>

                <div class="message-bubble incoming">
                    <label for="age">How old is your pet?</label>
                    <input type="text" name="age" id="age" required placeholder="Enter age">
                    <div id="age_error" class="error-message"></div>
                </div>

                <div class="message-bubble incoming">
                    <label for="vaccination_status">Vaccination Status</label>
                    <input type="text" name="vaccination_status" id="vaccination_status" required placeholder="Enter vaccination status">
                    <div id="vaccination_error" class="error-message"></div>
                </div>

                <div class="message-bubble incoming">
                    <label for="problem">What seems to be the problem?</label>
                    <textarea name="problem" id="problem" rows="4" required placeholder="Describe the problem"></textarea>
                    <div id="problem_error" class="error-message"></div>
                </div>

                <div class="chat-input-area">
                    <button type="submit" class="send-button">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>