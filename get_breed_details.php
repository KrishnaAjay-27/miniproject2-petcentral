<?php
require 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['breed'])) {
    $breed = mysqli_real_escape_string($con, $_POST['breed']);
    
    $query = "SELECT Age, color, lifespan FROM productpet WHERE product_name LIKE '%$breed%' LIMIT 1";
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'age' => $row['Age'],
            'colors' => $row['color'],
            'lifespan' => $row['lifespan'] // Add this line
        ]);
    } else {
        echo json_encode([
            'age' => 'Not found',
            'colors' => 'Not found',
            'lifespan' => 'Not found' // Add this line
        ]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}

mysqli_close($con);
?>