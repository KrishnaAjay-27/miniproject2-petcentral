<?php
require('connection.php');
session_start();


// Add this near the top of the file after your existing requires
require('send_sms.php');

// Get the user ID from the session
$uid = $_SESSION['uid'];
$supplier_id = null;
$supplier_name = "";
$order_details = [];
$error_message = "";
$zero_quantity_products = [];

// Fetch supplier name based on user ID
$query = "SELECT sid, name FROM s_registration WHERE lid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $supplier_id = intval($row['sid']);
    $supplier_name = $row['name'];
} else {
    $error_message = "Supplier not found.";
}
$stmt->close();

// Fetch order details for the supplier
if ($supplier_id) {
    $query = "
        SELECT 
            od.order_id, 
            od.product_name, 
            od.quantity AS ordered_quantity, 
            od.price, 
            od.petid, 
            od.product_id, 
            o.date, 
            r.name AS username, 
            r.district, 
            r.phone
        FROM 
            order_details od
        LEFT JOIN 
            tbl_order o ON od.order_id = o.order_id
        LEFT JOIN 
            registration r ON o.lid = r.lid
        LEFT JOIN 
            product_dog pd ON od.product_id = pd.product_id
        LEFT JOIN 
            productpet pp ON od.petid = pp.petid
        WHERE 
            (pd.sid = ? OR pp.sid = ?)
        ORDER BY o.date DESC
    ";

    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $supplier_id, $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_details[] = $row;
        }
    } else {
        $error_message = "No orders found for this supplier.";
    }
    $stmt->close();
}

// Check product quantities and identify zero-quantity products
foreach ($order_details as &$order) {
    if ($order['petid']) {
        $quantityQuery = "SELECT quantity FROM productpet WHERE petid = ?";
        $stmt = $con->prepare($quantityQuery);
        $stmt->bind_param("i", $order['petid']);
    } else {
        $quantityQuery = "SELECT quantity FROM product_variants WHERE product_id = ?";
        $stmt = $con->prepare($quantityQuery);
        $stmt->bind_param("i", $order['product_id']);
    }
    $stmt->execute();
    $quantityResult = $stmt->get_result();

    if ($quantityRow = $quantityResult->fetch_assoc()) {
        $order['current_quantity'] = $quantityRow['quantity'];
        if ($order['current_quantity'] == 0 && !in_array($order['product_name'], $zero_quantity_products)) {
            $zero_quantity_products[] = $order['product_name'];
        }
    } else {
        $order['current_quantity'] = 'N/A';
    }
    $stmt->close();
}

// Add this after the quantity check loop
if (!empty($zero_quantity_products)) {
    // Fetch supplier's phone number
    $phone_query = "SELECT phone FROM s_registration WHERE sid = ?";
    $stmt = $con->prepare($phone_query);
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $phone_result = $stmt->get_result();
    
    if ($phone_row = $phone_result->fetch_assoc()) {
        $supplier_phone = $phone_row['phone'];
        
        // Format phone number for Twilio (add country code if needed)
        $formatted_phone = '+91' . $supplier_phone; // Adjust country code as needed
        
        // Send SMS alert
        sendStockAlert($formatted_phone, $zero_quantity_products);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo htmlspecialchars($supplier_name); ?></title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    
    <style>
        /* Keep your existing styles and add: */
        .sidebar {
            width: 260px;
            background: #003366;
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            margin-top: 70px;
        }

        .top-header {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            background: white;
            padding: 15px 30px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Status badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-danger {
            background-color: #ffe4e4;
            color: #dc3545;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-ok {
            background-color: #d4edda;
            color: #155724;
        }

        /* DataTables customization */
        .dataTables_wrapper {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .dataTables_filter {
            margin-bottom: 20px;
        }

        .dataTables_filter input {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
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


        /* Add your existing modal styles here */
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 class="text-white mb-0">Welcome</h3>
            <p class="text-white-50 mb-0"><?php echo htmlspecialchars($supplier_name); ?></p>
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

    <!-- Header -->
    <div class="top-header">
        <div class="welcome-text">
            <h1>Welcome Back, <?php echo htmlspecialchars($supplier_name); ?>!</h1>
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

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <table id="ordersTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>SI No</th>
                                    <th>Order ID</th>
                                    <th>Product Name</th>
                                    <th>Ordered Qty</th>
                                    <th>Current Qty</th>
                                    <th>Price</th>
                                    <th>Order Date</th>
                                    <th>Customer</th>
                                    <th>District</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details as $index => $order): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['ordered_quantity']); ?></td>
                                        <td id="quantity_<?php echo $order['product_id'] ? $order['product_id'] : 'pet_'.$order['petid']; ?>">
                                            <?php echo htmlspecialchars($order['current_quantity']); ?>
                                        </td>
                                        <td>₹<?php echo htmlspecialchars($order['price']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($order['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td><?php echo htmlspecialchars($order['district']); ?></td>
                                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusClass($order['current_quantity']); ?>">
                                                <?php echo getStatusText($order['current_quantity']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="openUpdateModal(
                                                '<?php echo $order['product_id']; ?>', 
                                                '<?php echo $order['petid']; ?>', 
                                                '<?php echo $order['current_quantity']; ?>', 
                                                '<?php echo htmlspecialchars($order['product_name']); ?>'
                                            )" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update Quantity Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Update Stock Quantity</h5>
                <button type="button" class="close-modal" onclick="closeUpdateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="updateQuantityForm">
                <div class="modal-body">
                    <div class="product-info">
                        <div class="product-name" id="product_name_display"></div>
                        <p>Update the stock quantity for this product</p>
                    </div>
                    
                    <input type="hidden" id="update_product_id" name="product_id">
                    <input type="hidden" id="update_pet_id" name="pet_id">
                    
                    <div class="form-group">
                        <label for="new_quantity">
                            <i class="fas fa-box"></i> New Quantity
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="new_quantity" 
                               name="new_quantity" 
                               min="0" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-modal btn-cancel" 
                            onclick="closeUpdateModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" 
                            class="btn btn-modal btn-update">
                        <i class="fas fa-check"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Modal for Zero Quantity Products -->
    <?php if (!empty($zero_quantity_products)): ?>
        <div class="modal" id="alertModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Stock Alert</h5>
                    <button type="button" class="close-modal" id="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        The following products are out of stock:
                    </div>
                    <ul class="list-group">
                        <?php foreach ($zero_quantity_products as $product): ?>
                            <li class="list-group-item">
                                <i class="fas fa-box-open text-danger me-2"></i>
                                <?php echo htmlspecialchars($product); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-modal btn-primary" 
                            id="closeAlertModal">
                        <i class="fas fa-check"></i> Acknowledge
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#ordersTable').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-4"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rtip',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[6, 'desc']], // Sort by date by default
                columnDefs: [
                    { orderable: false, targets: [11] }, // Disable sorting for actions column
                    { searchable: false, targets: [11] } // Disable searching for actions column
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search orders...",
                    lengthMenu: "_MENU_ orders per page"
                }
            });
        });

        // Keep your existing JavaScript functions
        function updateDateTime() {
            const now = new Date();
            document.getElementById('currentDateTime').textContent = now.toLocaleString();
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Keep your existing modal functions
        function openUpdateModal(productId, petId, currentQuantity, productName) {
            document.getElementById('update_product_id').value = productId;
            document.getElementById('update_pet_id').value = petId;
            document.getElementById('new_quantity').value = currentQuantity;
            document.getElementById('product_name_display').textContent = 'Product: ' + productName;
            document.getElementById('updateModal').style.display = 'flex';
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        document.getElementById('updateQuantityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = document.getElementById('update_product_id').value;
            const petId = document.getElementById('update_pet_id').value;
            const newQuantity = document.getElementById('new_quantity').value;
            
            fetch('update_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&pet_id=${petId}&new_quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the displayed quantity
                    const elementId = productId ? `quantity_${productId}` : `quantity_pet_${petId}`;
                    document.getElementById(elementId).textContent = newQuantity;
                    closeUpdateModal();
                    alert('Quantity updated successfully!');
                    location.reload(); // Reload to update status colors
                } else {
                    alert('Failed to update quantity: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the quantity');
            });
        });

        // Zero quantity alert modal
        document.addEventListener('DOMContentLoaded', function() {
            const alertModal = document.getElementById('alertModal');
            const closeModal = document.getElementById('closeModal');
            
            if (alertModal && closeModal) {
                alertModal.style.display = 'flex';
                closeModal.onclick = function() {
                    alertModal.style.display = 'none';
                };
            }
        });
    </script>
</body>
</html>

<?php
function getStatusClass($quantity) {
    if ($quantity == 0) return 'status-danger';
    if ($quantity <= 5) return 'status-warning';
    return 'status-ok';
}

function getStatusText($quantity) {
    if ($quantity == 0) return 'Out of Stock';
    if ($quantity <= 2) return 'Low Stock';
    return 'In Stock';
}
?>
