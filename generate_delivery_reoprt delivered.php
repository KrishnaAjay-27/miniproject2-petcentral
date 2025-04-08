<?php
session_start();
require('connection.php');
require('vendor/autoload.php'); // Make sure you have TCPDF installed

// Check if delivery boy is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];
$report_type = $_GET['type'] ?? '';

// Get delivery boy details
$query = "SELECT name, phone, email FROM deliveryboy WHERE lid='$deid'";
$result = mysqli_query($con, $query);
$delivery_boy = mysqli_fetch_assoc($result);

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Pet Central');
$pdf->SetAuthor('Pet Central');
$pdf->SetTitle('Completed Orders - Customer Details');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 16);

// Add header
$pdf->Cell(0, 10, 'Pet Central', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Completed Orders - Customer Details', 0, 1, 'C');
$pdf->Ln(5);

// Add delivery boy information
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Delivery Boy Details:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 8, 'Name: ' . $delivery_boy['name'], 0, 1, 'L');
$pdf->Cell(0, 8, 'Phone: ' . $delivery_boy['phone'], 0, 1, 'L');
$pdf->Cell(0, 8, 'Email: ' . $delivery_boy['email'], 0, 1, 'L');
$pdf->Cell(0, 8, 'Report Generated: ' . date('d-m-Y h:i A'), 0, 1, 'L');
$pdf->Ln(5);

// Get all completed orders with customer details
$query = "SELECT 
            od.detail_id,
            od.order_id,
            od.product_name,
            od.quantity,
            od.price,
            od.order_status,
            o.date as order_date,
            r.name as customer_name,
            r.phone as customer_phone,
            r.email as customer_email,
            r.address,
            r.district,
            r.pincode,
            r.landmark,
            db.name as delivery_boy_name
          FROM order_details od 
          JOIN tbl_order o ON od.order_id = o.order_id
          JOIN registration r ON od.lid = r.lid
          JOIN deliveryboy db ON od.deid = db.deid
          WHERE db.lid = '$deid' 
          AND od.order_status =2
          ORDER BY o.date DESC";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {
    // Add table headers
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    
    // Create table header
    $pdf->Cell(15, 10, 'No.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Order ID', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Customer Name', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Phone', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'District', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 10);
    $count = 0;
    $total_revenue = 0;
    
    while($row = mysqli_fetch_assoc($result)) {
        $count++;
        
        // Basic Info Row
        $pdf->Cell(15, 10, $count, 1, 0, 'C');
        $pdf->Cell(35, 10, $row['order_id'], 1, 0, 'C');
        $pdf->Cell(50, 10, $row['customer_name'], 1, 0, 'L');
        $pdf->Cell(45, 10, $row['customer_phone'], 1, 0, 'C');
        $pdf->Cell(45, 10, $row['district'], 1, 1, 'L');
        
        // Detailed Info Row
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(190, 8, 'Order Details:', 1, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        
        // Email and Address
        $pdf->Cell(45, 8, 'Email:', 1, 0, 'R', false);
        $pdf->Cell(145, 8, $row['customer_email'], 1, 1, 'L');
        
        $pdf->Cell(45, 8, 'Address:', 1, 0, 'R', false);
        $full_address = $row['address'] . ', ' . $row['district'] . ' - ' . $row['pincode'];
        if ($row['landmark']) {
            $full_address .= ' (Landmark: ' . $row['landmark'] . ')';
        }
        $pdf->Cell(145, 8, $full_address, 1, 1, 'L');
        
        // Product Details
        $pdf->Cell(45, 8, 'Product:', 1, 0, 'R', false);
        $pdf->Cell(145, 8, $row['product_name'], 1, 1, 'L');
        
        $pdf->Cell(45, 8, 'Quantity & Price:', 1, 0, 'R', false);
        $pdf->Cell(145, 8, 'Qty: ' . $row['quantity'] . ' × Rs' . number_format($row['price'], 2) . 
                          ' = Rs' . number_format($row['price'] * $row['quantity'], 2), 1, 1, 'L');
        
        $pdf->Cell(45, 8, 'Order Date:', 1, 0, 'R', false);
        $pdf->Cell(145, 8, date('d-m-Y', strtotime($row['order_date'])), 1, 1, 'L');
        
        // Add space between orders
        $pdf->Ln(5);
        
        // Update total revenue
        $total_revenue += ($row['price'] * $row['quantity']);
    }
    
    // Add summary
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Summary Report', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 8, 'Total Completed Orders: ' . $count, 0, 1, 'L');
    $pdf->Cell(0, 8, 'Total Revenue: Rs' . number_format($total_revenue, 2), 0, 1, 'L');
    
} else {
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'No completed orders found.', 0, 1, 'C');
}

// Output the PDF
$filename = 'completed_orders_customer_details_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');

mysqli_close($con);
?>
