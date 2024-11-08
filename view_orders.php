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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-cell {
            font-weight: bold;
        }
        .status-ok {
            color: green;
        }
        .status-warning {
            color: orange;
        }
        .status-danger {
            color: red;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
<a href="supplierindex.php" class="back-link">Back to Dashboard</a>
    <h2>Order Details for <?php echo htmlspecialchars($supplier_name); ?></h2>

    <?php if ($error_message): ?>
        <div class="alert"><?php echo htmlspecialchars($error_message); ?></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>SI No</th>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Ordered Quantity</th>
                    <th>Current Quantity</th>
                    <th>Price</th>
                    <th>Order Date</th>
                    <th>Customer Name</th>
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
                        <td><?php echo htmlspecialchars($order['price']); ?></td>
                        <td><?php echo htmlspecialchars($order['date']); ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo htmlspecialchars($order['district']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td class="status-cell <?php echo getStatusClass($order['current_quantity']); ?>">
                            <?php echo getStatusText($order['current_quantity']); ?>
                        </td>
                        <td>
                            <button onclick="openUpdateModal(
                                '<?php echo $order['product_id']; ?>', 
                                '<?php echo $order['petid']; ?>', 
                                '<?php echo $order['current_quantity']; ?>', 
                                '<?php echo htmlspecialchars($order['product_name']); ?>'
                            )" class="edit-btn">
                                <i class="fas fa-edit"></i> Update Stock
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Update Quantity Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeUpdateModal()">&times;</span>
        <h3>Update Stock Quantity</h3>
        <form id="updateQuantityForm">
            <input type="hidden" id="update_product_id" name="product_id">
            <input type="hidden" id="update_pet_id" name="pet_id">
            <p id="product_name_display"></p>
            <div class="form-group">
                <label for="new_quantity">New Quantity:</label>
                <input type="number" id="new_quantity" name="new_quantity" min="0" required>
            </div>
            <button type="submit" class="submit-btn">Update Quantity</button>
        </form>
    </div>
</div>

<!-- Alert Modal for Zero Quantity Products -->
<?php if (!empty($zero_quantity_products)): ?>
    <div class="modal" id="alertModal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h3>Zero Quantity Products</h3>
            <ul>
                <?php foreach ($zero_quantity_products as $product): ?>
                    <li><?php echo htmlspecialchars($product); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<script>
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
