<?php
require('connection.php');
session_start();  // Ensure you have a valid session
 // Assuming this contains reusable HTML header elements

// Fetch payment and user details
$query = "
    SELECT 
        p.payment_id, 
        p.order_id, 
        p.amount, 
        p.payment_status, 
        p.payment_date, 
        r.name, 
        r.phone 
    FROM 
        payments p 
    INNER JOIN 
        registration r ON p.lid = r.lid
";
$result = mysqli_query($con, $query);
$payments = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status {
            font-weight: bold;
        }
        .status-0 {
            color: red;
        }
        .status-1 {
            color: green;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Payment Details</h2>
    <?php if (empty($payments)): ?>
        <p>No payment records found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>SI No.</th>
                    <th>Payment ID</th>
                    <th>Order ID</th>
                    <th>User Name</th>
                    <th>Phone Number</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td> <!-- SI No. -->
                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['name']); ?></td>
                        <td><?php echo htmlspecialchars($payment['phone']); ?></td>
                        <td><?php echo number_format($payment['amount'], 2); ?></td>
                        <td class="status status-<?php echo htmlspecialchars($payment['payment_status']); ?>">
                            <?php echo $payment['payment_status'] == 1 ? 'Paid' : 'Pending'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="supplierindex.php" class="back-link">Back to Dashboard</a>
</div>
</body>
</html>
