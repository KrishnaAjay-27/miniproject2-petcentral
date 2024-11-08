<?php
require 'connection.php';

header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to log messages
function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'debug.log');
}

try {
    logMessage("Script started");

    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['predictions'])) {
        throw new Exception("Invalid request or no predictions provided");
    }

    $predictions = json_decode($_POST['predictions'], true);
    
    logMessage("Received predictions: " . print_r($predictions, true));

    // Database connection check
    if (!$con) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Get all product names from the database
    $query = "SELECT product_name FROM productpet WHERE status = 1";
    $result = mysqli_query($con, $query);

    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($con));
    }

    $dbBreeds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dbBreeds[] = strtolower($row['product_name']);
    }

    // Compare predictions with database breeds
    $bestMatch = null;
    $highestSimilarity = 0;

    foreach ($predictions as $prediction) {
        $predictedBreed = strtolower($prediction['className']);
        foreach ($dbBreeds as $dbBreed) {
            $similarity = similar_text($predictedBreed, $dbBreed, $percent);
            if ($percent > $highestSimilarity) {
                $highestSimilarity = $percent;
                $bestMatch = $dbBreed;
            }
        }
    }

    if ($bestMatch && $highestSimilarity > 70) { // Adjust threshold as needed
        logMessage("Match found: " . $bestMatch . " (Similarity: " . $highestSimilarity . "%)");
        echo json_encode(['product_name' => ucfirst($bestMatch)]);
    } else {
        logMessage("No match found. Highest similarity: " . $highestSimilarity . "%");
        echo json_encode(['product_name' => null, 'message' => 'No match found']);
    }

} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

if (isset($con) && $con) {
    mysqli_close($con);
}

logMessage("Script ended");
?>