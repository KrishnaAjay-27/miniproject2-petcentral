<?php
require('fpdf/fpdf.php');
require('connection.php');

class PDF extends FPDF {
    function Header() {
        // Add logo
        //$this->Image('logo.png',10,6,30); // Uncomment and add your logo
        
        // Header Title
        $this->SetFont('Arial', 'B', 20);
        $this->SetFillColor(51, 122, 183); // Blue header
        $this->SetTextColor(255, 255, 255); // White text
        $this->Cell(0, 25, 'Delivered Orders Report', 0, 1, 'C', true);
        
        // Date
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100); // Gray text
        $this->Cell(0, 10, 'Generated on: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
    
    function SummarySection($total_orders, $total_amount) {
        $this->SetFillColor(245, 245, 245);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(51, 51, 51);
        $this->Cell(0, 12, 'Report Summary', 1, 1, 'L', true);
        
        $this->SetLeftMargin(20);
        $this->SetFont('Arial', '', 12);
        $this->Ln(5);
        
        // Summary box with border
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        
        $this->Cell(80, 10, 'Total Delivered Orders:', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 10, $total_orders, 1, 1, 'R', true);
        
        $this->SetFont('Arial', '', 12);
        $this->Cell(80, 10, 'Total Revenue:', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 10, 'Rs. ' . number_format($total_amount, 2), 1, 1, 'R', true);
        
        $this->SetLeftMargin(10);
        $this->Ln(10);
    }
    
    function AddOrderSection($row, $order_number, $total_orders) {
        // Order Header with progress indicator
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(51, 51, 51);
        $this->Cell(0, 12, 'Order #' . $row['order_id'] . ' (' . $order_number . ' of ' . $total_orders . ')', 1, 1, 'L', true);
        
        // Create a box for order details
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        
        // Left side details
        $this->SetLeftMargin(20);
        
        // Customer Details Section with box
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(170, 10, 'Customer Details', 1, 1, 'L', true);
        $this->SetFont('Arial', '', 11);
        $this->Cell(170, 8, 'Name: ' . $row['customer_name'], 'LR', 1, 'L', true);
        $this->Cell(170, 2, '', 'LRB', 1); // Bottom border
        
        // Product Details Section
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(170, 10, 'Product Information', 1, 1, 'L', true);
        $this->SetFont('Arial', '', 11);
        $this->Cell(170, 8, 'Product: ' . $row['product_name'], 'LR', 1, 'L', true);
        $this->Cell(170, 2, '', 'LRB', 1);
        
        // Delivery Details Section
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(170, 10, 'Delivery Information', 1, 1, 'L', true);
        $this->SetFont('Arial', '', 11);
        $this->Cell(170, 8, 'Delivery Boy: ' . $row['delivery_boy_name'], 'LR', 1, 'L', true);
        $this->Cell(170, 8, 'Delivery Date: ' . date('d/m/Y', strtotime($row['date'])), 'LR', 1, 'L', true);
        $this->Cell(170, 2, '', 'LRB', 1);
        
        // Payment Details Section with highlighted total
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(170, 10, 'Payment Details', 1, 1, 'L', true);
        $this->SetFont('Arial', '', 11);
        $this->SetFillColor(250, 250, 250);
        $this->Cell(85, 10, 'Total Amount:', 'L', 0, 'L', true);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(85, 10, 'Rs. ' . number_format($row['total'], 2), 'R', 1, 'R', true);
        $this->Cell(170, 2, '', 'LRB', 1);
        
        // Reset styles
        $this->SetLeftMargin(10);
        $this->SetFillColor(255, 255, 255);
        
        // Separator
        $this->Ln(8);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(8);
    }
}

// Create instance of PDF class
$pdf = new PDF();
$pdf->AliasNbPages(); // For total page numbers
$pdf->AddPage();

// Set default margin
$pdf->SetMargins(10, 10, 10);

// Fetch delivered orders
$query = "
    SELECT 
        od.detail_id AS order_id, 
        r.name AS customer_name, 
        pd.name AS product_name, 
        db.name AS delivery_boy_name, 
        od.price AS total, 
        db.assign_date AS date
    FROM order_details od
    LEFT JOIN registration r ON od.lid = r.lid
    LEFT JOIN product_dog pd ON od.product_id = pd.product_id
    LEFT JOIN deliveryboy db ON od.deid = db.deid
    WHERE od.order_status = 3
    ORDER BY db.assign_date DESC
";
$result = mysqli_query($con, $query);

// Calculate totals
$total_orders = mysqli_num_rows($result);
$total_amount = 0;
$orders = array();

while ($row = mysqli_fetch_assoc($result)) {
    $total_amount += $row['total'];
    $orders[] = $row;
}

// Add summary section
$pdf->SummarySection($total_orders, $total_amount);

// Add sections for each order
$order_number = 1;
foreach ($orders as $row) {
    $pdf->AddOrderSection($row, $order_number, $total_orders);
    
    // Add page break if needed
    if($pdf->GetY() > 250) {
        $pdf->AddPage();
    }
    $order_number++;
}

// Output the PDF
$pdf->Output('D', 'delivered_orders_report.pdf');
?>
