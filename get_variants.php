<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    exit('Unauthorized access');
}

$con = mysqli_connect("localhost", "root", "", "project");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $variantsQuery = "SELECT variant_id, size, quantity, price, status FROM product_variants WHERE product_id = $product_id";
    $variantsResult = mysqli_query($con, $variantsQuery);

    if (mysqli_num_rows($variantsResult) > 0) {
        echo "<table>";
        echo "<tr><th>Size</th><th>Quantity</th><th>Price</th><th>Status</th><th>Actions</th></tr>";
        while ($variant = mysqli_fetch_assoc($variantsResult)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($variant['size']) . "</td>";
            echo "<td>" . $variant['quantity'] . "</td>";
            echo "<td>$" . number_format($variant['price'], 2) . "</td>";
            echo "<td class='status-" . $variant['variant_id'] . "'>" . ($variant['status'] == 0 ? 'Active' : 'Inactive') . "</td>";
            echo "<td>";
            echo "<a href='edit_variant.php?variant_id=" . $variant['variant_id'] . "' class='edit-btn'>Edit</a> ";
            echo "<button onclick='toggleVariantStatus(" . $variant['variant_id'] . ", " . $variant['status'] . ")' class='toggle-btn toggle-btn-" . $variant['variant_id'] . "'>";
            echo $variant['status'] == 0 ? 'Deactivate' : 'Activate';
            echo "</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No variants found for this product.";
    }
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>

<script>
console.log('Script loaded');

function toggleVariantStatus(variantId, currentStatus) {
    console.log('Toggle status called with ID:', variantId, 'Current status:', currentStatus);
    fetch('toggle_variant_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'variant_id=' + variantId + '&current_status=' + currentStatus
    })
    .then(response => response.json())
    .then(result => {
        console.log('Response received:', result);
        if (result.success) {
            const statusCell = document.querySelector('.status-' + variantId);
            const toggleBtn = document.querySelector('.toggle-btn-' + variantId);
            
            if (result.new_status == 1) {
                statusCell.textContent = 'Inactive';
                toggleBtn.textContent = 'Activate';
                toggleBtn.onclick = function() { toggleVariantStatus(variantId, 1); };
            } else {
                statusCell.textContent = 'Active';
                toggleBtn.textContent = 'Deactivate';
                toggleBtn.onclick = function() { toggleVariantStatus(variantId, 0); };
            }
            alert(result.message);
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the variant status.');
    });
}

console.log('Script finished loading');
</script>