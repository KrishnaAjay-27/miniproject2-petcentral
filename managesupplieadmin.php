<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid']) || $_SESSION['u_type'] != 0) {
    header('Location: login.php');
    exit();
}

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $admin = mysqli_fetch_assoc($result);
    $adminEmail = $admin ? $admin['email'] : 'Admin';
} else {
    $adminEmail = 'Admin'; // Default email in case of query failure
}

// Handle activate/deactivate requests
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'activate') {
        $status = 0; // Set status to active
    } elseif ($action == 'deactivate') {
        $status = 1; // Set status to inactive
    } else {
        die("Invalid action");
    }

    // Prepare and execute the update query
    $updateQuery = "UPDATE login SET status=? WHERE lid=?";
    $stmt = mysqli_prepare($con, $updateQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $status, $userId);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: managesupplieadmin.php'); // Redirect to reflect changes
            exit();
        } else {
            echo "Update failed: " . mysqli_stmt_error($stmt); // Debugging output
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare failed: " . mysqli_error($con); // Debugging output
    }
}

// Fetch user details from the 's_registration' table
$userQuery = "SELECT r.name, r.email, r.phone, r.address, r.supplier_code, l.status, l.lid
              FROM s_registration r
              JOIN login l ON r.lid = l.lid";
$userResult = mysqli_query($con, $userQuery);

if (!$userResult) {
    die("Query failed: " . mysqli_error($con));
}

// Close the database connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers</title>
    
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Add DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            overflow-y: auto;
            z-index: 100;
            transition: all 0.3s;
        }
        
        .sidebar .admin-info {
            padding: 15px;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 15px;
        }
        
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #ced4da;
            text-decoration: none;
            transition: 0.3s;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }
        
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left-color: #17a2b8;
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        /* Main content area */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* Header styles */
        .header {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 25px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header .logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
            text-decoration: none;
        }
        
        .header .logout-btn {
            padding: 8px 16px;
            background-color: #dc3545;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .header .logout-btn:hover {
            background-color: #c82333;
            text-decoration: none;
            color: white;
        }
        
        /* Table styling */
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-top: 20px;
        }
        
        .table-title {
            margin-bottom: 20px;
            color: #343a40;
            font-weight: 600;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            border: none;
            padding: 12px 15px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.03);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-activate {
            background-color: #28a745;
            color: white;
        }
        
        .btn-activate:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }
        
        .btn-deactivate {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-deactivate:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        
        /* DataTables custom styling */
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-bottom: 20px;
        }
        
        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        div.dataTables_wrapper div.dataTables_info {
            padding-top: 15px;
            font-size: 0.9rem;
        }
        
        div.dataTables_wrapper div.dataTables_paginate {
            padding-top: 15px;
        }
        
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            margin: 2px 0;
            white-space: nowrap;
            justify-content: flex-end;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                overflow-x: hidden;
            }
            
            .sidebar a span {
                display: none;
            }
            
            .sidebar a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .sidebar .admin-info {
                text-align: center;
                padding: 10px 5px;
            }
            
            .sidebar .admin-info p {
                display: none;
            }
            
            .main-content {
                margin-left: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-info">
            <p class="mb-0">Welcome,</p>
            <h6 class="font-weight-bold"><?php echo htmlspecialchars($adminEmail); ?></h6>
        </div>
        <a href="admindashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        <a href="manageuseradmin.php"><i class="fas fa-users"></i> <span>Manage Users</span></a>
        <a href="addcategory.php"><i class="fas fa-list"></i> <span>Manage Categories</span></a>
        <a href="addsubcategory.php"><i class="fas fa-list-ul"></i> <span>Manage Subcategory</span></a>
        <a href="viewcategory.php"><i class="fas fa-eye"></i> <span>View Categories</span></a>
        <a href="viewsubcategory.php"><i class="fas fa-eye"></i> <span>View Subcategories</span></a>
        <a href="addsuppliers.php"><i class="fas fa-store"></i> <span>Add Suppliers</span></a>
        <a href="adddoctors.php"><i class="fas fa-user-md"></i> <span>Add Doctors</span></a>
        <a href="adddeliveryboy.php"><i class="fas fa-motorcycle"></i> <span>Add Delivery Boy</span></a>
        <a href="managesupplieadmin.php" class="active"><i class="fas fa-cog"></i> <span>Manage Suppliers</span></a>
        <a href="managedeliveryboy.php"><i class="fas fa-truck"></i> <span>Manage Deliveryboy</span></a>
        <a href="fetch_products.php"><i class="fas fa-box"></i> <span>View Products</span></a>
        <a href="order_history.php"><i class="fas fa-history"></i> <span>Order History</span></a>
        <a href="admin_chat.php"><i class="fas fa-comments"></i> <span>Chat with Delivery Boys</span></a>
    </div>

    <div class="main-content">
        <div class="header">
            <a href="admindashboard.php" class="logo">Admin Dashboard</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
        </div>
        
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="mb-0">Manage Suppliers</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 bg-transparent p-0">
                                <li class="breadcrumb-item"><a href="admindashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Manage Suppliers</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <h5 class="table-title"><i class="fas fa-store mr-2"></i>Registered Suppliers</h5>
                
                <table id="suppliersTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                            <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                            <th>Supplier ID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($userResult) > 0) {
                    $serialNo = 1; // Initialize serial number
                    while ($row = mysqli_fetch_assoc($userResult)) {
                        $userName = htmlspecialchars($row['name']);
                        $userEmail = htmlspecialchars($row['email']);
                                $userPhone = htmlspecialchars($row['phone'] ?? 'N/A');
                                $userAddress = htmlspecialchars($row['address'] ?? 'N/A');
                        $usersupply = htmlspecialchars($row['supplier_code']);
                                $status = intval($row['status']);
                                $userId = intval($row['lid']); // Used for activation/deactivation

                                $statusClass = $status == 0 ? 'status-active' : 'status-inactive';
                        $statusText = $status == 0 ? 'Active' : 'Inactive';
                        $statusAction = $status == 0 ? 'deactivate' : 'activate';
                                $btnClass = $status == 0 ? 'btn-deactivate' : 'btn-activate';
                        $btnText = $status == 0 ? 'Deactivate' : 'Activate';
                                $btnIcon = $status == 0 ? 'fa-ban' : 'fa-check';

                        echo "<tr>
                                        <td>$serialNo</td>
                                <td>$userName</td>
                                <td>$userEmail</td>
                                <td>$userPhone</td>
                                <td>$userAddress</td>
                                <td>$usersupply</td>
                                        <td><span class='status-badge $statusClass'>$statusText</span></td>
                                        <td>
                                            <a href='managesupplieadmin.php?action=$statusAction&id=$userId' class='btn-action $btnClass'>
                                                <i class='fas $btnIcon mr-1'></i> $btnText
                                            </a>
                                        </td>
                              </tr>";

                        $serialNo++; // Increment serial number
                    }
                } else {
                            echo "<tr><td colspan='8' class='text-center'>No suppliers found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
        </div>
    </div>
    
    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Add DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#suppliersTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": {
                    "search": "Search suppliers:",
                    "lengthMenu": "Show _MENU_ suppliers per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ suppliers",
                    "infoEmpty": "No suppliers available",
                    "infoFiltered": "(filtered from _MAX_ total suppliers)"
                }
            });
        });
    </script>
</body>
</html>
