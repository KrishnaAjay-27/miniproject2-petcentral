<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    exit('Unauthorized access');
}


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
            echo "<button onclick='editVariant(" . $variant['variant_id'] . ")'>Edit</button> ";
            echo "<button onclick='toggleVariantStatus(" . $variant['variant_id'] . ", " . ($variant['status'] == 0 ? 1 : 0) . ")' class='toggle-btn-" . $variant['variant_id'] . "'>";
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
function editVariant(variantId) {
    // Open edit_variant.php in a new window or tab
    window.open('edit_variant.php?variant_id=' + variantId, '_blank');
}

function toggleVariantStatus(variantId, newStatus) {
    fetch('update_variant_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'variant_id=' + variantId + '&new_status=' + newStatus
    })
    .then(response => response.text())
    .then(result => {
        if (result === "Variant status updated successfully") {
            const statusCell = document.querySelector('.status-' + variantId);
            const toggleBtn = document.querySelector('.toggle-btn-' + variantId);
            
            if (newStatus == 1) {
                statusCell.textContent = 'Inactive';
                toggleBtn.textContent = 'Activate';
                toggleBtn.onclick = function() { toggleVariantStatus(variantId, 0); };
            } else {
                statusCell.textContent = 'Active';
                toggleBtn.textContent = 'Deactivate';
                toggleBtn.onclick = function() { toggleVariantStatus(variantId, 1); };
            }
            alert(result);
        } else {
            alert('Error: ' + result);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the variant status.');
    });
}
</script>