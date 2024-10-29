<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the category ID from the URL
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

// Validate category ID
if ($cid <= 0) {
    die("Invalid category ID.");
}

// Start a transaction
mysqli_begin_transaction($con);

try {
    // Delete subcategories first
    $deleteSubcategoriesQuery = "DELETE FROM subcategory WHERE cid = $cid";
    if (!mysqli_query($con, $deleteSubcategoriesQuery)) {
        throw new Exception("Error deleting subcategories: " . mysqli_error($con));
    }

    // Delete category
    $deleteCategoryQuery = "DELETE FROM category WHERE cid = $cid";
    if (!mysqli_query($con, $deleteCategoryQuery)) {
        throw new Exception("Error deleting category: " . mysqli_error($con));
    }

    // Commit transaction
    mysqli_commit($con);

    // Redirect to view categories page
    header('Location: viewcategory.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($con);

    // Print error message
    echo "Error: " . $e->getMessage();
}

// Close the database connection
mysqli_close($con);
?>
