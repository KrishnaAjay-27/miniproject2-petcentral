<?php
session_start();
require('connection.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Check if delivery boy is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];

// Fetch the delivery boy's name and assign date from the 'deliveryboy' table
$query = "SELECT name, assign_date FROM deliveryboy WHERE lid='$deid'";
$result = mysqli_query($con, $query);
$delivery_boy = mysqli_fetch_assoc($result);
$delivery_boy_name = $delivery_boy ? $delivery_boy['name'] : 'Delivery Boy'; // Default name if no record found
$assign_date = $delivery_boy ? $delivery_boy['assign_date'] : 'N/A'; // Default assign date if no record found

// Generate OTP and send email when "Mark as Delivered" is clicked
if (isset($_POST['generate_otp'])) {
    $detail_id = mysqli_real_escape_string($con, $_POST['detail_id']);
    
    // Check if an OTP already exists for this order
    $otpCheckQuery = "
        SELECT otp_code, expires_at 
        FROM otp 
        WHERE detail_id = '$detail_id'
    ";
    $otpCheckResult = mysqli_query($con, $otpCheckQuery);
    
    if (mysqli_num_rows($otpCheckResult) > 0) {
        // OTP exists, fetch it
        $otpData = mysqli_fetch_assoc($otpCheckResult);
        $otp_code = $otpData['otp_code'];
        $expires_at = $otpData['expires_at'];

        // Check if the existing OTP is still valid
        if (strtotime($expires_at) > time()) {
            // OTP is still valid, inform the user
            echo json_encode(['success' => true, 'message' => "An OTP has already been generated and is still valid: $otp_code", 'detail_id' => $detail_id]);
            exit;
        } else {
            // OTP has expired, generate a new one
            $otp_code = rand(100000, 999999);
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // OTP valid for 5 minutes

            // Update the existing OTP
            $updateOtpQuery = "
                UPDATE otp 
                SET otp_code = '$otp_code', expires_at = '$expires_at' 
                WHERE detail_id = '$detail_id'
            ";
            mysqli_query($con, $updateOtpQuery);
        }
    } else {
        // Generate a new OTP
        $otp_code = rand(100000, 999999);
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // OTP valid for 5 minutes

        // Insert new OTP into the database
        $insertOtpQuery = "
            INSERT INTO otp (detail_id, otp_code, expires_at) 
            VALUES ('$detail_id', '$otp_code', '$expires_at')
        ";
        mysqli_query($con, $insertOtpQuery);
    }

    // Fetch customer email address
    $customerQuery = "
        SELECT r.email 
        FROM order_details od 
        JOIN registration r ON od.lid = r.lid 
        WHERE od.detail_id = '$detail_id'
    ";
    $customerResult = mysqli_query($con, $customerQuery);
    $customer = mysqli_fetch_assoc($customerResult);
    
    // Send OTP to customer's email
    $emailSent = sendOtpToEmail($customer['email'], $otp_code);
    
    echo json_encode([
        'success' => $emailSent, 
        'message' => $emailSent ? 'OTP sent to customer email' : 'Failed to send OTP', 
        'detail_id' => $detail_id
    ]);
    exit;
}

// Verify OTP and update statuses
if (isset($_POST['verify_otp'])) {
    $detail_id = mysqli_real_escape_string($con, $_POST['detail_id']);
    $entered_otp = mysqli_real_escape_string($con, $_POST['otp']);
    
    // Check if OTP matches
    $otpQuery = "SELECT otp_code, expires_at FROM otp WHERE detail_id = '$detail_id'";
    $otpResult = mysqli_query($con, $otpQuery);
    
    if (mysqli_num_rows($otpResult) > 0) {
        $otpData = mysqli_fetch_assoc($otpResult);
        $stored_otp = $otpData['otp_code'];
        $expires_at = $otpData['expires_at'];
        
        if (time() > strtotime($expires_at)) {
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please generate a new one.']);
            exit;
        }
        
        if ($entered_otp == $stored_otp) {
            // Start transaction
            mysqli_begin_transaction($con);
            
            try {
                // Update order status to 3 (delivered)
                $updateOrderQuery = "UPDATE order_details SET order_status = 3 WHERE detail_id = '$detail_id'";
                if (!mysqli_query($con, $updateOrderQuery)) {
                    throw new Exception("Error updating order status: " . mysqli_error($con));
                }
                
                // Get the delivery boy deid for this order
                $deidQuery = "SELECT deid FROM order_details WHERE detail_id = '$detail_id'";
                $deidResult = mysqli_query($con, $deidQuery);
                $deidData = mysqli_fetch_assoc($deidResult);
                $order_deid = $deidData['deid'];
                
                // Update delivery boy status to 2 (delivered)
                $updateDeliveryBoyQuery = "UPDATE deliveryboy SET status = 2 WHERE deid = '$order_deid'";
                if (!mysqli_query($con, $updateDeliveryBoyQuery)) {
                    throw new Exception("Error updating delivery boy status: " . mysqli_error($con));
                }
                
                mysqli_commit($con);
                echo json_encode(['success' => true, 'message' => 'Delivery confirmed successfully!']);
            } catch (Exception $e) {
                mysqli_rollback($con);
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No OTP found for this order.']);
    }
    exit;
}

// Fetch assignments including order_status and assign_date
$query = "SELECT 
            od.detail_id,
            o.date,
            od.product_name,
            od.order_status,
            r.name as customer_name,
            r.email as customer_email,
            r.phone as customer_phone,
            r.pincode as customer_pincode,
            r.district as customer_district,
            db.assign_date
          FROM order_details od
          JOIN tbl_order o ON od.order_id = o.order_id
          JOIN registration r ON od.lid = r.lid
          JOIN deliveryboy db ON od.deid = db.deid
          WHERE db.lid = '$deid'
          ORDER BY o.date DESC";

$result = mysqli_query($con, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Function to send OTP to email
function sendOtpToEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Disable verbose debug output
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'petcentral68@gmail.com'; // Your Gmail address
        $mail->Password = 'zdla rnbx zeyg yjfr'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('petcentral68@gmail.com', 'Petcentral');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Delivery Confirmation';
        $mail->Body = "Your OTP for confirming the delivery is: <strong>$otp</strong>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <!-- Custom styles -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            background: #003366;
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-links {
            padding: 0;
            margin: 0;
            list-style: none;
            flex-grow: 1;
        }
        .nav-links a {
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }
        .nav-link-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-link-content i {
            width: 20px;
            font-size: 18px;
            text-align: center;
        }
        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            color: white;
        }
        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
            margin-left: 10px;
        }
        .logout-btn {
            margin: 20px;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }
        .content-header {
            margin-bottom: 25px;
        }
        .content-header h1 {
            color: #003366;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .page-description {
            color: #6c757d;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-radius: 0.5rem;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            color: #003366;
            font-weight: 600;
        }
        .table-responsive {
            padding: 1rem;
        }
        table.dataTable {
            border-collapse: collapse !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
        }
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
            border-top: none;
            border-bottom: 2px solid #e9ecef !important;
            color: #495057;
            padding: 12px !important;
        }
        .table td {
            vertical-align: middle;
            padding: 12px !important;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.01);
        }
        .status-btn {
            padding: 8px 12px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-deliver {
            background-color: #28a745;
        }
        .btn-deliver:hover {
            background-color: #218838;
        }
        .btn-delivered {
            background-color: #6c757d;
            cursor: default;
        }
        .badge {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 30px;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        /* OTP Modal Styles */
        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(to right, #003366, #004080);
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 15px 20px;
            border-bottom: none;
        }
        .modal-title {
            font-weight: 600;
        }
        .close {
            color: white;
            opacity: 1;
            text-shadow: none;
        }
        .close:hover {
            color: #f4f4f4;
        }
        .modal-body {
            padding: 25px;
        }
        .otp-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            padding: 15px;
            height: auto;
        }
        .otp-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
        .otp-info p {
            margin-bottom: 0;
            color: #495057;
        }
        .btn-verify {
            padding: 10px 30px;
            background: linear-gradient(to right, #28a745, #20c997);
            border: none;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .customer-email {
            font-weight: 600;
            color: #003366;
        }
        .order-link {
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
        }
        .order-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Welcome</h2>
            <p><?php echo htmlspecialchars($delivery_boy_name); ?></p>
        </div>
       
        <div class="nav-links">
            <a href="deliveryindex.php">
                <div class="nav-link-content">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
            </a>
            <a href="delivery_profile.php">
                <div class="nav-link-content">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </div>
            </a>
            <a href="viewdeliveryassignment.php" class="active">
                <div class="nav-link-content">
                    <i class="fas fa-truck"></i>
                    <span>View Assignments</span>
                </div>
            </a>
            <a href="delivery_chat.php">
                <div class="nav-link-content">
                    <i class="fas fa-comments"></i>
                    <span>Chat with Admin</span>
                </div>
                <?php 
                // Get unread message count
                $unread_query = "SELECT COUNT(*) as unread_count 
                               FROM chatmessage 
                               WHERE lid = ? 
                               AND sender_id = 16 
                               AND is_read = 0";
                $stmt = mysqli_prepare($con, $unread_query);
                mysqli_stmt_bind_param($stmt, 'i', $deid);
                mysqli_stmt_execute($stmt);
                $unread_result = mysqli_stmt_get_result($stmt);
                $unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];
                
                if ($unread_count > 0): 
                ?>
                    <span class="badge badge-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="notificationdelivery.php">
                <div class="nav-link-content">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </div>
            </a>
        </div>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="content">
        <div class="content-header">
        <h1>My Delivery Assignments</h1>
            <p class="page-description">Manage and complete your delivery tasks</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-list mr-2"></i> Assigned Orders</h5>
            </div>
            <div class="table-responsive">
        <?php if (mysqli_num_rows($result) > 0): ?>
                    <table id="assignmentsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Product</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                                    <td>
                                        <span class="order-link" onclick="generatePdf(<?php echo $row['detail_id']; ?>)">
                                            <?php echo $row['detail_id']; ?>
                                        </span>
                                    </td>
                            <td><?php echo date('d-m-Y', strtotime($row['date'])); ?></td>
                            <td><?php echo $row['product_name']; ?></td>
                                    <td>
                                        <strong><?php echo $row['customer_name']; ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope mr-1"></i> <?php echo $row['customer_email']; ?><br>
                                            <i class="fas fa-phone mr-1"></i> <?php echo $row['customer_phone']; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt mr-1"></i> <?php echo $row['customer_district']; ?><br>
                                        <small class="text-muted">Pincode: <?php echo $row['customer_pincode']; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($row['order_status'] == 2): ?>
                                            <span class="badge badge-warning">In Transit</span>
                                        <?php elseif ($row['order_status'] == 3): ?>
                                            <span class="badge badge-success">Delivered</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Processing</span>
                                        <?php endif; ?>
                                    </td>
                            <td>
                                <?php if ($row['order_status'] == 2): ?>
                                            <button class="btn btn-sm btn-deliver mark-delivered-btn" 
                                                    data-id="<?php echo $row['detail_id']; ?>" 
                                                    data-email="<?php echo $row['customer_email']; ?>"
                                                    data-name="<?php echo $row['customer_name']; ?>">
                                                <i class="fas fa-check mr-1"></i> Mark Delivered
                                        </button>
                                <?php else: ?>
                                            <button class="btn btn-sm btn-delivered" disabled>
                                                <i class="fas fa-check-double mr-1"></i> Delivered
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle mr-2"></i> No pending delivery assignments found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">
                        <i class="fas fa-key mr-2"></i> Confirm Delivery
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="otp-info">
                        <p><i class="fas fa-info-circle mr-2"></i> An OTP has been sent to <span class="customer-email" id="customerEmail"></span>.</p>
                        <p class="mb-0">Please ask the customer for the OTP to confirm delivery.</p>
                    </div>
                    <form id="otpForm">
                        <input type="hidden" id="detail_id" name="detail_id">
                        <div class="form-group">
                            <label for="otp"><i class="fas fa-hashtag mr-1"></i> Enter OTP:</label>
                            <input type="text" class="form-control otp-input" id="otp" name="otp" maxlength="6" autocomplete="off" required>
                        </div>
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-verify">
                                <i class="fas fa-check-circle mr-2"></i> Verify OTP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#assignmentsTable').DataTable({
                "order": [[0, "desc"]],
                "language": {
                    "lengthMenu": "Show _MENU_ orders per page",
                    "zeroRecords": "No delivery assignments found",
                    "info": "Showing page _PAGE_ of _PAGES_",
                    "infoEmpty": "No records available",
                    "infoFiltered": "(filtered from _MAX_ total records)"
                },
                "pagingType": "simple_numbers",
                "pageLength": 10,
                "columnDefs": [
                    { "orderable": false, "targets": 6 } // Disable sorting on action column
                ]
            });
            
            // Handle Mark as Delivered button click
            $('.mark-delivered-btn').click(function() {
                const detailId = $(this).data('id');
                const email = $(this).data('email');
                const name = $(this).data('name');
                
                if (confirm('Are you sure you want to mark this delivery as completed?')) {
                    // Generate and send OTP
                    $.ajax({
                        url: 'viewdeliveryassignment.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            generate_otp: true,
                            detail_id: detailId
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show OTP modal
                                $('#detail_id').val(detailId);
                                $('#customerEmail').text(email);
                                $('#otpModal').modal('show');
                                $('#otp').focus();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('An error occurred while generating OTP.');
                        }
                    });
                }
            });
            
            // Handle OTP form submission
            $('#otpForm').submit(function(e) {
                e.preventDefault();
                
                const detailId = $('#detail_id').val();
                const otp = $('#otp').val();
                
                if(otp.length !== 6) {
                    alert('Please enter a valid 6-digit OTP');
                    return;
                }
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...');
                submitBtn.prop('disabled', true);
                
                // Verify OTP
                $.ajax({
                    url: 'viewdeliveryassignment.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        verify_otp: true,
                        detail_id: detailId,
                        otp: otp
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message and reload
                            $('#otpModal').modal('hide');
                            showAlert('success', '<i class="fas fa-check-circle mr-2"></i> ' + response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            // Reset button and show error
                            submitBtn.html(originalText);
                            submitBtn.prop('disabled', false);
                            showAlert('danger', '<i class="fas fa-times-circle mr-2"></i> ' + response.message);
                        }
                    },
                    error: function() {
                        // Reset button and show error
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        showAlert('danger', '<i class="fas fa-times-circle mr-2"></i> An error occurred while verifying OTP.');
                    }
                });
            });
            
            // Only allow numbers in OTP input
            $('#otp').on('input', function() {
                $(this).val($(this).val().replace(/[^0-9]/g, ''));
            });
            
            // Close alert messages
            $(document).on('click', '.alert .close', function() {
                $(this).parent().fadeOut();
            });
        });
        
        // Function to generate PDF
        function generatePdf(detailId) {
            window.open(`generate_order_pdf.php?detail_id=${detailId}`, '_blank');
        }
        
        // Function to show alert messages
        function showAlert(type, message) {
            const alertDiv = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + 
                               message + 
                               '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                               '<span aria-hidden="true">&times;</span></button></div>');
            
            // Remove any existing alerts
            $('.alert').remove();
            
            // Add the alert before the form in the modal
            $('#otpForm').before(alertDiv);
        }
    </script>
</body>
</html>

<?php mysqli_close($con); ?>