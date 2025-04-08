<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid']) || !isset($_GET['deid'])) {
    exit('Unauthorized access');
}

$deid = intval($_GET['deid']);

// Get the delivery boy details
$query = "SELECT * FROM deliveryboy WHERE deid = $deid";
$result = mysqli_query($con, $query);
$deliveryBoy = mysqli_fetch_assoc($result);

if (!$deliveryBoy) {
    exit('Delivery boy not found');
}

// Count today's orders for this delivery boy - improved query
$lid = $deliveryBoy['lid'];
$todayOrdersQuery = "
    SELECT COUNT(*) as day_count 
    FROM deliveryboy 
    WHERE lid = '$lid' AND detail_id IS NOT NULL AND DATE(assign_date) = CURDATE()
";
$todayOrdersResult = mysqli_query($con, $todayOrdersQuery);
$today_count = mysqli_fetch_assoc($todayOrdersResult)['day_count'];
$limit_reached = $today_count >= 10;

// Get delivery statistics
$statsQuery = "
    SELECT 
        COUNT(DISTINCT detail_id) as total_deliveries,
        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as completed_deliveries,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as ongoing_deliveries
    FROM deliveryboy 
    WHERE lid = '$lid'
";
$statsResult = mysqli_query($con, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

// Get the 2 most recent delivery orders
$recentOrdersQuery = "
    SELECT od.*, d.assign_date, r.name as customer_name, r.phone as customer_phone,
           r.landmark, r.roadname, r.district, r.state, r.pincode
    FROM deliveryboy d
    JOIN order_details od ON d.detail_id = od.detail_id
    JOIN registration r ON od.lid = r.lid
    WHERE d.lid = '$lid'
    ORDER BY d.assign_date DESC
    LIMIT 2
";
$recentOrdersResult = mysqli_query($con, $recentOrdersQuery);

// Count total orders
$totalOrdersQuery = "
    SELECT COUNT(*) as total
    FROM deliveryboy
    WHERE lid = '$lid' AND detail_id IS NOT NULL
";
$totalOrdersResult = mysqli_query($con, $totalOrdersQuery);
$totalOrders = mysqli_fetch_assoc($totalOrdersResult)['total'];
$hasMoreOrders = $totalOrders > 2;

// Count processing and delivered orders
$processingOrdersQuery = "
    SELECT COUNT(*) as count
    FROM deliveryboy d
    JOIN order_details od ON d.detail_id = od.detail_id
    WHERE d.lid = '$lid' AND od.order_status = 2
";
$processingOrdersResult = mysqli_query($con, $processingOrdersQuery);
$processingOrdersCount = mysqli_fetch_assoc($processingOrdersResult)['count'];

$deliveredOrdersQuery = "
    SELECT COUNT(*) as count
    FROM deliveryboy d
    JOIN order_details od ON d.detail_id = od.detail_id
    WHERE d.lid = '$lid' AND od.order_status = 3
";
$deliveredOrdersResult = mysqli_query($con, $deliveredOrdersQuery);
$deliveredOrdersCount = mysqli_fetch_assoc($deliveredOrdersResult)['count'];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Personal Information</h6>
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($deliveryBoy['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($deliveryBoy['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($deliveryBoy['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Pincode:</th>
                            <td><?php echo htmlspecialchars($deliveryBoy['pincode']); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($limit_reached): ?>
                                    <span class="badge badge-danger">Unavailable</span>
                                    <small class="text-muted ml-2">Daily limit reached</small>
                                <?php else: ?>
                                    <span class="badge badge-info">Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delivery Statistics -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Delivery Statistics</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-primary"><?php echo $totalOrders; ?></h3>
                            <p class="text-muted">Total Orders</p>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning"><?php echo $processingOrdersCount; ?></h3>
                            <p class="text-muted">Processing</p>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success"><?php echo $deliveredOrdersCount; ?></h3>
                            <p class="text-muted">Delivered</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="text-muted">Today's Orders</h6>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar <?php echo $limit_reached ? 'bg-danger' : 'bg-success'; ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo ($today_count / 10) * 100; ?>%" 
                                 aria-valuenow="<?php echo $today_count; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="10">
                            </div>
                        </div>
                        <div class="text-right mb-3">
                            <span class="badge <?php echo $limit_reached ? 'badge-danger' : 'badge-success'; ?>">
                                <?php echo $today_count; ?>/10
                            </span>
                            <?php if ($limit_reached): ?>
                                <small class="text-danger">Daily limit reached</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <a href="export_orders.php?lid=<?php echo $lid; ?>&type=processing" class="btn btn-warning btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i> Download Processing Report
                        </a>
                        <a href="export_orders.php?lid=<?php echo $lid; ?>&type=delivered" class="btn btn-success btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i> Download Delivery Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-subtitle text-muted">Recent Deliveries</h6>
                <?php if ($hasMoreOrders): ?>
                    <button id="viewAllDeliveries" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list mr-1"></i> View All Deliveries
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if (mysqli_num_rows($recentOrdersResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Address</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($recentOrdersResult)): ?>
                                <tr>
                                    <td><?php echo $order['detail_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($order['landmark']) . ', ' . 
                                                     htmlspecialchars($order['roadname']) . '<br>' .
                                                     htmlspecialchars($order['district']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($order['assign_date'])); ?></td>
                                    <td>
                                        <?php if ($order['order_status'] == 3): ?>
                                            <span class="badge badge-success">Delivered</span>
                                        <?php elseif ($order['order_status'] == 2): ?>
                                            <span class="badge badge-warning">Processing</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No recent deliveries found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View All Deliveries section - Hidden by default -->
<div id="allDeliveriesSection" style="display: none;">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-subtitle text-muted">All Deliveries</h6>
                <button id="hideAllDeliveries" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times mr-1"></i> Hide
                </button>
            </div>
            
            <div id="allDeliveriesContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading delivery history...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle View All Deliveries button click
    $('#viewAllDeliveries').click(function() {
        $('#allDeliveriesSection').show();
        
        // Load all deliveries via AJAX
        $.ajax({
            url: 'get_all_deliveries.php',
            type: 'GET',
            data: { lid: '<?php echo $lid; ?>' },
            success: function(response) {
                $('#allDeliveriesContent').html(response);
            },
            error: function() {
                $('#allDeliveriesContent').html('<div class="alert alert-danger">Error loading delivery history</div>');
            }
        });
    });
    
    // Handle Hide All Deliveries button click
    $('#hideAllDeliveries').click(function() {
        $('#allDeliveriesSection').hide();
    });
});
</script>
