<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch the admin's email
$uid = $_SESSION['uid'];
$query = "SELECT email FROM login WHERE lid='$uid'";
$result = mysqli_query($con, $query);
$admin = mysqli_fetch_assoc($result);
$adminEmail = $admin ? $admin['email'] : 'Admin';

// Fetch order history with customer and delivery boy details
$orderHistoryQuery = "
    SELECT 
        od.detail_id,
        od.price,
        od.order_status,
        r.name as customer_name,
        r.phone as customer_phone,
        r.pincode as customer_pincode,
        db.name as delivery_boy_name,
        db.phone as delivery_boy_phone,
        db.assign_date,
        CASE 
            WHEN od.order_status = 3 THEN 'Delivered'
            WHEN od.order_status = 2 THEN 'Assigned'
            WHEN od.order_status = 1 THEN 'Not Assigned'
            ELSE 'Pending Assignment'
        END as delivery_status_text,
        CASE 
            WHEN od.order_status IN (1, 2, 3) THEN 'Payment Successful'
            ELSE 'Payment Pending'
        END as payment_status_text
    FROM order_details od
    LEFT JOIN registration r ON od.lid = r.lid
    LEFT JOIN deliveryboy db ON od.deid = db.deid
    ORDER BY od.detail_id DESC
";

$orderHistoryResult = mysqli_query($con, $orderHistoryQuery);
$orders = mysqli_fetch_all($orderHistoryResult, MYSQLI_ASSOC);

// Handle download requests
if (isset($_GET['download'])) {
    $downloadType = $_GET['download'];
    $filename = "orders_report.csv";
    $query = "";

    if ($downloadType === 'delivered_today') {
        $query = "
            SELECT od.detail_id, r.name as customer_name, od.price, od.order_status
            FROM order_details od
            LEFT JOIN registration r ON od.lid = r.lid
            LEFT JOIN deliveryboy db ON od.deid = db.deid
            WHERE od.order_status = 3 AND DATE(db.assign_date) = CURDATE()
        ";
        $filename = "delivered_orders_today.csv";
    } elseif ($downloadType === 'delivered_month') {
        $query = "
            SELECT od.detail_id, r.name as customer_name, od.price, od.order_status
            FROM order_details od
            LEFT JOIN registration r ON od.lid = r.lid
            LEFT JOIN deliveryboy db ON od.deid = db.deid
            WHERE od.order_status = 3 AND MONTH(db.assign_date) = MONTH(CURDATE())
        ";
        $filename = "delivered_orders_this_month.csv";
    }

    if ($query) {
        $result = mysqli_query($con, $query);
        $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Order ID', 'Customer Name', 'Price', 'Order Status'));

        foreach ($orders as $order) {
            fputcsv($output, $order);
        }

        fclose($output);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    
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
        
        /* Search container styling */
        .search-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .search-container .form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.95rem;
            padding: 10px 12px;
            transition: all 0.3s;
        }
        
        .search-container .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        .search-container .input-group-text {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 0.9rem;
        }
        
        .search-container .btn-voice {
            background-color: #17a2b8;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }
        
        .search-container .btn-voice:hover {
            background-color: #138496;
        }
        
        .search-container .btn-voice:focus {
            box-shadow: 0 0 0 0.2rem rgba(23,162,184,.5);
        }
        
        .search-container .btn-voice.listening {
            animation: pulse 1.5s infinite;
            background-color: #dc3545;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* Actions button group */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons .btn {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .action-buttons .btn-primary {
            background-color: #007bff;
        }
        
        .action-buttons .btn-success {
            background-color: #28a745;
        }
        
        .action-buttons .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        /* Table styling */
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-top: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            border: none;
            padding: 12px 15px;
            font-weight: 500;
            font-size: 0.9rem;
            vertical-align: middle;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        
        .table tbody tr {
            transition: background-color 0.3s;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.05);
        }
        
        /* Status badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-successful {
            background-color: #28a745;
            color: white;
        }
        
        .badge-processing {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-delivered {
            background-color: #28a745;
            color: white;
        }
        
        .badge-assigned {
            background-color: #007bff;
            color: white;
        }
        
        .badge-waiting {
            background-color: #6c757d;
            color: white;
        }

        /* Highlight for delivered orders */
        tr.delivered {
            background-color: rgba(40,167,69,0.05);
        }
        
        /* Highlight for pending payment */
        tr.payment-pending {
            background-color: rgba(255,193,7,0.05);
        }
        
        /* Action buttons in table */
        .btn-action {
            padding: 6px 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        /* Voice feedback indicator */
        #voice-feedback-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(52, 58, 64, 0.8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            max-width: 300px;
            display: none;
            z-index: 1000;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
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
        <a href="managesupplieadmin.php"><i class="fas fa-cog"></i> <span>Manage Suppliers</span></a>
        <a href="managedeliveryboy.php"><i class="fas fa-truck"></i> <span>Manage Deliveryboy</span></a>
        <a href="fetch_products.php"><i class="fas fa-box"></i> <span>View Products</span></a>
       
        <a href="order_history.php" class="active"><i class="fas fa-history"></i> <span>Order History</span></a>
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
                        <h1 class="mb-0">Order History</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 bg-transparent p-0">
                                <li class="breadcrumb-item"><a href="admindashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Order History</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter Container -->
            <div class="search-container">
            <div class="row">
                    <div class="col-md-5">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" 
                               id="orderSearch" 
                               class="form-control" 
                               placeholder="Search orders by ID, customer name, or status...">
                            <div class="input-group-append">
                                <button id="voice-search" class="btn btn-voice" title="Search by voice">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                </div>
                        <small class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle"></i> Click the microphone to search by voice
                        </small>
                    </div>
                    
                <div class="col-md-3">
                    <select class="form-control" id="paymentFilter">
                        <option value="">All Payment Status</option>
                        <option value="successful">Payment Successful</option>
                        <option value="pending">Payment Pending</option>
                    </select>
                </div>
                    
                    <div class="col-md-4">
                        <select class="form-control" id="deliveryFilter">
                            <option value="">All Delivery Status</option>
                            <option value="delivered">Delivered</option>
                            <option value="assigned">Assigned</option>
                            <option value="processing">Processing</option>
                            <option value="pending">Pending Assignment</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="action-buttons">
                        <a href="generate_order_report.php" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Download All Orders
                        </a>
                        <a href="generate_delivered_pdf_month.php" class="btn btn-primary">
                             <i class="fas fa-file-pdf"></i> Download Delivered This Month
                        </a>
                        <a href="generate_delivered_pdf.php" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Downloa Daily Delivery Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

            <!-- Order Table -->
            <div class="table-container">
        <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="ordersTable">
                        <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Details</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Delivery Boy</th>
                        <th>Delivery Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="<?php 
                            echo ($order['order_status'] == '3') ? 'delivered' : '';
                            echo ($order['order_status'] == '0') ? ' payment-pending' : '';
                        ?>" data-order-id="<?php echo htmlspecialchars($order['detail_id']); ?>">
                            <td class="font-weight-bold">#<?php echo htmlspecialchars($order['detail_id']); ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                    <span class="text-muted small">
                                        <i class="fas fa-phone-alt mr-1"></i> <?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Pincode: <?php echo htmlspecialchars($order['customer_pincode'] ?? 'N/A'); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="font-weight-bold">₹<?php echo htmlspecialchars($order['price']); ?></td>
                            <td>
                                <?php 
                                // Determine payment status based on order_status
                                if (in_array($order['order_status'], [1, 2, 3])) {
                                    echo '<span class="status-badge badge-successful">Payment Successful</span>';
                                } else {
                                    echo '<span class="status-badge badge-pending">Payment Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($order['delivery_boy_name']): ?>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold"><?php echo htmlspecialchars($order['delivery_boy_name']); ?></span>
                                        <span class="text-muted small">
                                            <i class="fas fa-phone-alt mr-1"></i> <?php echo htmlspecialchars($order['delivery_boy_phone'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-user-clock mr-1"></i> Not Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                // Determine delivery status based on order_status
                                switch ($order['order_status']) {
                                    case '3':
                                        echo '<span class="status-badge badge-delivered">Delivered</span>';
                                        break;
                                        case '2':
                                        echo '<span class="status-badge badge-assigned">Assigned</span>';
                                            break;
                                        case '1':
                                        echo '<span class="status-badge badge-waiting">Not Assigned</span>';
                                            break;
                                        default:
                                        echo '<span class="status-badge badge-pending">Pending Assignment</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <a href="generate_order_pdf.php?detail_id=<?php echo $order['detail_id']; ?>" 
                                   class="btn btn-primary btn-action btn-speak" 
                                   data-speak="download order <?php echo $order['detail_id']; ?>">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Voice Feedback Indicator -->
    <div id="voice-feedback-indicator">
        <div class="d-flex align-items-center">
            <i class="fas fa-volume-up mr-2"></i>
            <span id="voice-feedback-text">Speaking...</span>
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
            // Initialize DataTable with accessibility features
            var table = $('#ordersTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "responsive": true,
                "searching": false, // We'll use our custom search
                "language": {
                    "paginate": {
                        "previous": "<i class='fas fa-chevron-left'></i>",
                        "next": "<i class='fas fa-chevron-right'></i>"
                    },
                    "info": "Showing _START_ to _END_ of _TOTAL_ orders",
                    "infoEmpty": "No orders available",
                    "zeroRecords": "No matching orders found"
                },
                "dom": '<"row"<"col-12"i>><"row"<"col-12"p>>',
                "accessibility": {
                    "pagingMenuPosition": "below"
                }
            });
            
    // Voice search functionality
            $('#voice-search').on('click', function() {
                const voiceSearchBtn = $(this);
                
        if (!('webkitSpeechRecognition' in window)) {
                    alert('Your browser does not support voice search. Please use Chrome browser or type your search query.');
            return;
        }
                
                const recognition = new webkitSpeechRecognition();
        recognition.lang = 'en-US';
        recognition.interimResults = false;
                recognition.maxAlternatives = 1;
                
                // Visual feedback that we're listening
                voiceSearchBtn.addClass('listening');
                voiceSearchBtn.html('<i class="fas fa-circle"></i>');
                
                recognition.start();
                
                speakMessage('Listening. Please speak your search query.');

        recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    $('#orderSearch').val(transcript);
                    
                    // Provide feedback
                    speakMessage('Searching for: ' + transcript);
                    
                    // Apply filters
                    filterTable();
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error detected: ' + event.error);
                    voiceSearchBtn.removeClass('listening');
                    voiceSearchBtn.html('<i class="fas fa-microphone"></i>');
                    
                    speakMessage('Sorry, I could not understand. Please try again.');
        };

                recognition.onend = function() {
                    voiceSearchBtn.removeClass('listening');
                    voiceSearchBtn.html('<i class="fas fa-microphone"></i>');
    };
            });

            // Function to filter the table based on search input and dropdown selections
    function filterTable() {
                const searchText = $('#orderSearch').val().toLowerCase();
                const paymentFilter = $('#paymentFilter').val().toLowerCase();
                const deliveryFilter = $('#deliveryFilter').val().toLowerCase();
                
                // Reset DataTable search
                table.search('').draw();
                
                // Custom filtering
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const rowText = $(row).text().toLowerCase();
                    
                    // Check if row matches search text
                    const matchesSearch = searchText === '' || rowText.includes(searchText);
                    
                    // Check if row matches payment status
                    const paymentCell = $(row).find('td:nth-child(4)').text().toLowerCase();
                    const matchesPayment = paymentFilter === '' || paymentCell.includes(paymentFilter);
                    
                    // Check if row matches delivery status
                    const deliveryCell = $(row).find('td:nth-child(6)').text().toLowerCase();
                    const matchesDelivery = deliveryFilter === '' || deliveryCell.includes(deliveryFilter);
                    
                    return matchesSearch && matchesPayment && matchesDelivery;
                });
                
                // Redraw table with filters applied
                table.draw();
                
                // Remove the custom filter function after drawing
                $.fn.dataTable.ext.search.pop();
                
                // Read the first matching order details if filtering via voice
                const firstVisibleRow = table.rows({search: 'applied'}).nodes()[0];
                if (firstVisibleRow) {
                    const matchCount = table.rows({search: 'applied'}).count();
                    
                    if (searchText !== '') {
                        let feedbackMessage = `Found ${matchCount} matching orders.`;
                        
                        if (matchCount > 0) {
                            const orderDetails = getOrderDetailsFromRow($(firstVisibleRow));
                            feedbackMessage += ` First matching order: ID ${orderDetails.id}, Customer ${orderDetails.customer}, Amount ${orderDetails.amount}, ${orderDetails.payment}, ${orderDetails.delivery}.`;
                        }
                        
                        speakMessage(feedbackMessage);
                    }
                } else if (searchText !== '') {
                    speakMessage('No matching orders found.');
                }
            }
            
            // Keyboard input for search
            $('#orderSearch').on('keyup', filterTable);
            
            // Dropdown change for filters
            $('#paymentFilter, #deliveryFilter').on('change', filterTable);
            
            // Function to speak messages
            function speakMessage(message) {
                // Cancel any ongoing speech
                window.speechSynthesis.cancel();
                
                // Create and configure speech synthesis
                const speech = new SpeechSynthesisUtterance();
                speech.text = message;
                speech.volume = 1;
                speech.rate = 1;
                speech.pitch = 1;
                
                // Show the feedback indicator
                $('#voice-feedback-text').text(message);
                $('#voice-feedback-indicator').fadeIn(300);
                
                // Hide the indicator when speech ends
                speech.onend = function() {
                    $('#voice-feedback-indicator').fadeOut(300);
                };
                
                // Speak the message
                window.speechSynthesis.speak(speech);
            }
            
            // Extract order details from a table row for voice feedback
            function getOrderDetailsFromRow($row) {
                return {
                    id: $row.find('td:first-child').text().replace('#', '').trim(),
                    customer: $row.find('td:nth-child(2) .font-weight-bold').text().trim(),
                    amount: $row.find('td:nth-child(3)').text().trim(),
                    payment: $row.find('td:nth-child(4) .status-badge').text().trim(),
                    delivery: $row.find('td:nth-child(6) .status-badge').text().trim()
                };
            }
            
            // Voice feedback for clicked rows
            $(document).on('click', '#ordersTable tbody tr', function() {
                const orderDetails = getOrderDetailsFromRow($(this));
                
                const message = `Order ID ${orderDetails.id}, Customer ${orderDetails.customer}, Amount ${orderDetails.amount}, ${orderDetails.payment}, ${orderDetails.delivery}`;
                
                speakMessage(message);
            });
            
            // Voice command for downloading
            $(document).on('click', '.btn-speak', function(e) {
                // Don't prevent default - let the browser follow the link
                const message = $(this).data('speak');
                speakMessage(message);
            });
            
            // Voice command system - listen for keywords
            $('#voice-search').on('contextmenu', function(e) {
                e.preventDefault();
                
                if (!('webkitSpeechRecognition' in window)) {
                    alert('Your browser does not support voice commands.');
                    return;
                }
                
                speakMessage('Voice command mode activated. You can say: filter by payment status, filter by delivery status, or download orders.');
                
                const recognition = new webkitSpeechRecognition();
                recognition.lang = 'en-US';
                recognition.interimResults = false;
                recognition.maxAlternatives = 1;
                
                $(this).addClass('listening');
                $(this).html('<i class="fas fa-circle"></i>');
                
                recognition.start();
                
                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript.toLowerCase();
                    
                    // Handle payment status filter commands
                    if (transcript.includes('payment') && transcript.includes('successful')) {
                        $('#paymentFilter').val('successful').trigger('change');
                        speakMessage('Filtering by successful payments.');
                    } 
                    else if (transcript.includes('payment') && transcript.includes('pending')) {
                        $('#paymentFilter').val('pending').trigger('change');
                        speakMessage('Filtering by pending payments.');
                    }
                    // Handle delivery status filter commands
                    else if (transcript.includes('delivery') && transcript.includes('assigned')) {
                        $('#deliveryFilter').val('assigned').trigger('change');
                        speakMessage('Filtering by assigned delivery status.');
                    }
                    else if (transcript.includes('delivery') && transcript.includes('delivered')) {
                        $('#deliveryFilter').val('delivered').trigger('change');
                        speakMessage('Filtering by delivered status.');
                    }
                    else if (transcript.includes('delivery') && transcript.includes('processing')) {
                        $('#deliveryFilter').val('processing').trigger('change');
                        speakMessage('Filtering by processing status.');
                    }
                    else if (transcript.includes('delivery') && transcript.includes('pending')) {
                        $('#deliveryFilter').val('pending').trigger('change');
                        speakMessage('Filtering by pending assignment status.');
                    }
                    // Handle download commands
                    else if (transcript.includes('download') && transcript.includes('all')) {
                        speakMessage('Downloading all orders.');
                        window.location.href = '?download=all';
                    }
                    else if (transcript.includes('download') && transcript.includes('paid')) {
                        speakMessage('Downloading paid orders.');
                        window.location.href = '?download=paid';
                    }
                    else if (transcript.includes('download') && transcript.includes('unpaid')) {
                        speakMessage('Downloading unpaid orders.');
                        window.location.href = '?download=unpaid';
                    }
                    // Handle clear filters command
                    else if (transcript.includes('clear') && transcript.includes('filter')) {
                        $('#orderSearch').val('');
                        $('#paymentFilter').val('');
                        $('#deliveryFilter').val('');
                        filterTable();
                        speakMessage('Filters cleared.');
                    }
                    // Unknown command
                    else {
                        speakMessage('Command not recognized. Please try again.');
                    }
                };
                
                recognition.onerror = function(event) {
                    console.error('Speech recognition error detected: ' + event.error);
                    $('#voice-search').removeClass('listening');
                    $('#voice-search').html('<i class="fas fa-microphone"></i>');
                };
                
                recognition.onend = function() {
                    $('#voice-search').removeClass('listening');
                    $('#voice-search').html('<i class="fas fa-microphone"></i>');
                };
            });
            
            // Initialize tooltip for voice search button
            $('[data-toggle="tooltip"]').tooltip();
            
            // Add tooltip for voice commands
            $('#voice-search').attr('title', 'Click for voice search. Right-click for voice commands.')
                             .tooltip();
            
            // Speak help message on page load
            setTimeout(function() {
                speakMessage('Order history page loaded. Click on rows to hear order details or use the microphone button for voice search.');
            }, 1000);
    });
    </script>
</body>
</html>

<?php mysqli_close($con); ?>
                     