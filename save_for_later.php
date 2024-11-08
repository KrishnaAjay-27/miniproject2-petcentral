<?php
session_start();
require('connection.php');
header('Content-Type: application/json');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userid = $_SESSION['uid'];
$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$type = isset($_POST['type']) ? mysqli_real_escape_string($con, $_POST['type']) : '';
$product_id = 0;
$petid = 0;
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

// Determine whether it's a product or pet
if ($type === 'dog') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
} elseif ($type === 'pet') {
    $petid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
}

// Validate inputs
if (!$cart_id || !$type || (!$product_id && !$petid) || !$price || !$quantity) {
    echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
    exit;
}

// Check if already saved
$check_query = "SELECT * FROM tbl_save_later WHERE (product_id='$product_id' OR petid='$petid') AND lid='$userid' AND type='$type'";
$check_result = mysqli_query($con, $check_query);

if (!$check_result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
    exit;
}

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Item already saved']);
    exit;
}

// Save the item
$sql = "INSERT INTO tbl_save_later (cart_id, product_id, petid, lid, price, quantity, type, PostedDate) 
        VALUES ('$cart_id', '$product_id', '$petid', '$userid', '$price', '$quantity', '$type', NOW())";
$result = mysqli_query($con, $sql);

if ($result) {
    // Remove from cart
    $delete_sql = "DELETE FROM tbl_cart WHERE cart_id='$cart_id' AND lid='$userid'";
    $delete_result = mysqli_query($con, $delete_sql);
    
    if ($delete_result) {
        echo json_encode(['success' => true, 'message' => 'Item saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from cart: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save item: ' . mysqli_error($con)]);
}

mysqli_close($con);
?>