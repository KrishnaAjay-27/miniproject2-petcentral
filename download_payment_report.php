<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Get supplier information
$uid = $_SESSION['uid'];
$sidQuery = "SELECT s.*, l.email FROM s_registration s JOIN login l ON s.lid = l.lid WHERE s.lid = '$uid'";
$sidResult = mysqli_query($con, $sidQuery);
$sidRow = mysqli_fetch_assoc($sidResult);
$supplier_name = $sidRow['name'];
$supplier_email = $sidRow['email'];

// Create PDF class with custom header and footer
class PDF extends FPDF {
    function Header() {
        // Title
        $this->SetFont('Arial', 'B', 24);
        $this->Cell(0, 15, 'Pet Central', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'Payment History Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Generated on: ' . date('d/m/Y h:i A'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
}

// Initialize PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // For total page numbers
$pdf->AddPage('L', 'A4');

// Add supplier details section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Supplier Details:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(30, 7, 'Name:', 0);
$pdf->Cell(100, 7, $supplier_name, 0, 1);
$pdf->Cell(30, 7, 'Email:', 0);
$pdf->Cell(100, 7, $supplier_email, 0, 1);
$pdf->Ln(5);

// Add report summary section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Report Summary:', 0, 1);

// Get summary statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN payment_status = 1 THEN 1 ELSE 0 END) as paid_payments,
        SUM(CASE WHEN payment_status = 0 THEN 1 ELSE 0 END) as pending_payments,
        SUM(amount) as total_amount
    FROM payments p
    INNER JOIN order_details od ON p.order_id = od.order_id
    INNER JOIN product_dog pd ON od.product_id = pd.product_id
";
$statsResult = mysqli_query($con, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 7, 'Total Transactions:', 0);
$pdf->Cell(30, 7, $stats['total_payments'], 0, 1);
$pdf->Cell(60, 7, 'Paid Transactions:', 0);
$pdf->Cell(30, 7, $stats['paid_payments'], 0, 1);
$pdf->Cell(60, 7, 'Pending Transactions:', 0);
$pdf->Cell(30, 7, $stats['pending_payments'], 0, 1);
$pdf->Cell(60, 7, 'Total Amount:', 0);
$pdf->Cell(30, 7, 'Rs' . number_format($stats['total_amount'], 2), 0, 1);
$pdf->Ln(5);

// Detailed Payment Records section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Detailed Payment Records:', 0, 1);

// Table headers
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(0, 51, 102);
$pdf->SetTextColor(255, 255, 255);

// Adjusted column widths
$pdf->Cell(10, 10, '#', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Payment ID', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Order ID', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Customer', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Phone', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Products', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Amount', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Date', 1, 1, 'C', true);

// Fetch and display payment details
$query = "
    SELECT 
        p.payment_id, 
        p.order_id, 
        p.amount, 
        p.payment_status, 
        DATE_FORMAT(p.payment_date, '%d/%m/%Y') as formatted_date,
        r.name as customer_name, 
        r.phone,
        GROUP_CONCAT(pd.name SEPARATOR ', ') as product_names
    FROM 
        payments p 
    INNER JOIN 
        registration r ON p.lid = r.lid
    INNER JOIN 
        order_details od ON p.order_id = od.order_id
    INNER JOIN 
        product_dog pd ON od.product_id = pd.product_id
    GROUP BY 
        p.payment_id
    ORDER BY 
        p.payment_date DESC
";

$result = mysqli_query($con, $query);

// Table content
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$index = 1;
$total_amount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Handle line breaks for product names if too long
    $products = $row['product_names'];
    if (strlen($products) > 40) {
        $products = substr($products, 0, 37) . '...';
    }

    $pdf->Cell(10, 10, $index, 1, 0, 'C');
    $pdf->Cell(25, 10, $row['payment_id'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['order_id'], 1, 0, 'C');
    $pdf->Cell(35, 10, $row['customer_name'], 1, 0, 'L');
    $pdf->Cell(25, 10, $row['phone'], 1, 0, 'C');
    $pdf->Cell(60, 10, $products, 1, 0, 'L');
    $pdf->Cell(25, 10, 'Rs' . number_format($row['amount'], 2), 1, 0, 'R');
    $pdf->Cell(20, 10, $row['payment_status'] == 1 ? 'Paid' : 'Pending', 1, 0, 'C');
    $pdf->Cell(35, 10, $row['formatted_date'], 1, 1, 'C');
    
    $total_amount += $row['amount'];
    $index++;
}

// Final Summary
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Total Amount: Rs' . number_format($total_amount, 2), 0, 1, 'R');

// Output PDF
$pdf->Output('Payment_History_Report.pdf', 'D');
?>