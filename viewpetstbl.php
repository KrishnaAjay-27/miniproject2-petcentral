<?php
session_start();

require('connection.php');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the supplier ID based on user ID
$uid = $_SESSION['uid'];
$sidQuery = "SELECT s.*, l.email FROM s_registration s JOIN login l ON s.lid = l.lid WHERE s.lid = '$uid'";
$sidResult = mysqli_query($con, $sidQuery);
if ($sidResult && mysqli_num_rows($sidResult) > 0) {
    $sidRow = mysqli_fetch_assoc($sidResult);
    $supplier_id = intval($sidRow['sid']);
    $suppliers = $sidRow['name']; // For sidebar display
} else {
    die("Supplier ID (sid) not found for the logged-in user.");
}

// Handle activation/deactivation
if (isset($_POST['toggle_status']) && isset($_POST['product_id']) && isset($_POST['current_status'])) {
    $product_id = intval($_POST['product_id']);
    $new_status = ($_POST['current_status'] == 0) ? 1 : 0;
    $updateQuery = "UPDATE productpet SET status = $new_status WHERE petid = $product_id AND sid = $supplier_id";
    if (mysqli_query($con, $updateQuery)) {
        echo "<script>alert('Pet status updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating pet status: " . mysqli_error($con) . "');</script>";
    }
}

// Fetch products for this supplier
$productsQuery = "SELECT pd.*, c.name as category_name, sc.name as subcategory_name, pd.Gender, pd.Age
                  FROM productpet pd
                  JOIN category c ON pd.cid = c.cid
                  JOIN subcategory sc ON pd.subid = sc.subid
                  WHERE pd.sid = $supplier_id
                  ORDER BY pd.petid DESC";
$productsResult = mysqli_query($con, $productsQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f4f7fe;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: #003366;
            padding: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 10px;
        }

        .sidebar-header h1 {
            color: white;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header h2 {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            font-weight: normal;
        }

        .nav-links {
            padding: 10px 0;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            font-size: 15px;
            transition: 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a i {
            width: 24px;
            font-size: 16px;
            margin-right: 10px;
        }

        /* Header Styles */
        .top-header {
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - 260px);
            background: white;
            color: #333;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-text h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 2px;
        }

        .welcome-text p {
            color: #666;
            font-size: 14px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .date-time {
            background: #f5f5f5;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }

        .logout-btn {
            background: #cc0000;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: #b30000;
        }

        /* Content Styles */
        .content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 20px;
            width: calc(100% - 260px);
            background: #f4f7fe;
            min-height: calc(100vh - 70px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 25px 30px;
        }

        .section-title {
            font-size: 20px;
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* DataTables-inspired styling */
        .dataTables_wrapper {
            position: relative;
            clear: both;
            padding-top: 20px;
        }
        
        .dataTables_length {
            float: left;
            margin-bottom: 15px;
        }
        
        .dataTables_filter {
            float: right;
            margin-bottom: 20px;
            text-align: right;
        }
        
        .dataTables_info {
            clear: both;
            float: left;
            margin-top: 15px;
        }
        
        .dataTables_paginate {
            float: right;
            margin-top: 15px;
        }
        
        .paginate_button {
            box-sizing: border-box;
            display: inline-block;
            min-width: 1.5em;
            padding: 0.5em 1em;
            margin-left: 2px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            color: #333;
            border: 1px solid transparent;
            border-radius: 2px;
        }
        
        .paginate_button.current {
            background: #003366;
            color: white;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-top: 50px;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: #003366;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
            vertical-align: middle;
            border: none;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background-color: #f1f4f8;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #e9ecef;
        }
        
        .table img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .table img:hover {
            transform: scale(1.1);
        }
        
        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 30px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            margin-right: 5px;
        }
        
        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-activate {
            background-color: #28a745;
            color: white;
        }
        
        .btn-deactivate {
            background-color: #dc3545;
            color: white;
        }
        
        .description-container {
            max-width: 250px;
        }
        
        .description-short {
            display: inline;
        }
        
        .description-full {
            display: none;
        }
        
        .view-more {
            display: inline-block;
            color: #007bff;
            cursor: pointer;
            font-size: 12px;
            margin-left: 5px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: none;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
        }

        .dataTables_filter input {
            margin-left: 8px;
        }

        .dataTables_length select {
            margin: 0 8px;
        }

        @media (max-width: 768px) {
            .sidebar, .top-header {
                display: none;
            }
            .content {
                width: 100%;
                margin-left: 0;
                margin-top: 20px;
            }
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-header {
            border-bottom: 1px solid #edf2f9;
            background: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .table-responsive {
            margin: 0;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .row {
            margin: 0;
            padding: 15px;
        }

        .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px 10px;
            margin-left: 5px;
        }

        .dataTables_length select {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px 10px;
            margin: 0 5px;
        }

        table.dataTable {
            width: 100% !important;
            margin: 0 !important;
            border-collapse: collapse !important;
        }

        table.dataTable thead th {
            background: #f8f9fa;
            color: #344767;
            font-weight: 600;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }

        table.dataTable tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>Welcome</h1>
            <h2><?php echo htmlspecialchars($suppliers); ?></h2>
        </div>
        <div class="nav-links">
            <a href="supplierindex.php">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="view_profilesupplier.php">
                <i class="fas fa-user"></i>My Profile
            </a>
            <a href="addproductdog.php">
                <i class="fas fa-dog"></i>Manage Dogs
            </a>
            <a href="addproductpets.php">
                <i class="fas fa-paw"></i>Manage Pets
            </a>
            <a href="viewproduct.php">
                <i class="fas fa-list"></i>View Products
            </a>
            <a href="viewpetstbl.php" class="active">
                <i class="fas fa-table"></i>View Pets
            </a>
            <a href="view_orders.php">
                <i class="fas fa-shopping-cart"></i>Order History
            </a>
            <a href="payment_orders.php">
                <i class="fas fa-money-bill"></i>Payment History
            </a>
            <a href="edit_profilesupplier.php">
                <i class="fas fa-edit"></i>Edit Profile
            </a>
            <a href="supplierpassword.php">
                <i class="fas fa-key"></i>Change Password
            </a>
        </div>
    </div>

    <div class="top-header">
        <div class="welcome-text">
            <h1>Welcome Back, <?php echo htmlspecialchars($suppliers); ?>!</h1>
            <p>Manage your pet listings and inventory.</p>
        </div>
        <div class="header-right">
            <div class="date-time">
                <i class="fas fa-calendar"></i>
                <span id="currentDateTime"></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid px-4">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-paw me-2"></i>Your Pets</h5>
                        <a href="addproductpets.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Add New Pet
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="petsTable" class="table table-hover display nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Description</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sno = 1;
                            while ($product = mysqli_fetch_assoc($productsResult)): 
                                $shortDescription = substr($product['description'], 0, 50);
                                $fullDescription = $product['description'];
                            ?>
                                <tr>
                                    <td><?php echo $sno++; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td>
                                        <img src="uploads/<?php echo $product['image1']; ?>" 
                                             alt="Pet Image" 
                                             class="rounded shadow-sm" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['subcategory_name']); ?></td>
                                    <td>
                                        <div class="description-wrapper">
                                            <span class="description-short"><?php echo htmlspecialchars($shortDescription); ?></span>
                                            <?php if (strlen($product['description']) > 50): ?>
                                                <span class="description-full" style="display: none;">
                                                    <?php echo htmlspecialchars($product['description']); ?>
                                                </span>
                                                <a href="#" class="read-more text-primary">Read More</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['Age']); ?></td>
                                    <td><?php echo htmlspecialchars($product['Gender']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo $product['status'] == 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $product['status'] == 0 ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $product['petid']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $product['status']; ?>">
                                                <button type="submit" 
                                                        name="toggle_status" 
                                                        class="btn btn-sm <?php echo $product['status'] == 0 ? 'btn-outline-danger' : 'btn-outline-success'; ?>"
                                                        data-bs-toggle="tooltip" 
                                                        title="<?php echo $product['status'] == 0 ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas <?php echo $product['status'] == 0 ? 'fa-toggle-off' : 'fa-toggle-on'; ?>"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString();
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // View More functionality
        document.querySelectorAll('.view-more').forEach(button => {
            button.addEventListener('click', function() {
                const shortDesc = this.previousElementSibling.previousElementSibling;
                const fullDesc = this.previousElementSibling;
                
                if (fullDesc.style.display === 'none') {
                    fullDesc.style.display = 'inline';
                    shortDesc.style.display = 'none';
                    this.textContent = 'View Less';
                } else {
                    fullDesc.style.display = 'none';
                    shortDesc.style.display = 'inline';
                    this.textContent = 'View More';
                }
            });
        });

        function openEditModal(petId) {
            // Fetch pet details using AJAX
            $.ajax({
                url: 'get_pet_details.php',
                type: 'POST',
                data: { petid: petId },
                success: function(response) {
                    const pet = JSON.parse(response);
                    
                    // Populate the form fields
                    $('#edit_petid').val(pet.petid);
                    $('#edit_name').val(pet.product_name);
                    $('#edit_price').val(pet.price);
                    $('#edit_age').val(pet.Age);
                    $('#edit_gender').val(pet.Gender);
                    $('#edit_description').val(pet.description);
                    
                    // Load categories and set selected
                    loadCategories(pet.cid);
                    
                    // Display current images
                    $('#current_image1').attr('src', 'uploads/' + pet.image1);
                    $('#current_image2').attr('src', 'uploads/' + pet.image2);
                    $('#current_image3').attr('src', 'uploads/' + pet.image3);
                    
                    // Show the modal using Bootstrap's modal
                    var myModal = new bootstrap.Modal(document.getElementById('editPetModal'));
                    myModal.show();
                },
                error: function(xhr, status, error) {
                    alert('Error fetching pet details: ' + error);
                }
            });
        }
    </script>
</body>
</html>
