<?php
session_start();

require_once 'vendor/autoload.php';
require_once 'connection.php'; // Make sure this file contains your database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$client = new Google_Client(['client_id' => '151430511839-rm5ljn03n9qpf98nsh9od7q1h0vc319l.apps.googleusercontent.com']);

if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
    
    try {
        $payload = $client->verifyIdToken($id_token);
        if ($payload) {
            $email = $payload['email'];
            
            // Check if the email exists in the login table
            $query = "SELECT * FROM login WHERE email = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists, set up the session
                $user = $result->fetch_assoc();
                $_SESSION['uid'] = $user['lid'];
                $_SESSION['u_type'] = $user['u_type'];
                
                // Log the user type for debugging
                error_log("User type: " . $user['u_type']);
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Successfully signed in: " . $email, 
                    'u_type' => $user['u_type'],
                    'debug_info' => [
                        'user_id' => $user['lid'],
                        'user_type' => $user['u_type'],
                        'email' => $email
                    ]
                ]);
            } else {
                // User doesn't exist
                echo json_encode(['success' => false, 'message' => "No account found with this email. Please register first."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Invalid ID token"]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Server error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "No credential provided"]);
}