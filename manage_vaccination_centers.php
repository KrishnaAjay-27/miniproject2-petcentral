<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Handle activation/deactivation
if(isset($_POST['toggle_status'])) {
    $center_id = mysqli_real_escape_string($con, $_POST['center_id']);
    $current_status = isset($_POST['current_status']) ? (int)$_POST['current_status'] : 0;
    $new_status = ($current_status == 1) ? 0 : 1;
    
    $update_query = "UPDATE vaccination_centers SET status = $new_status WHERE center_id = '$center_id'";
    if(mysqli_query($con, $update_query)) {
        echo "<script>alert('Status updated successfully!'); window.location.href='manage_vaccination_centers.php';</script>";
    } else {
        echo "<script>alert('Error updating status: " . mysqli_error($con) . "');</script>";
    }
}

// Fetch all vaccination centers
$query = "SELECT vc.*, l.email 
          FROM vaccination_centers vc 
          JOIN login l ON vc.lid = l.lid 
          ORDER BY vc.status ASC, vc.center_name ASC";
$result = mysqli_query($con, $query);

// Fetch admin email for sidebar
$uid = $_SESSION['uid'];
$adminQuery = "SELECT email FROM login WHERE lid='$uid'";
$adminResult = mysqli_query($con, $adminQuery);
$admin = mysqli_fetch_assoc($adminResult);
$adminEmail = $admin ? $admin['email'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vaccination Centers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Copy your existing dashboard CSS here */
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
        
        
        /* ... copy all the CSS from your dashboard ... */

        /* Additional styles for vaccination centers */
        .table thead th {
        background-color: #343a40;
        color: #fff;
        border-color: #454d55;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(52, 58, 64, 0.075);
    }

    .center-table {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
        border: none;
    }

    .center-table .card-header {
        border-radius: 8px 8px 0 0;
        border: none;
    }

    .table {
        margin-bottom: 0;
    }
    .table td, .table th {
        vertical-align: middle;
        padding: 0.75rem;
    }
         /* Status badge styles */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #ffc107;
        color: #000;
    }

    .status-active {
        background-color: #28a745;
        color: #fff;
    }

    /* Action button styles */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .action-buttons .btn {
        margin: 0 2px;
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
        <a href="manage_vaccination_centers.php" class="active"><i class="fas fa-syringe"></i> <span>Vaccination Centers</span></a>
        <a href="addcategory.php"><i class="fas fa-list"></i> <span>Manage Categories</span></a>
        <a href="addsubcategory.php"><i class="fas fa-list-ul"></i> <span>Manage Subcategory</span></a>
        <a href="viewcategory.php"><i class="fas fa-eye"></i> <span>View Categories</span></a>
        <a href="viewsubcategory.php"><i class="fas fa-eye"></i> <span>View Subcategories</span></a>
        <a href="addsuppliers.php"><i class="fas fa-store"></i> <span>Add Suppliers</span></a>
        <a href="adddoctors.php"><i class="fas fa-user-md"></i> <span>Add Doctors</span></a>
        <a href="adddeliveryboy.php"><i class="fas fa-motorcycle"></i> <span>Add Delivery Boy</span></a>
        <a href="managesupplieadmin.php"><i class="fas fa-cog"></i> <span>Manage Suppliers</span></a>
        <a href="managedeliveryboy.php"><i class="fas fa-truck"></i> <span>Manage Deliveryboy</span></a>
        <a href="fetch_products.php"><i class="fas fa-box"></i> <span>View Products</span></a>
        <a href="order_history.php"><i class="fas fa-history"></i> <span>Order History</span></a>
        <a href="admin_chat.php"><i class="fas fa-comments"></i> <span>Chat with Delivery Boys</span></a>
    </div>

    <div class="main-content">
        <div class="header">
            <a href="admindashboard.php" class="logo">Manage Vaccination Centers</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                <div class="card center-table">
    <div class="card-header text-white" style="background-color: #343a40;">
        <h5 class="mb-0"><i class="fas fa-syringe mr-2"></i>Vaccination Centers</h5>
    </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Center Name</th>
                                            <th>Head Name</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>License No.</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($center = mysqli_fetch_assoc($result)) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                                                <td><?php echo htmlspecialchars($center['head_name']); ?></td>
                                                <td><?php echo htmlspecialchars($center['contact_number']); ?></td>
                                                <td><?php echo htmlspecialchars($center['email']); ?></td>
                                                <td><?php echo htmlspecialchars($center['license_number']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $center['status'] == 1 ? 'status-active' : 'status-pending'; ?>">
                                                        <?php echo $center['status'] == 1 ? 'Active' : 'Pending'; ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="center_id" value="<?php echo $center['center_id']; ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo $center['status']; ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $center['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>">
                                                            <?php echo $center['status'] == 1 ? 'Deactivate' : 'Activate'; ?>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="viewDetails(<?php echo $center['center_id']; ?>)">
                                                        View Details
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing details -->
    <div class="modal fade" id="centerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Center Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="centerDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    function viewDetails(centerId) {
        $.ajax({
            url: 'get_center_details.php',
            type: 'POST',
            data: { center_id: centerId },
            success: function(response) {
                $('#centerDetailsContent').html(response);
                $('#centerDetailsModal').modal('show');
            }
        });
    }
    </script>
</body>
</html>