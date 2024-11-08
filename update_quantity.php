<?php
session_start();
require('connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$pet_id = isset($_POST['pet_id']) ? intval($_POST['pet_id']) : 0;
$new_quantity = isset($_POST['new_quantity']) ? intval($_POST['new_quantity']) : 0;

if ($new_quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
    exit;
}

try {
    if ($product_id) {
        $stmt = $con->prepare("UPDATE product_variants SET quantity = ? WHERE product_id = ?");
        $stmt->bind_param("ii", $new_quantity, $product_id);
    } else if ($pet_id) {
        $stmt = $con->prepare("UPDATE productpet SET quantity = ? WHERE petid = ?");
        $stmt->bind_param("ii", $new_quantity, $pet_id);
    } else {
        throw new Exception("No valid product or pet ID provided");
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Failed to update quantity");
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$con->close();
?>