<?php
require('fpdf/fpdf.php');
require('connection.php');

class PDF extends FPDF {
    function Header() {
        // Logo and Title Section
        $this->SetFillColor(51, 122, 183);
        $this->Rect(0, 0, 220, 40, 'F');
        
        // Title
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 20, 'Delivery Report', 0, 1, 'C');
        
        // Subtitle with month and year
        $this->SetFont('Arial', '', 14);
        $this->Cell(0, 10, date('F Y'), 0, 1, 'C');
        
        // Reset position
        $this->SetY(45);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
        $this->SetX(10);
        $this->Cell(0, 10, 'Generated on: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    }
    
    function DeliverySummary($data) {
        // Summary Box
        $this->SetFillColor(245, 245, 245);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(51, 51, 51);
        $this->RoundedRect(10, 50, 190, 60, 3, 'F');
        
        // Summary Title
        $this->SetXY(15, 55);
        $this->Cell(180, 10, 'Monthly Summary', 0, 1);
        
        // Summary Content
        $this->SetFont('Arial', '', 12);
        $this->SetXY(20, 70);
        
        // Create summary grid
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        
        // Total Deliveries
        $this->Cell(85, 10, 'Total Deliveries:', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(85, 10, $data['total_deliveries'], 1, 1, 'R', true);
        
        // Total Revenue
        $this->SetFont('Arial', '', 12);
        $this->Cell(85, 10, 'Total Revenue:', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(85, 10, 'Rs. ' . number_format($data['total_amount'], 2), 1, 1, 'R', true);
        
        // Delivery Period
        $this->SetFont('Arial', '', 12);
        $this->Cell(85, 10, 'Report Period:', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(85, 10, date('d/m/Y', strtotime($data['first_delivery'])) . ' - ' . 
                          date('d/m/Y', strtotime($data['last_delivery'])), 1, 1, 'R', true);
        
        $this->Ln(15);
    }
    
    function DeliveryDetailsHeader() {
        $this->SetFillColor(51, 122, 183);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 11);
        $this->SetDrawColor(41, 98, 146);
        
        $this->Cell(25, 10, 'Date', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Order ID', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Customer', 1, 0, 'C', true);
        $this->Cell(45, 10, 'Product', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Del. Boy', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Amount', 1, 1, 'C', true);
    }
    
    function DeliveryDetailsRow($row, $fill = false) {
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 10);
        $this->SetDrawColor(200, 200, 200);
        
        $this->Cell(25, 8, date('d/m/y', strtotime($row['date'])), 'B', 0, 'C', $fill);
        $this->Cell(25, 8, $row['order_id'], 'B', 0, 'C', $fill);
        $this->Cell(50, 8, substr($row['customer_name'], 0, 25), 'B', 0, 'L', $fill);
        $this->Cell(45, 8, substr($row['product_name'], 0, 20), 'B', 0, 'L', $fill);
        $this->Cell(25, 8, substr($row['delivery_boy_name'], 0, 12), 'B', 0, 'L', $fill);
        $this->Cell(20, 8, number_format($row['total'], 2), 'B', 1, 'R', $fill);
    }
    
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', 
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k,
            $x3*$this->k, ($h-$y3)*$this->k));
    }
}

// Create PDF instance
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P', 'A4');
$pdf->SetMargins(10, 10, 10);

// Get first and last day of current month
$first_day_this_month = date('Y-m-01');
$last_day_this_month = date('Y-m-t');

// Fetch all delivered orders for this month
$query = "
    SELECT 
        od.detail_id AS order_id, 
        r.name AS customer_name, 
        pd.name AS product_name, 
        db.name AS delivery_boy_name, 
        od.price AS total, 
        db.assign_date AS date,
        MIN(db.assign_date) as first_delivery,
        MAX(db.assign_date) as last_delivery
    FROM order_details od
    LEFT JOIN registration r ON od.lid = r.lid
    LEFT JOIN product_dog pd ON od.product_id = pd.product_id
    LEFT JOIN deliveryboy db ON od.deid = db.deid
    WHERE od.order_status = 3 
    AND DATE(db.assign_date) BETWEEN '$first_day_this_month' AND '$last_day_this_month'
    GROUP BY od.detail_id
    ORDER BY db.assign_date ASC
";
$result = mysqli_query($con, $query);

// Calculate summary data
$summary_data = array(
    'total_deliveries' => mysqli_num_rows($result),
    'total_amount' => 0,
    'first_delivery' => $first_day_this_month,
    'last_delivery' => $last_day_this_month
);

$orders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $summary_data['total_amount'] += $row['total'];
    if (isset($row['first_delivery'])) {
        $summary_data['first_delivery'] = $row['first_delivery'];
    }
    if (isset($row['last_delivery'])) {
        $summary_data['last_delivery'] = $row['last_delivery'];
    }
    $orders[] = $row;
}

// Add Summary Section
$pdf->DeliverySummary($summary_data);

// Add Detailed Delivery List
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Delivery Details', 0, 1, 'L');
$pdf->Ln(5);

// Add table header
$pdf->DeliveryDetailsHeader();

// Add table rows with alternating colors
$fill = false;
foreach ($orders as $row) {
    $pdf->DeliveryDetailsRow($row, $fill);
    $fill = !$fill;
    
    // Add page break if needed
    if($pdf->GetY() > 250) {
        $pdf->AddPage();
        $pdf->DeliveryDetailsHeader();
    }
}

// Output the PDF
$month_year = date('F_Y');
$pdf->Output('D', 'monthly_delivery_report_' . $month_year . '.pdf');
?>
