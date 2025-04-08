<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the admin's email from the 'login' table
$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $admin = mysqli_fetch_assoc($result);
    $adminEmail = $admin ? $admin['email'] : 'Admin';
} else {
    $adminEmail = 'Admin'; // Default email in case of query failure
}

// // Fetch the count of registered users from the 'registration' table
// $userCountQuery = "SELECT COUNT(*) AS count FROM registration";
// $userCountResult = mysqli_query($con, $userCountQuery);
// $userCount = $userCountResult ? mysqli_fetch_assoc($userCountResult)['count'] : 0;
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Handle order assignment when Assign button is clicked
if (isset($_POST['assign']) && isset($_POST['deid']) && isset($_POST['order_id'])) {
    $deid = intval($_POST['deid']);
    $orderId = intval($_POST['order_id']);
    
    // Get delivery boy's details
    $deliveryBoyQuery = "SELECT * FROM deliveryboy WHERE deid = $deid";
    $deliveryBoyResult = mysqli_query($con, $deliveryBoyQuery);
    
    if ($deliveryBoyResult && mysqli_num_rows($deliveryBoyResult) > 0) {
        $deliveryBoy = mysqli_fetch_assoc($deliveryBoyResult);
        $lid = $deliveryBoy['lid'];
        
        // Count how many orders were assigned today to this delivery boy (by lid)
        $todayOrdersCountQuery = "
            SELECT COUNT(*) as today_count 
            FROM deliveryboy 
            WHERE lid = '$lid' 
            AND DATE(assign_date) = CURDATE()
        ";
        $todayOrdersCountResult = mysqli_query($con, $todayOrdersCountQuery);
        $todayOrdersCount = mysqli_fetch_assoc($todayOrdersCountResult)['today_count'];
        
        if ($todayOrdersCount >= 10) {
            echo json_encode(['success' => false, 'message' => 'This delivery boy has already been assigned 10 orders today. Daily limit reached.']);
            exit;
        }
        
        // Check if order exists and is ready for assignment
        $orderQuery = "SELECT * FROM order_details WHERE detail_id = $orderId AND order_status = 1";
        $orderResult = mysqli_query($con, $orderQuery);
        
        if ($orderResult && mysqli_num_rows($orderResult) > 0) {
            $order = mysqli_fetch_assoc($orderResult);
                
                mysqli_begin_transaction($con);
                try {
                // Create a new record in the deliveryboy table
                $insertQuery = "
                        INSERT INTO deliveryboy (
                            name, 
                            email, 
                            phone, 
                            pincode, 
                            status, 
                            detail_id, 
                            lid, 
                            u_type, 
                            doctor_code,
                            assign_date
                    ) VALUES (
                        '{$deliveryBoy['name']}',
                        '{$deliveryBoy['email']}',
                        '{$deliveryBoy['phone']}',
                        '{$deliveryBoy['pincode']}',
                        1, -- Set as assigned
                        $orderId,
                        $lid,
                        '{$deliveryBoy['u_type']}',
                        '{$deliveryBoy['doctor_code']}',
                        NOW()
                    )
                ";
                
                if (!mysqli_query($con, $insertQuery)) {
                    throw new Exception("Error creating delivery record: " . mysqli_error($con));
                }
                
                // Get the new delivery boy record ID
                    $newDeid = mysqli_insert_id($con);
                    
                // Update the order_details table
                    $updateOrderQuery = "
                        UPDATE order_details 
                    SET deid = $newDeid, 
                        order_status = 2 
                    WHERE detail_id = $orderId
                    ";
                    
                    if (!mysqli_query($con, $updateOrderQuery)) {
                        throw new Exception("Error updating order details: " . mysqli_error($con));
                    }

                    // Insert notification for the delivery boy
                $notificationMessage = "You have been assigned to Order #$orderId.";
                    $insertNotificationQuery = "
                    INSERT INTO notification (deid, lid, message, created_at) 
                    VALUES ($newDeid, $lid, '$notificationMessage', NOW())
                    ";
                    
                    if (!mysqli_query($con, $insertNotificationQuery)) {
                        throw new Exception("Error inserting notification: " . mysqli_error($con));
                    }

                    mysqli_commit($con);
                echo json_encode(['success' => true, 'message' => "Order #$orderId has been assigned successfully!"]);
                exit;
                
                } catch (Exception $e) {
                    mysqli_rollback($con);
                echo json_encode(['success' => false, 'message' => "Error assigning order: " . $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Order not found or already assigned.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Delivery boy not found.']);
        exit;
    }
}

// Fetch unique delivery boys (grouped by lid)
$uniqueDeliveryBoysQuery = "
    SELECT d.*, 
           (SELECT status FROM deliveryboy WHERE lid = d.lid ORDER BY assign_date DESC LIMIT 1) as latest_status
    FROM deliveryboy d 
    WHERE d.deid IN (
        SELECT MAX(deid) 
        FROM deliveryboy 
        GROUP BY lid
    )
    ORDER BY d.deid DESC
";
$deliveryBoysResult = mysqli_query($con, $uniqueDeliveryBoysQuery);
$delivery_boys = mysqli_fetch_all($deliveryBoysResult, MYSQLI_ASSOC);

// Fetch details of a specific delivery boy if ID is provided
$deliveryBoyDetails = null;
if (isset($_GET['deid'])) {
    $deid = intval($_GET['deid']);
    $deliveryBoyQuery = "SELECT * FROM deliveryboy WHERE deid = $deid";
    $deliveryBoyResult = mysqli_query($con, $deliveryBoyQuery);
    $deliveryBoyDetails = mysqli_fetch_assoc($deliveryBoyResult);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Delivery Boys</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f4f4;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #2c3e50, #34495e);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            padding-top: 10px;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .admin-info {
            padding: 15px 10px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 15px;
        }
        .admin-info p {
            margin: 0;
            font-size: 14px;
            color: #cce0ff;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
            padding-left: 20px;
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
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #ffffff;
            color: #2c3e50;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            color: #2c3e50;
        }
        .header .logout-btn {
            background-color: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 5px 15px;
            border-radius: 4px;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        .header .logout-btn:hover {
            background-color: #dc3545;
            color: #fff;
        }
        .card {
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.05);
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            background: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h5 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.05);
            background: white;
        }
        .dataTables_wrapper {
            padding: 20px;
        }
        .table {
            margin-bottom: 0;
            width: 100%;
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 12px 15px;
        }
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            color: #495057;
            border-color: #f2f2f2;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(44, 62, 80, 0.02);
        }
        .badge {
            padding: 7px 12px;
            font-weight: 500;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-radius: 30px;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .daily-limit {
            font-size: 12px;
            margin-left: 5px;
            color: #6c757d;
        }
        .daily-limit.reached {
            color: #dc3545;
            font-weight: 600;
        }
        .modal-header {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            border-bottom: none;
        }
        .close {
            color: white;
            opacity: 1;
        }
        .close:hover {
            color: #f8f9fa;
        }
        .modal-content {
            border: none;
            border-radius: 8px;
            overflow: hidden;
        }
        .page-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .page-subtitle {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-info">
            <p>Welcome, <?php echo htmlspecialchars($adminEmail); ?></p>
        </div>
        <a href="admindashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manageuseradmin.php"><i class="fas fa-users"></i> Manage Users</a>
        <a href="addcategory.php"><i class="fas fa-list"></i> Manage Categories</a>
        <a href="addsubcategory.php"><i class="fas fa-th-list"></i> Manage Subcategory</a>
        <a href="viewcategory.php"><i class="fas fa-eye"></i> View Categories</a>
        <a href="viewsubcategory.php"><i class="fas fa-eye"></i> View Sub categories</a>
        <a href="addsuppliers.php"><i class="fas fa-truck"></i> Add Suppliers</a>
        <a href="adddoctors.php"><i class="fas fa-user-md"></i> Add Doctors</a>
        <a href="adddeliveryboy.php"><i class="fas fa-shipping-fast"></i> Add Delivery Boy</a>
        <a href="managesupplieadmin.php"><i class="fas fa-cogs"></i> Manage Suppliers</a>
        <a href="managedeliveryboy.php"><i class="fas fa-people-carry"></i> Manage Deliveryboy</a>
        <a href="fetch_products.php"><i class="fas fa-boxes"></i> View product</a>
        <a href="order_history.php"><i class="fas fa-history"></i> Order History</a>
        <a href="admin_chat.php"><i class="fas fa-comments"></i> <span>Chat with Delivery Boys</span></a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <a href="admindashboard.php" class="logo"><i class="fas fa-cog mr-2"></i>Admin Dashboard</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
        </div>

        <h1 class="page-title">Manage Delivery Boys</h1>
        <p class="page-subtitle">View, manage, and assign orders to delivery personnel</p>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-users mr-2"></i>Delivery Personnel</h5>
        </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table id="deliveryBoyTable" class="table table-hover display">
            <thead>
                <tr>
                                <th>Name</th>
                                <th>Contact Info</th>
                                <th>Location</th>
                    <th>Status</th>
                                <th>Daily Assignments</th>
                                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($delivery_boys as $db): ?>
                                <?php 
                                // Calculate today's orders count
                                $lid = $db['lid'];
                                $todayOrdersQuery = "
                                    SELECT COUNT(*) as day_count 
                                    FROM deliveryboy 
                                    WHERE lid = '$lid' AND detail_id IS NOT NULL AND DATE(assign_date) = CURDATE()
                                ";
                                $todayOrdersResult = mysqli_query($con, $todayOrdersQuery);
                                $today_count = mysqli_fetch_assoc($todayOrdersResult)['day_count'];
                                $limit_reached = $today_count >= 10;
                                
                                // Use the latest status for this delivery boy
                                $status = $db['latest_status'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($db['name']); ?></strong>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($db['email']); ?></div>
                                        <div><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($db['phone']); ?></div>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt mr-1"></i> 
                                        Pincode: <?php echo htmlspecialchars($db['pincode']); ?>
                        </td>
                        <td>
                            <?php 
                                        // Show as "Unavailable" if limit is reached, otherwise "Available"
                                        if ($limit_reached) {
                                            echo '<span class="badge badge-danger">Unavailable</span>';
                            } else {
                                echo '<span class="badge badge-info">Available</span>';
                            }
                            ?>
                        </td>
                        <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress mr-2" style="height: 8px; width: 60px;">
                                                <div class="progress-bar <?php echo $limit_reached ? 'bg-danger' : 'bg-success'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo ($today_count / 10) * 100; ?>%" 
                                                     aria-valuenow="<?php echo $today_count; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="10">
                                                </div>
                                            </div>
                                            <span class="badge <?php echo $limit_reached ? 'badge-danger' : 'badge-success'; ?>">
                                                <?php echo $today_count; ?>/10
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="?deid=<?php echo $db['deid']; ?>" class="btn btn-info btn-sm view-details-btn">
                                            <i class="fas fa-eye mr-1"></i> View Details
                                        </a>
                                        <?php if (!$limit_reached): ?>
                                            <button class="btn btn-success btn-sm assign-btn mt-1" 
                                                    data-deid="<?php echo $db['deid']; ?>"
                                                    data-pincode="<?php echo $db['pincode']; ?>">
                                                <i class="fas fa-truck mr-1"></i> Assign Order
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm mt-1" disabled>
                                                <i class="fas fa-ban mr-1"></i> Limit Reached
                                            </button>
                                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
            </div>
        </div>
    </div>

    <!-- Delivery Boy Details Modal -->
    <div class="modal fade" id="deliveryBoyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-id-card mr-2"></i>Delivery Boy Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="deliveryBoyDetails">
                    <!-- Content will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading delivery boy details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Assignment Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-clipboard-list mr-2"></i>Available Orders</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="availableOrders">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Loading available orders...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#deliveryBoyTable').DataTable({
                "language": {
                    "lengthMenu": "Show _MENU_ delivery boys",
                    "zeroRecords": "No delivery boys found",
                    "info": "Showing page _PAGE_ of _PAGES_",
                    "infoEmpty": "No delivery boys available",
                    "infoFiltered": "(filtered from _MAX_ total records)"
                },
                "order": [[3, "asc"]],  // Sort by status
                "columnDefs": [
                    { "orderable": false, "targets": 5 }  // Disable sorting on action column
                ],
                "pageLength": 10,
                "responsive": true
            });
            
            // Handle View Details button click
            $('.view-details-btn').click(function(e) {
                e.preventDefault();
                var deid = $(this).attr('href').split('=')[1];
                
                $('#deliveryBoyModal').modal('show');
                
                // AJAX request to fetch delivery boy details
                $.ajax({
                    url: 'get_delivery_boy_details.php',
                    type: 'GET',
                    data: { deid: deid },
                    success: function(response) {
                        $('#deliveryBoyDetails').html(response);
                    },
                    error: function() {
                        $('#deliveryBoyDetails').html('<div class="alert alert-danger">Error loading delivery boy details</div>');
                    }
                });
            });

            // Handle Assign Order button click
            $('.assign-btn').click(function() {
                var deid = $(this).data('deid');
                var pincode = $(this).data('pincode');
                
                $('#assignModal').modal('show');
                
                // Fetch available orders
                $.ajax({
                    url: 'get_available_orders.php',
                    type: 'GET',
                    data: {
                        deid: deid,
                        pincode: pincode
                    },
                    success: function(response) {
                        $('#availableOrders').html(response);
                    },
                    error: function() {
                        $('#availableOrders').html('<div class="alert alert-danger">Error loading orders</div>');
                    }
                });
            });
        });
        
        // Function to assign an order (will be called from get_available_orders.php)
        function assignOrder(deid, orderId) {
            if (confirm('Are you sure you want to assign this order?')) {
                $.ajax({
                    url: 'managedeliveryboy.php',
                    type: 'POST',
                    data: {
                        assign: true,
                        deid: deid,
                        order_id: orderId
                    },
                    success: function(response) {
                        try {
                            var result = JSON.parse(response);
                            if (result.success) {
                                alert(result.message);
                                location.reload();
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch(e) {
                            console.error('Error parsing response:', response);
                            alert('An error occurred. Please try again.');
                        }
                    },
                    error: function() {
                        alert('Error occurred while connecting to the server.');
                    }
                });
            }
        }
    </script>
</body>
</html>

<?php
mysqli_close($con);
?>
